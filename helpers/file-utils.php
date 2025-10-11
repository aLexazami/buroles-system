<?php
/* ****************************************************************************************** */
function getFilesForView($userId, $view = 'my-files', $folderId = null, $sortBy = 'updated_at', $sortDir = 'DESC')
{
  global $pdo;

  $params = [];
  $where = [];
  $joins = '';
  $select = 'f.*';
  $order = "ORDER BY f.$sortBy $sortDir";

  // 📁 Folder filter
  if ($folderId) {
    $where[] = 'f.parent_id = :folderId';
    $params[':folderId'] = $folderId;
  } else {
    $where[] = 'f.parent_id IS NULL';
  }

  // 🔍 View-specific filters
  switch ($view) {
    case 'shared-with-me':
      $joins = 'JOIN access_control ac ON ac.file_id = f.id';
      $where[] = 'ac.user_id = :userId';
      $where[] = 'ac.is_revoked = FALSE';
      $where[] = 'f.is_deleted = FALSE';
      $where[] = 'f.owner_id != :userId';
      $params[':userId'] = $userId;
      break;

    case 'shared-by-me':
      $joins = 'JOIN access_control ac ON ac.file_id = f.id';
      $select = 'f.*, ac.user_id AS shared_with, ac.permission';
      $where[] = 'ac.granted_by = :userId';
      $where[] = 'ac.is_revoked = FALSE';
      $where[] = 'f.is_deleted = FALSE';
      $params[':userId'] = $userId;
      break;

    case 'trash':
      $where[] = 'f.owner_id = :userId';
      $where[] = 'f.is_deleted = TRUE';
      $params[':userId'] = $userId;
      break;

    default: // 'my-files'
      $where[] = 'f.owner_id = :userId';
      $where[] = 'f.is_deleted = FALSE';
      $params[':userId'] = $userId;
      break;
  }

  // 🧠 Final SQL assembly
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

  // ✅ Inject permissions using helper
  foreach ($files as &$file) {
    $file['permissions'] = getPermissionsForFile($file, $view, $userId, $pdo);
  }

  return $files;
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
