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
  // 🔍 Check ownership
  if ($type === 'file') {
    $stmt = $pdo->prepare("SELECT owner_id, folder_id FROM files WHERE id = ?");
  } else {
    $stmt = $pdo->prepare("SELECT owner_id, parent_id FROM folders WHERE id = ?");
  }
  $stmt->execute([$itemId]);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$item) return false;
  if ((int)$item['owner_id'] === $userId) return 'owner';

  // 🔍 Check direct share
  $shareTable = $type === 'file' ? 'shared_files' : 'shared_folders';
  $shareColumn = $type === 'file' ? 'file_id' : 'folder_id';

  $stmt = $pdo->prepare("SELECT access_level FROM $shareTable WHERE $shareColumn = ? AND shared_with = ? AND is_revoked = 0");
  $stmt->execute([$itemId, $userId]);
  $direct = $stmt->fetchColumn();
  if ($direct) return $direct;

  // 🔁 Traverse parent folders for inherited access
  $folderId = $type === 'file' ? (int)$item['folder_id'] : (int)$item['parent_id'];

  while ($folderId) {
    $stmt = $pdo->prepare("SELECT parent_id FROM folders WHERE id = ?");
    $stmt->execute([$folderId]);
    $parentId = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT access_level FROM shared_folders WHERE folder_id = ? AND shared_with = ? AND is_revoked = 0");
    $stmt->execute([$folderId, $userId]);
    $inherited = $stmt->fetchColumn();
    if ($inherited) return $inherited;

    $folderId = $parentId ? (int)$parentId : 0;
  }

  return false;
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

function buildSharedTree(array $items): array
{
  $tree = [];

  foreach ($items as $item) {
    $path = sanitizePath($item['path']);
    $segments = explode('/', $path);
    $ref = &$tree;

    foreach ($segments as $i => $segment) {
      if (!isset($ref[$segment])) {
        $ref[$segment] = ['__meta' => null, '__children' => []];
      }

      if ($i === count($segments) - 1) {
        $ref[$segment]['__meta'] = $item;
      }

      $ref = &$ref[$segment]['__children'];
    }
  }

  return $tree;
}

function fetchSharedItems(PDO $pdo, string $type, string $view, int $userId, string $orderColumn, bool $rootOnly = false): array
{
  $table      = $type === 'file' ? 'shared_files' : 'shared_folders';
  $itemTable  = $type === 'file' ? 'files' : 'folders';
  $itemColumn = $type === 'file' ? 'file_id' : 'folder_id';

  $selectEmail = $view === 'by'
    ? "u.email AS recipient_email, sf.shared_by"
    : "u.email AS owner_email, sf.shared_by";

  $whereClause = "sf." . ($view === 'by' ? 'shared_by' : 'shared_with') . " = ?";
  if ($rootOnly) {
    $whereClause .= " AND sf.is_root = 1";
  }

  $sql = "
    SELECT f.name, f.path, sf.access_level, sf.shared_at, $selectEmail, '$type' AS type
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
  $userId   = $item['shared_by'] ?? $GLOBALS['activeUserId'];

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
