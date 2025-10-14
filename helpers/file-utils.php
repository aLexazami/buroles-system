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

    return $visible;
  }

  $params = [];
  $where = [];
  $joins = '';
  $select = 'f.*, u.first_name AS owner_first_name, u.last_name AS owner_last_name';
  $order = "ORDER BY f.$sortBy $sortDir";

  // ✅ Refactored parent filtering logic
  if ($view === 'trash') {
    if ($folderId && isValidUuid($folderId)) {
      $where[] = 'f.parent_id = :folderId';
      $params[':folderId'] = $folderId;
    } else {
      $where[] = 'f.parent_id IS NULL';
    }
  } elseif ($view === 'shared-by-me') {
    if ($folderId && isValidUuid($folderId)) {
      $where[] = 'f.parent_id = :folderId';
      $params[':folderId'] = $folderId;
    } else {
      // Top-level shared-by-me: skip parent filtering
    }
  } else {
    if ($folderId && isValidUuid($folderId)) {
      $where[] = 'f.parent_id = :folderId';
      $params[':folderId'] = $folderId;
    } else {
      $where[] = 'f.parent_id IS NULL';
    }
  }

  switch ($view) {
    case 'shared-by-me':
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
      break;

    case 'trash':
      $joins = 'JOIN users u ON f.owner_id = u.id';
      $where[] = 'f.owner_id = :trashOwnerId';
      $where[] = 'f.is_deleted = TRUE';
      $where[] = 'f.deleted_by_parent = 0';
      $params[':trashOwnerId'] = $userId;
      break;

    default:
      $joins = 'JOIN users u ON f.owner_id = u.id';
      $where[] = 'f.owner_id = :ownerId';
      $where[] = 'f.is_deleted = FALSE';
      $params[':ownerId'] = $userId;
      break;
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
      $file['permissions'] = ['edit', 'comment', 'share', 'delete'];
      $file['inherited_from'] = null;
      $file['source_type'] = 'owner';
      $enriched[] = $file;
    } elseif ($file['owner_id'] === $userId) {
      $file['permissions'] = ['edit', 'comment', 'share', 'delete'];
      $file['inherited_from'] = null;
      $file['source_type'] = 'owner';
      $enriched[] = $file;
    } else {
      $access = getEffectivePermissionsWithSource($pdo, $file['id'], $userId);
      if (!empty($access['permissions'])) {
        $file['permissions'] = $access['permissions'];
        $file['inherited_from'] = $access['inheritedFrom'];
        $file['source_type'] = $access['sourceType'];
        $enriched[] = $file;
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
  }

  if ($view === 'trash') {
    foreach ($enriched as &$file) {
      if ($file['type'] === 'folder') {
        $file['children'] = getTrashedChildren($pdo, $file['id'], $userId);
      }
    }
  }

  return $enriched;
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

function canPerformAction($userId, $fileId, $action)
{
  global $pdo;

  // Check ownership
  $stmt = $pdo->prepare("SELECT owner_id FROM files WHERE id = ?");
  $stmt->execute([$fileId]);
  $ownerId = $stmt->fetchColumn();
  if ($ownerId == $userId) return true;

  // Check direct access
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM access_control
                         WHERE user_id = ? AND file_id = ? AND permission = ? AND is_revoked = FALSE");
  $stmt->execute([$userId, $fileId, $action]);
  return $stmt->fetchColumn() > 0;
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

  // 🧠 Build dynamic query for root or nested folders
  $query = "
    SELECT id, name, type, path, size, mime_type, updated_at, parent_id
    FROM files
    WHERE " . ($folderId ? "parent_id = ?" : "parent_id IS NULL") . "
      AND is_deleted = TRUE
      AND deleted_by_parent = TRUE
      AND owner_id = ?
    ORDER BY $sortBy $sortDir
  ";

  $stmt = $pdo->prepare($query);
  $folderId ? $stmt->execute([$folderId, $userId]) : $stmt->execute([$userId]);
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($items as &$item) {
    // 🧩 Inject restore preview info
    $item['restore_preview'] = [
      'path' => $item['path'],
      'size' => $item['size'],
      'mime_type' => $item['mime_type'],
      'updated_at' => $item['updated_at']
    ];

    // 🧩 Inject depth for UI rendering (optional)
    $item['depth'] = $depth;

    // 🌳 Recursively fetch children if folder
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

function getTrashedRootFolders(PDO $pdo, int $userId): array
{
  $stmt = $pdo->prepare("
    SELECT * FROM files
    WHERE is_deleted = TRUE
      AND owner_id = ?
      AND (parent_id IS NULL OR parent_id NOT IN (
        SELECT id FROM files WHERE is_deleted = TRUE AND owner_id = ?
      ))
    ORDER BY updated_at DESC
  ");
  $stmt->execute([$userId, $userId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFileById(PDO $pdo, string $id, int $userId): ?array
{
  $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND owner_id = ?");
  $stmt->execute([$id, $userId]);
  return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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
