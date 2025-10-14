<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/path.php'; // ensureVirtualPathExists()

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$fileId = $input['id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$fileId) {
  echo json_encode(['success' => false, 'message' => 'Missing user or file ID']);
  exit;
}

try {
  // 🔍 Fetch item info
  $stmt = $pdo->prepare("SELECT id, type, is_deleted FROM files WHERE id = ? AND owner_id = ?");
  $stmt->execute([$fileId, $userId]);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$item || !$item['is_deleted']) {
    echo json_encode(['success' => false, 'message' => 'Item not found or not deleted']);
    exit;
  }

  if ($item['type'] === 'file') {
    restoreFile($pdo, $userId, $item['id']);
    echo json_encode(['success' => true, 'message' => 'File restored successfully']);
    exit;
  }

  restoreFolderAndContents($pdo, $userId, $fileId);
  echo json_encode(['success' => true, 'message' => 'Folder and contents restored successfully']);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// 🔧 Restore a single file
function restoreFile(PDO $pdo, int $userId, string $fileId): void {
  $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND owner_id = ?");
  $stmt->execute([$fileId, $userId]);
  $file = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$file || !$file['is_deleted']) {
    throw new Exception("File not found or not deleted.");
  }

  $trashPath = __DIR__ . "/../../" . ltrim($file['path'], '/');
  $originalPath = $file['original_path'] ?? null;
  $parentId = $file['parent_id'];
  $fallbackPath = "/srv/burol-storage/$userId/" . $file['id'];

  if ($parentId) {
    $stmtParent = $pdo->prepare("SELECT is_deleted FROM files WHERE id = ?");
    $stmtParent->execute([$parentId]);
    $parentIsDeleted = (int) $stmtParent->fetchColumn() === 1;

    if ($parentIsDeleted) {
      $originalPath = $fallbackPath;
      $parentId = null;
    } else {
      $originalPath = $file['original_path'];
    }
  }

  if (!$originalPath) {
    throw new Exception("Restore failed: original path missing.");
  }

  $originalFullPath = __DIR__ . "/../../" . ltrim($originalPath, '/');
  ensureVirtualPathExists($originalPath);

  if (!is_file($trashPath)) {
    if (file_exists($originalFullPath)) {
      $stmt = $pdo->prepare("
  UPDATE files
  SET is_deleted = 0,
      path = ?,
      original_path = NULL,
      parent_id = ?,
      deleted_by_parent = 0,
      deleted_by_user_id = NULL,
      updated_at = NOW()
  WHERE id = ?
");
      $stmt->execute([$originalPath, $parentId, $file['id']]);
      logRestore($pdo, $userId, $file['id'], 'File already present, DB state updated');
      return;
    }
    throw new Exception('File missing from trash');
  }

  if (!is_writable(dirname($originalFullPath))) {
    throw new Exception("Restore failed: destination folder is not writable.");
  }

  if (file_exists($originalFullPath)) {
    throw new Exception("Restore failed: destination file already exists.");
  }

  rename($trashPath, $originalFullPath);

 $stmt = $pdo->prepare("
  UPDATE files
  SET is_deleted = 0,
      path = ?,
      original_path = NULL,
      parent_id = ?,
      deleted_by_parent = 0,
      deleted_by_user_id = NULL,
      updated_at = NOW()
  WHERE id = ?
");

  $stmt->execute([$originalPath, $parentId, $file['id']]);

  logRestore($pdo, $userId, $file['id'], 'File restored from trash');
}

// 🔧 Restore folder and its contents recursively
function restoreFolderAndContents(PDO $pdo, int $userId, string $folderId): void {
  $stmt = $pdo->prepare("SELECT path, original_path FROM files WHERE id = ?");
  $stmt->execute([$folderId]);
  $folder = $stmt->fetch(PDO::FETCH_ASSOC);

  $restorePath = $folder['original_path'] ?? $folder['path'];
  ensureVirtualPathExists($restorePath);

  $stmt = $pdo->prepare("
  UPDATE files
  SET is_deleted = 0,
      path = original_path,
      original_path = NULL,
      deleted_by_parent = 0,
      deleted_by_user_id = NULL,
      updated_at = NOW()
  WHERE id = ?
");
  $stmt->execute([$folderId]);

  logRestore($pdo, $userId, $folderId, 'Folder restored from trash');

  // 🔁 Restore children with deleted_by_parent = 1
  $stmt = $pdo->prepare("
    SELECT id, type FROM files
    WHERE parent_id = ? AND is_deleted = 1 AND deleted_by_parent = 1
  ");
  $stmt->execute([$folderId]);
  $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($children as $child) {
    if ($child['type'] === 'folder') {
      restoreFolderAndContents($pdo, $userId, $child['id']);
    } else {
      restoreFile($pdo, $userId, $child['id']);
    }
  }

  // 🧠 Scan for orphaned standalone items that belong under this folder
  $stmt = $pdo->prepare("
    SELECT id, type FROM files
    WHERE is_deleted = 1 AND deleted_by_parent = 0 AND original_path LIKE CONCAT(?, '/%')
  ");
  $stmt->execute([$restorePath]);
  $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($orphans as $orphan) {
    if ($orphan['type'] === 'folder') {
      restoreFolderAndContents($pdo, $userId, $orphan['id']);
    } else {
      restoreFile($pdo, $userId, $orphan['id']);
    }
  }
}

// 🔧 Log restore action
function logRestore(PDO $pdo, int $userId, string $fileId, string $details): void {
  $stmt = $pdo->prepare("
    INSERT INTO logs (id, file_id, user_id, action, details, source)
    VALUES (UUID(), ?, ?, 'restore', ?, 'dashboard')
  ");
  $stmt->execute([$fileId, $userId, $details]);
}