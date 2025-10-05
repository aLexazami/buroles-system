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
function getSharedAccess(PDO $pdo, int $userId, string $type, int $itemId): string|false {
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
function getSharedAccessCached(PDO $pdo, int $userId, string $type, int $itemId, array &$cache): string|false {
  $key = "$type:$itemId";

  if (isset($cache[$key])) {
    return $cache[$key]; // ✅ Return cached result
  }

  $access = getSharedAccess($pdo, $userId, $type, $itemId);
  $cache[$key] = $access; // ✅ Store in cache

  return $access;
}