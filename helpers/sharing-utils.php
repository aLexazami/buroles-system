<?php
function getSharedAccess(PDO $pdo, int $userId, string $type, int $itemId): string|false
{
  static $accessCache = [];
  $cacheKey = "$type:$itemId:$userId";
  if (isset($accessCache[$cacheKey])) return $accessCache[$cacheKey];

  // 🔍 Resolve ownership and parent folder
  if ($type === 'file') {
    $stmt = $pdo->prepare("SELECT owner_id, folder_id, path FROM files WHERE id = ?");
  } else {
    $stmt = $pdo->prepare("SELECT owner_id, parent_id, path FROM folders WHERE id = ?");
  }
  $stmt->execute([$itemId]);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$item) return $accessCache[$cacheKey] = false;

  if ((int)$item['owner_id'] === $userId) {
    return $accessCache[$cacheKey] = 'owner';
  }

  $itemPath = $item['path'] ?? '';
  $folderId = $type === 'file' ? (int)$item['folder_id'] : (int)$item['parent_id'];

  // 🔍 Check direct share
  $shareTable  = $type === 'file' ? 'shared_files' : 'shared_folders';
  $shareColumn = $type === 'file' ? 'file_id'       : 'folder_id';

  $stmt = $pdo->prepare("
    SELECT access_level
    FROM $shareTable
    WHERE $shareColumn = ? AND shared_with = ? AND is_revoked = 0
  ");
  $stmt->execute([$itemId, $userId]);
  $direct = $stmt->fetchColumn();
  if ($direct) return $accessCache[$cacheKey] = $direct;

  // 🔁 Traverse upward for inherited access
  while ($folderId) {
    $stmt = $pdo->prepare("
      SELECT access_level, is_root
      FROM shared_folders
      WHERE folder_id = ? AND shared_with = ? AND is_revoked = 0
    ");
    $stmt->execute([$folderId, $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) return $accessCache[$cacheKey] = $row['access_level'];

    $stmt = $pdo->prepare("SELECT parent_id, is_root FROM folders WHERE id = ?");
    $stmt->execute([$folderId]);
    $meta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$meta || (int)$meta['is_root'] === 1) break;
    $folderId = (int)$meta['parent_id'];
  }

  // 🔽 Check downward inheritance from shared root folders
  if ($itemPath !== '') {
    $stmt = $pdo->prepare("
      SELECT sf.access_level
      FROM shared_folders sf
      JOIN folders f ON f.id = sf.folder_id
      WHERE sf.shared_with = ? AND sf.is_revoked = 0 AND sf.is_root = 1
        AND f.path <> '' AND ? LIKE CONCAT(f.path, '%')
      ORDER BY LENGTH(f.path) DESC
      LIMIT 1
    ");
    $stmt->execute([$userId, $itemPath]);
    $inherited = $stmt->fetchColumn();
    if ($inherited) return $accessCache[$cacheKey] = $inherited;
  }

  return $accessCache[$cacheKey] = false;
}


function canEdit(PDO $pdo, int $userId, string $type, int $itemId): bool
{
  $access = getSharedAccess($pdo, $userId, $type, $itemId);
  return in_array($access, ['owner', 'editor'], true);
}

function canComment(PDO $pdo, int $userId, string $type, int $itemId): bool
{
  $access = getSharedAccess($pdo, $userId, $type, $itemId);
  return in_array($access, ['owner', 'editor', 'comment'], true);
}

function canView(PDO $pdo, int $userId, string $type, int $itemId): bool
{
  $access = getSharedAccess($pdo, $userId, $type, $itemId);
  return in_array($access, ['owner', 'editor', 'comment', 'view'], true);
}

function revokeShare(PDO $pdo, string $type, int $itemId, int $sharedWith): bool
{
  $table = $type === 'file' ? 'shared_files' : 'shared_folders';
  $column = $type === 'file' ? 'file_id' : 'folder_id';
  $stmt = $pdo->prepare("UPDATE $table SET is_revoked = 1 WHERE $column = ? AND shared_with = ?");
  return $stmt->execute([$itemId, $sharedWith]);
}

function fetchSharedItems(
  PDO $pdo,
  string $type,
  string $view,
  int $userId,
  string $orderColumn,
  bool $rootOnly = false
): array {
  // Determine table and column names
  $shareTable   = $type === 'file' ? 'shared_files'   : 'shared_folders';
  $itemTable    = $type === 'file' ? 'files'          : 'folders';
  $itemIdColumn = $type === 'file' ? 'file_id'        : 'folder_id';

  // Determine user context
  $isByView = $view === 'by';
  $userColumn = $isByView ? 'shared_by' : 'shared_with';
  $joinUserColumn = $isByView ? 'shared_with' : 'shared_by';

  // Select user metadata
  $userFields = $isByView
    ? "u.email AS recipient_email, u.avatar_path AS recipient_avatar"
    : "u.email AS shared_by_email, u.avatar_path AS shared_by_avatar";

  // Build WHERE clause
  $where = "sf.$userColumn = :userId AND sf.is_revoked = 0";
  if ($rootOnly) {
    $where .= " AND sf.is_root = 1";
  }

  // Build SQL query
  $sql = "
    SELECT
      sf.id AS id,
      f.name,
      f.path,
      f.owner_id,
      sf.access_level,
      sf.shared_at,
      sf.shared_by,
      sf.shared_with,
      $userFields,
      :type AS type
    FROM $shareTable sf
    JOIN $itemTable f ON sf.$itemIdColumn = f.id
    JOIN users u ON sf.$joinUserColumn = u.id
    WHERE $where
    ORDER BY $orderColumn
  ";

  // Prepare and execute
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    'userId' => $userId,
    'type'   => $type,
  ]);

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function renderSharedItem(array $item): void
{
  $isFolder = ($item['type'] ?? '') === 'folder';
  if ($isFolder) {
    $icon = 'folder-icon.png';
  } else {
    $name = $item['name'] ?? '';
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $icon = match ($ext) {
      'jpg', 'jpeg', 'png', 'gif' => 'image-icon.png',
      'pdf' => 'pdf-icon.png',
      'doc', 'docx' => 'doc-icon.png',
      'xls', 'xlsx' => 'excel-icon.png',
      'zip', 'rar' => 'archive-icon.png',
      default => 'file-icon.png',
    };
  }

  $userId   = $item['shared_by'] ?? $item['shared_with'] ?? $GLOBALS['activeUserId'];
  $ownerId  = $item['owner_id'] ?? $item['shared_by'] ?? $GLOBALS['activeUserId']; // ✅ Correct placement

  $path = $item['path'] ?? '';
  $name = $item['name'] ?? '';
  $link = $isFolder
    ? "/pages/staff/file-manager.php?shared=1&user_id={$ownerId}&path=" . urlencode($path)
    : getUserUploadUrl('1', $ownerId, dirname($path), $name);

  echo '<div class="flex items-center gap-2">';
  echo '<img src="/assets/img/' . $icon . '" class="w-4 h-4" alt="' . ($isFolder ? 'Folder' : strtoupper($ext) . ' file') . ' icon" />';
  echo '<a href="' . $link . '" class="text-sm text-emerald-700 hover:underline">' . htmlspecialchars($name) . '</a>';

  if (isset($item['access_level'], $item['shared_at'])) {
    echo '<span class="ml-2 text-xs text-gray-500">('
      . ucfirst($item['access_level']) . ' • '
      . date('Y-m-d', strtotime($item['shared_at'])) . ')</span>';
  }

  echo '</div>';
}

function renderOrphanedNode(string $name): void
{
  echo '<span class="text-gray-400 italic text-sm">' . htmlspecialchars($name) . '</span>';
}

function getFileComments(PDO $pdo, int $fileId): array
{
  $stmt = $pdo->prepare("
    SELECT
  fc.id,
  fc.comment_text,
  fc.commented_at,
  u.email AS commenter_email
FROM file_comments fc
JOIN users u ON fc.commenter_id = u.id
WHERE fc.file_id = ?
ORDER BY fc.commented_at DESC
  ");
  $stmt->execute([$fileId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFolderComments(PDO $pdo, int $folderId): array
{
  $stmt = $pdo->prepare("
    SELECT fc.comment_text, u.email AS commenter_email
    FROM folder_comments fc
    JOIN users u ON fc.commenter_id = u.id
    WHERE fc.folder_id = ?
    ORDER BY fc.commented_at ASC
  ");
  $stmt->execute([$folderId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getThreadedFileComments(PDO $pdo, int $fileId): array {
  // Fetch top-level comments
  $stmt = $pdo->prepare("
    SELECT fc.id, fc.comment_text, fc.commented_at, u.email AS commenter_email
    FROM file_comments fc
    JOIN users u ON fc.commenter_id = u.id
    WHERE fc.file_id = ? AND fc.parent_comment_id IS NULL AND fc.is_deleted = FALSE
    ORDER BY fc.commented_at ASC
  ");
  $stmt->execute([$fileId]);
  $topLevel = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fetch replies for each top-level comment
  foreach ($topLevel as &$comment) {
    $stmt = $pdo->prepare("
      SELECT fc.comment_text, fc.commented_at, u.email AS commenter_email
      FROM file_comments fc
      JOIN users u ON fc.commenter_id = u.id
      WHERE fc.parent_comment_id = ? AND fc.is_deleted = FALSE
      ORDER BY fc.commented_at ASC
    ");
    $stmt->execute([$comment['id']]);
    $comment['replies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  return $topLevel;
}

function getThreadedFolderComments(PDO $pdo, int $folderId): array {
  // Top-level comments
  $stmt = $pdo->prepare("
    SELECT fc.id, fc.comment_text, fc.commented_at, u.email AS commenter_email
    FROM folder_comments fc
    JOIN users u ON fc.commenter_id = u.id
    WHERE fc.folder_id = ? AND fc.parent_comment_id IS NULL AND fc.is_deleted = FALSE
    ORDER BY fc.commented_at ASC
  ");
  $stmt->execute([$folderId]);
  $topLevel = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Replies per comment
  foreach ($topLevel as &$comment) {
    $stmt = $pdo->prepare("
      SELECT fc.comment_text, fc.commented_at, u.email AS commenter_email
      FROM folder_comments fc
      JOIN users u ON fc.commenter_id = u.id
      WHERE fc.parent_comment_id = ? AND fc.is_deleted = FALSE
      ORDER BY fc.commented_at ASC
    ");
    $stmt->execute([$comment['id']]);
    $comment['replies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  return $topLevel;
}

function fetchUnifiedSharedItems(PDO $pdo, int $userId): array
{
  $byMe = array_merge(
    fetchSharedItems($pdo, 'folder', 'by', $userId, 'sf.shared_at', true),
    fetchSharedItems($pdo, 'file',   'by', $userId, 'sf.shared_at', true)
  );
  $withMe = array_merge(
    fetchSharedItems($pdo, 'folder', 'with', $userId, 'sf.shared_at', true),
    fetchSharedItems($pdo, 'file',   'with', $userId, 'sf.shared_at', true)
  );

  foreach ($byMe as &$item) {
    $item['origin'] = 'by';
  }
  foreach ($withMe as &$item) {
    $item['origin'] = 'with';
  }

  return array_merge($byMe, $withMe);
}

function normalizeSharedItems(array $items): array
{
  foreach ($items as &$item) {
    // Preserve original path for access and link generation
    $item['full_path'] = $item['path'] ?? '';

    // Normalize path for display (strip scoped prefix)
    $item['path'] = isset($item['path'])
      ? preg_replace('#^.*uploads/staff/\d+/#', '', $item['path'])
      : '';

    // Extract display name from normalized path
    $item['name'] = $item['path'] ? basename($item['path']) : 'Unnamed';
  }

  return $items;
}

function sortSharedItems(array $items, string $sortBy, string $sortOrder): array
{
  usort($items, function ($a, $b) use ($sortBy, $sortOrder) {
    $direction = $sortOrder === 'asc' ? 1 : -1;

    return match ($sortBy) {
      'name'     => $direction * strcasecmp($a['name'], $b['name']),
      'modified' => $direction * (strtotime($a['shared_at'] ?? '') <=> strtotime($b['shared_at'] ?? '')),
      default    => 0,
    };
  });

  return $items;
}
