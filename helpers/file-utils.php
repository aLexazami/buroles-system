<?php
require_once __DIR__ . '/folder-utils.php'; // getRecursiveFolderSize()
require_once __DIR__ . '/access-utils.php'; //  getEffectivePermissionsWithSource()
/* ****************************************************************************************** */
function getFilesForView(
  PDO $pdo,
  int $userId,
  string $view = 'my-files',
  ?string $folderId = null,
  string $sortBy = 'updated_at',
  string $sortDir = 'DESC'
): array {
  require_once __DIR__ . '/sharing-utils.php';

  if ($view === 'shared-with-me') {
    if ($folderId && isValidUuid($folderId)) {
      return getSharedFolderContents($pdo, $folderId, $userId);
    }

    $stmt = $pdo->prepare("
      SELECT f.*, u.first_name AS owner_first_name, u.last_name AS owner_last_name
      FROM files f
      JOIN access_control ac ON ac.file_id = f.id
      JOIN users u ON f.owner_id = u.id
      WHERE ac.user_id = :userId
        AND ac.is_revoked = FALSE
        AND f.is_deleted = FALSE
    ");
    $stmt->execute([':userId' => $userId]);
    $sharedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $visible = [];
    foreach ($sharedItems as &$item) {
      $access = getEffectivePermissionsWithSource($pdo, $item['id'], $userId);
      if (!empty($access['permissions'])) {
        $item['permissions'] = $access['permissions'];
        $item['inherited_from'] = $access['inheritedFrom'];
        $item['source_type'] = $access['sourceType'];

        if (!empty($item['parent_id'])) {
          $stmtParent = $pdo->prepare("SELECT name FROM files WHERE id = ?");
          $stmtParent->execute([$item['parent_id']]);
          $item['parent_name'] = $stmtParent->fetchColumn();
        }

        if ($item['type'] === 'folder') {
          $item['size'] = getRecursiveFolderSize($pdo, $item['id']);
        }

        $visible[] = $item;
      }
    }

    $seen = [];
    $deduped = array_filter($visible, function ($item) use (&$seen) {
      if (in_array($item['id'], $seen)) return false;
      $seen[] = $item['id'];
      return true;
    });

    return array_values($deduped);
  }

  $params = [];
  $where = [];
  $joins = '';
  $select = 'f.*, u.first_name AS owner_first_name, u.last_name AS owner_last_name';
  $order = "ORDER BY f.$sortBy $sortDir";

  if ($view === 'trash') {
    $joins = '
      JOIN users u ON f.owner_id = u.id
      LEFT JOIN users du ON f.deleted_by_user_id = du.id
    ';
    $select .= ', du.first_name AS deleted_by_first_name, du.last_name AS deleted_by_last_name';
    $where[] = '(f.owner_id = :trashOwnerId OR f.deleted_by_user_id = :trashActorId)';
    $where[] = 'f.is_deleted = TRUE';
    $where[] = 'f.deleted_by_parent = 0';
    $params[':trashOwnerId'] = $userId;
    $params[':trashActorId'] = $userId;

    if ($folderId && isValidUuid($folderId)) {
      $where[] = 'f.parent_id = :folderId';
      $params[':folderId'] = $folderId;
    } else {
      $where[] = 'f.parent_id IS NULL';
    }
  } elseif ($view === 'shared-by-me') {
    $joins = '
      JOIN access_control ac ON ac.file_id = f.id
      JOIN users u ON f.owner_id = u.id
      JOIN users r ON ac.user_id = r.id
    ';
    $select .= ',
      ac.user_id AS shared_with,
      ac.permission,
      r.first_name AS recipient_first_name,
      r.last_name AS recipient_last_name,
      r.email AS recipient_email
    ';
    $where[] = 'ac.granted_by = :grantedBy';
    $where[] = 'ac.is_revoked = FALSE';
    $where[] = 'f.is_deleted = FALSE';
    $params[':grantedBy'] = $userId;

    if ($folderId && isValidUuid($folderId)) {
      $where[] = 'f.parent_id = :folderId';
      $params[':folderId'] = $folderId;
    }
  } else {
    $joins = 'JOIN users u ON f.owner_id = u.id';
    $where[] = 'f.owner_id = :ownerId';
    $where[] = 'f.is_deleted = FALSE';
    $params[':ownerId'] = $userId;

    if ($folderId && isValidUuid($folderId)) {
      $where[] = 'f.parent_id = :folderId';
      $params[':folderId'] = $folderId;
    } else {
      $where[] = 'f.parent_id IS NULL';
    }
  }

  $sql = "
    SELECT $select
    FROM files f
    $joins
    WHERE " . implode(' AND ', $where) . "
    $order
  ";

  $stmt = $pdo->prepare($sql);
  foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
  }

  $stmt->execute();
  $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $enriched = [];
  foreach ($files as &$file) {
    if ($view === 'shared-by-me') {
      $file['deleted_by_first_name'] = $file['deleted_by_first_name'] ?? null;
      $file['deleted_by_last_name'] = $file['deleted_by_last_name'] ?? null;
      $file['permissions'] = ['edit', 'comment', 'share', 'delete'];
      $file['inherited_from'] = null;
      $file['source_type'] = 'owner';
    } elseif ($file['owner_id'] === $userId) {
      $file['deleted_by_first_name'] = $file['deleted_by_first_name'] ?? null;
      $file['deleted_by_last_name'] = $file['deleted_by_last_name'] ?? null;
      $file['permissions'] = ['edit', 'comment', 'share', 'delete', 'owner'];
      $file['inherited_from'] = null;
      $file['source_type'] = 'owner';
    } elseif ($view === 'trash' && $file['deleted_by_user_id'] === $userId) {
      $file['deleted_by_first_name'] = $file['deleted_by_first_name'] ?? null;
       $file['deleted_by_last_name'] = $file['deleted_by_last_name'] ?? null;
      $file['permissions'] = ['restore', 'delete-permanent'];
      $file['inherited_from'] = null;
      $file['source_type'] = 'recipient';
    } else {
      $access = getEffectivePermissionsWithSource($pdo, $file['id'], $userId);
      if (!empty($access['permissions'])) {
        $file['deleted_by_first_name'] = $file['deleted_by_first_name'] ?? null;
        $file['deleted_by_last_name'] = $file['deleted_by_last_name'] ?? null;
        $file['permissions'] = $access['permissions'];
        $file['inherited_from'] = $access['inheritedFrom'];
        $file['source_type'] = $access['sourceType'];
      } else {
        continue;
      }
    }

    if (!empty($file['parent_id'])) {
      $stmtParent = $pdo->prepare("SELECT name FROM files WHERE id = ?");
      $stmtParent->execute([$file['parent_id']]);
      $file['parent_name'] = $stmtParent->fetchColumn();
    }

    if ($file['type'] === 'folder') {
      $file['size'] = getRecursiveFolderSize($pdo, $file['id']);
      if ($view === 'shared-by-me') {
        $file['children'] = getSharedFolderContents($pdo, $file['id'], $userId, false);
      }
    }

    $enriched[] = $file;
  }

  if ($view === 'trash') {
    foreach ($enriched as &$file) {
      if ($file['type'] === 'folder') {
        $file['children'] = getTrashedChildren($pdo, $file['id'], $userId);
      }
    }
  }

  $seen = [];
  $deduped = array_filter($enriched, function ($file) use (&$seen) {
    if (in_array($file['id'], $seen)) return false;
    $seen[] = $file['id'];
    return true;
  });

  return array_values($deduped);
}

