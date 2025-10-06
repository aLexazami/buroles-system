<?php

/**
 * Determine the access level a user has to a specific file or folder,
 * considering direct shares, inherited shares from parent folders, and ownership.
 *
 * @param PDO    $pdo     The PDO database connection.
 * @param int    $userId  The ID of the user whose access is being checked.
 * @param string $type    Either 'file' or 'folder'.
 * @param int    $itemId  The ID of the file or folder.
 *
 * @return string|false   Returns 'owner', 'edit', 'view', or false if no access.
 */
function getSharedAccess(PDO $pdo, int $userId, string $type, int $itemId): string|false
{
  static $accessCache = [];
  $cacheKey = "$type:$itemId:$userId";
  if (isset($accessCache[$cacheKey])) return $accessCache[$cacheKey];

  error_log("🔍 Access check → user: $userId, type: $type, itemId: $itemId");

  // 🔍 Check ownership
  if ($type === 'file') {
    $stmt = $pdo->prepare("SELECT owner_id, folder_id FROM files WHERE id = ?");
  } else {
    $stmt = $pdo->prepare("SELECT owner_id, parent_id FROM folders WHERE id = ?");
  }
  $stmt->execute([$itemId]);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$item) {
    error_log("❌ Access denied → item not found.");
    return $accessCache[$cacheKey] = false;
  }

  if ((int)$item['owner_id'] === $userId) {
    error_log("✅ Access granted: owner");
    return $accessCache[$cacheKey] = 'owner';
  }

  // 🔍 Check direct share
  $shareTable  = $type === 'file' ? 'shared_files' : 'shared_folders';
  $shareColumn = $type === 'file' ? 'file_id'     : 'folder_id';

  $stmt = $pdo->prepare("
    SELECT access_level
    FROM $shareTable
    WHERE $shareColumn = ? AND shared_with = ? AND is_revoked = 0
  ");
  $stmt->execute([$itemId, $userId]);
  $direct = $stmt->fetchColumn();

  if ($direct) {
    error_log("✅ Access granted: direct ($direct)");
    return $accessCache[$cacheKey] = $direct;
  }

  // 🔁 Traverse parent folders for inherited access
  $folderId = $type === 'file' ? (int)$item['folder_id'] : (int)$item['parent_id'];

  while ($folderId) {
    error_log("🔁 Traversing folder $folderId");

    // Check inherited share
    $stmt = $pdo->prepare("
      SELECT access_level
      FROM shared_folders
      WHERE folder_id = ? AND shared_with = ? AND is_revoked = 0
    ");
    $stmt->execute([$folderId, $userId]);
    $inherited = $stmt->fetchColumn();

    if ($inherited) {
      error_log("✅ Access granted: inherited ($inherited) from folder $folderId");
      return $accessCache[$cacheKey] = $inherited;
    }

    // Traverse up
    $stmt = $pdo->prepare("SELECT parent_id FROM folders WHERE id = ?");
    $stmt->execute([$folderId]);
    $parentId = $stmt->fetchColumn();
    $folderId = $parentId !== null ? (int)$parentId : 0;
  }

  error_log("❌ Access denied → no ownership, direct, or inherited access.");
  return $accessCache[$cacheKey] = false;
}

function getAccessByPath(PDO $pdo, int $viewerId, string $path, string $type): string|false
{
  $table = $type === 'file' ? 'files' : 'folders';
  $stmt = $pdo->prepare("SELECT id FROM $table WHERE path = ?");
  $stmt->execute([$path]);
  $itemId = $stmt->fetchColumn();
  return $itemId ? getSharedAccess($pdo, $viewerId, $type, (int)$itemId) : false;
}

function resolveUserId(PDO $pdo, string $email): int|false
{
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->execute([$email]);
  return $stmt->fetchColumn() ?: false;
}

function canEdit(PDO $pdo, int $userId, string $type, int $itemId): bool
{
  $access = getSharedAccess($pdo, $userId, $type, $itemId);
  return in_array($access, ['owner', 'editor'], true);
}

function canComment(PDO $pdo, int $userId, string $type, int $itemId): bool
{
  $access = getSharedAccess($pdo, $userId, $type, $itemId);
  return $access === 'comment';
}

function getAccessLabel(string|false $access): string
{
  return match ($access) {
    'owner'   => 'Owner',
    'editor'  => 'Can Edit',
    'comment' => 'Can Comment',
    'view'    => 'View Only',
    default   => 'No Access',
  };
}

function revokeShare(PDO $pdo, string $type, int $itemId, int $sharedWith): bool
{
  $table = $type === 'file' ? 'shared_files' : 'shared_folders';
  $column = $type === 'file' ? 'file_id' : 'folder_id';
  $stmt = $pdo->prepare("UPDATE $table SET is_revoked = 1 WHERE $column = ? AND shared_with = ?");
  return $stmt->execute([$itemId, $sharedWith]);
}

/**
 * Cached version of getSharedAccess to minimize database queries.
 */
function getSharedAccessCached(PDO $pdo, int $userId, string $type, int $itemId, array &$cache): string|false
{
  $key = "$type:$itemId";

  if (isset($cache[$key])) {
    return $cache[$key]; // ✅ Return cached result
  }

  $access = getSharedAccess($pdo, $userId, $type, $itemId);
  $cache[$key] = $access; // ✅ Store in cache

  return $access;
}


function fetchSharedItems(PDO $pdo, string $type, string $view, int $userId, string $orderColumn, bool $rootOnly = false): array
{
  $table      = $type === 'file' ? 'shared_files' : 'shared_folders';
  $itemTable  = $type === 'file' ? 'files' : 'folders';
  $itemColumn = $type === 'file' ? 'file_id' : 'folder_id';

  // ✅ Select name and email for both views
  $selectUser = $view === 'by'
    ? "u.email AS recipient_email, u.avatar_path AS recipient_avatar, sf.shared_by, sf.shared_with"
    : "u.email AS shared_by_email, u.avatar_path AS shared_by_avatar, sf.shared_by, sf.shared_with";

  $whereClause = "sf." . ($view === 'by' ? 'shared_by' : 'shared_with') . " = ?";
  if ($rootOnly) {
    $whereClause .= " AND sf.is_root = 1";
  }

  $sql = "
    SELECT sf.id AS id, f.name, f.path, sf.access_level, sf.shared_at, $selectUser, '$type' AS type
    FROM $table sf
    JOIN $itemTable f ON sf.$itemColumn = f.id
    JOIN users u ON sf." . ($view === 'by' ? 'shared_with' : 'shared_by') . " = u.id
    WHERE $whereClause
    ORDER BY $orderColumn
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$userId]);
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
  $userId = $item['shared_by'] ?? $item['shared_with'] ?? $GLOBALS['activeUserId'];

  $path = $item['path'] ?? '';
  $name = $item['name'] ?? '';
  $link = $isFolder
    ? "/pages/staff/file-manager.php?shared=1&user_id={$userId}&path=" . urlencode($path)
    : getUserUploadUrl('1', $userId, dirname($path), $name);

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
    SELECT fc.comment_text, fc.commented_at, u.email AS commenter_email
FROM file_comments fc
JOIN users u ON fc.commenter_id = u.id
WHERE fc.file_id = ?
ORDER BY fc.commented_at DESC
  ");
  $stmt->execute([$fileId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchUnifiedSharedItems(PDO $pdo, int $userId): array {
  $byMe = array_merge(
    fetchSharedItems($pdo, 'folder', 'by', $userId, 'sf.shared_at', true),
    fetchSharedItems($pdo, 'file',   'by', $userId, 'sf.shared_at', true)
  );
  $withMe = array_merge(
    fetchSharedItems($pdo, 'folder', 'with', $userId, 'sf.shared_at', true),
    fetchSharedItems($pdo, 'file',   'with', $userId, 'sf.shared_at', true)
  );

  foreach ($byMe as &$item)   { $item['origin'] = 'by'; }
  foreach ($withMe as &$item) { $item['origin'] = 'with'; }

  return array_merge($byMe, $withMe);
}

function normalizeSharedItems(array $items): array {
  foreach ($items as &$item) {
    $item['path'] = isset($item['path']) ? preg_replace('#^.*uploads/staff/\d+/#', '', $item['path']) : '';
    $item['name'] = $item['path'] ? basename($item['path']) : 'Unnamed';
  }
  return $items;
}

function sortSharedItems(array $items, string $sortBy, string $sortOrder): array {
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