function getPermissionsForFile(array $file, string $view, int $userId, PDO $pdo): array
{
  $permissions = [];

  // 🧠 Owner always has full control
  if ($file['owner_id'] === $userId) {
    $permissions = ['delete', 'share', 'comment'];
  }

  // 🧠 Trash view always allows restore + permanent delete
  if ($view === 'trash') {
    $permissions[] = 'restore';
    $permissions[] = 'permanent-delete';
  }

  // 🧠 Shared-with-me: check access_control table
  if ($view === 'shared-with-me' && $file['owner_id'] !== $userId) {
    $stmt = $pdo->prepare("
      SELECT permission
      FROM access_control
      WHERE file_id = :fileId
        AND user_id = :userId
        AND is_revoked = FALSE
    ");
    $stmt->execute([
      ':fileId' => $file['id'],
      ':userId' => $userId
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($rows as $perm) {
      // Map DB permissions to UI actions
      if ($perm === 'read') $permissions[] = 'comment';
      if ($perm === 'share') $permissions[] = 'share';
      if ($perm === 'delete') $permissions[] = 'delete';
    }
  }

  // 🧠 Shared-by-me: allow delete if you're the owner
  if ($view === 'shared-by-me' && $file['owner_id'] === $userId) {
    $permissions[] = 'delete';
  }

  return array_unique($permissions);
}


function formatSize($bytes)
{
  if ($bytes < 1024) return $bytes . ' B';
  if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
  return round($bytes / 1048576, 1) . ' MB';
}

function formatDate($timestamp)
{
  return date('M d, Y', strtotime($timestamp));
}

function getTrashedChildren(
  PDO $pdo,
  ?string $folderId,
  int $userId,
  int $depth = 0,
  int $maxDepth = 10,
  string $sortBy = 'updated_at',
  string $sortDir = 'DESC'
): array {
  if ($depth > $maxDepth) return [];

  $query = "
    SELECT f.*, 
           u.first_name AS owner_first_name, u.last_name AS owner_last_name,
           du.first_name AS deleted_by_first_name, du.last_name AS deleted_by_last_name,
           r.first_name AS recipient_first_name, r.last_name AS recipient_last_name, r.email AS recipient_email
    FROM files f
    LEFT JOIN users u ON f.owner_id = u.id
    LEFT JOIN users du ON f.deleted_by_user_id = du.id
    LEFT JOIN users r ON f.deleted_by_user_id = r.id
    WHERE " . ($folderId ? "f.parent_id = ?" : "f.parent_id IS NULL") . "
      AND f.is_deleted = TRUE
      AND f.deleted_by_parent = TRUE
      AND (f.owner_id = ? OR f.deleted_by_user_id = ?)
    ORDER BY f.$sortBy $sortDir
  ";

  $stmt = $pdo->prepare($query);
  $folderId
    ? $stmt->execute([$folderId, $userId, $userId])
    : $stmt->execute([$userId, $userId]);

  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($items as &$item) {
    $item['depth'] = $depth;

    $item['permissions'] = ['restore', 'delete-permanent'];
    $item['source_type'] = ($item['deleted_by_user_id'] === $userId) ? 'recipient' : 'owner';
    $item['inherited_from'] = null;

    if (!empty($item['parent_id'])) {
      $stmtParent = $pdo->prepare("SELECT name FROM files WHERE id = ?");
      $stmtParent->execute([$item['parent_id']]);
      $item['parent_name'] = $stmtParent->fetchColumn();
    }

    if ($item['type'] === 'folder') {
      $item['children'] = getTrashedChildren($pdo, $item['id'], $userId, $depth + 1, $maxDepth, $sortBy, $sortDir);
    }
  }

  return $items;
}
/******* BREADCRUMB TRAIL ******** */
function getFolderTrail(PDO $pdo, string $folderId, int $userId): array
{
  $trail = [];
  $currentId = $folderId;

  while ($currentId) {
    $stmt = $pdo->prepare("SELECT id, name, parent_id FROM files WHERE id = ? AND owner_id = ?");
    $stmt->execute([$currentId, $userId]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$folder) break;

    array_unshift($trail, $folder);
    $currentId = $folder['parent_id'];
  }

  return $trail;
}

function getTrashedRootFolders(PDO $pdo, int $userId): array {
  $stmt = $pdo->prepare("
    SELECT f.*, 
           u.first_name AS owner_first_name, u.last_name AS owner_last_name,
           du.first_name AS deleted_by_first_name, du.last_name AS deleted_by_last_name,
           r.first_name AS recipient_first_name, r.last_name AS recipient_last_name, r.email AS recipient_email
    FROM files f
    LEFT JOIN users u ON f.owner_id = u.id
    LEFT JOIN users du ON f.deleted_by_user_id = du.id
    LEFT JOIN users r ON f.deleted_by_user_id = r.id
    WHERE f.is_deleted = TRUE
      AND (f.owner_id = ? OR f.deleted_by_user_id = ?)
      AND (f.parent_id IS NULL OR f.parent_id NOT IN (
        SELECT id FROM files WHERE is_deleted = TRUE AND (owner_id = ? OR deleted_by_user_id = ?)
      ))
    ORDER BY f.updated_at DESC
  ");
  $stmt->execute([$userId, $userId, $userId, $userId]);

  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($items as &$item) {
    $item['depth'] = 0;

    $item['permissions'] = ['restore', 'delete-permanent'];
    $item['source_type'] = ($item['deleted_by_user_id'] === $userId) ? 'recipient' : 'owner';
    $item['inherited_from'] = null;

    if (!empty($item['parent_id'])) {
      $stmtParent = $pdo->prepare("SELECT name FROM files WHERE id = ?");
      $stmtParent->execute([$item['parent_id']]);
      $item['parent_name'] = $stmtParent->fetchColumn();
    }

    if ($item['type'] === 'folder') {
      $item['children'] = getTrashedChildren($pdo, $item['id'], $userId, 1);
    }
  }

  return $items;
}

function getUniqueFileName(PDO $pdo, ?string $parentId, string $baseName): string
{
  $stmt = $pdo->prepare("
    SELECT name FROM files
    WHERE parent_id " . ($parentId ? "= ?" : "IS NULL") . "
    AND type = 'file' AND is_deleted = 0
  ");
  $stmt->execute($parentId ? [$parentId] : []);
  $existingNames = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));

  if (!in_array(strtolower($baseName), $existingNames)) return $baseName;

  $ext = pathinfo($baseName, PATHINFO_EXTENSION);
  $nameOnly = pathinfo($baseName, PATHINFO_FILENAME);
  $counter = 1;

  do {
    $candidate = $ext ? "{$nameOnly} ({$counter}).{$ext}" : "{$nameOnly} ({$counter})";
    $counter++;
  } while (in_array(strtolower($candidate), $existingNames));

  return $candidate;
}

// For download.php and download-folder.php
function resolveStoragePath($storedPath)
{
  $relativePath = ltrim(str_replace('/srv/burol-storage/', '', $storedPath), '/');
  $fullPath = realpath(__DIR__ . '/../srv/burol-storage/' . $relativePath);
  $storageRoot = realpath(__DIR__ . '/../srv/burol-storage');

  if (!$fullPath || strpos($fullPath, $storageRoot) !== 0 || !file_exists($fullPath)) {
    return null;
  }

  return $fullPath;
}

function hasAccessToFile(PDO $pdo, string $fileId, int $userId): bool
{
  // Direct or inherited access to the file or its parents
  $checked = [];

  while ($fileId && !in_array($fileId, $checked)) {
    $checked[] = $fileId;

    $stmt = $pdo->prepare("
      SELECT 1 FROM access_control
      WHERE file_id = ? AND user_id = ? AND is_revoked = 0
      LIMIT 1
    ");
    $stmt->execute([$fileId, $userId]);
    if ($stmt->fetchColumn()) return true;

    $parentStmt = $pdo->prepare("SELECT parent_id FROM files WHERE id = ? LIMIT 1");
    $parentStmt->execute([$fileId]);
    $fileId = $parentStmt->fetchColumn();
  }

  return false;
}

// for rename-item.php
function hasEditPermission(PDO $pdo, string $fileId, int $userId): bool
{
  $checked = [];

  while ($fileId && !in_array($fileId, $checked)) {
    $checked[] = $fileId;

    $stmt = $pdo->prepare("
      SELECT 1 FROM access_control
      WHERE file_id = ? AND user_id = ? AND is_revoked = 0 AND permission IN ('edit', 'write', 'owner')
      LIMIT 1
    ");
    $stmt->execute([$fileId, $userId]);
    if ($stmt->fetchColumn()) return true;

    $parentStmt = $pdo->prepare("SELECT parent_id FROM files WHERE id = ? LIMIT 1");
    $parentStmt->execute([$fileId]);
    $fileId = $parentStmt->fetchColumn();
  }

  return false;
}

function getFileById(PDO $pdo, string $id): ?array
{
  $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
