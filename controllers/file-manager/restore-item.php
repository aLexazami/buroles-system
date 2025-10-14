<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/path.php'; // ensureVirtualPathExists()
require_once __DIR__ . '/../../helpers/folder-utils.php'; // restoreFolderAndContents(), ensureFolderHierarchyExists()

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$fileId = $input['id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$fileId) {
  echo json_encode(['success' => false, 'message' => 'Missing user or file ID']);
  exit;
}

try {
  // 🔍 Fetch item info for owner or recipient
  $stmt = $pdo->prepare("
    SELECT * FROM files
    WHERE id = ?
      AND is_deleted = 1
      AND (owner_id = ? OR deleted_by_user_id = ?)
  ");
  $stmt->execute([$fileId, $userId, $userId]);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found or not deleted']);
    exit;
  }

  if ($item['type'] === 'file') {
    restoreFile($pdo, $userId, $item);
    echo json_encode(['success' => true, 'message' => 'File restored successfully']);
    exit;
  }

  restoreFolderAndContents($pdo, $userId, $item['id']);
  echo json_encode(['success' => true, 'message' => 'Folder and contents restored successfully']);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// 🔧 Restore a single file
function restoreFile(PDO $pdo, int $userId, array $file): void {
  $fileId = $file['id'];
  $isOwner = $file['owner_id'] == $userId;
  $isRecipient = $file['deleted_by_user_id'] == $userId;

  $trashPath = __DIR__ . "/../../" . ltrim($file['path'], '/');
  $originalPath = $file['original_path'] ?? null;
  $parentId = $file['parent_id'];
  $fallbackPath = "/srv/burol-storage/$userId/" . $fileId;

  if ($parentId) {
    $stmtParent = $pdo->prepare("SELECT is_deleted FROM files WHERE id = ?");
    $stmtParent->execute([$parentId]);
    $parentIsDeleted = (int) $stmtParent->fetchColumn() === 1;

    if ($parentIsDeleted) {
      $originalPath = $fallbackPath;
      $parentId = null;
    }
  }

  if (!$originalPath) {
    throw new Exception("Restore failed: original path missing.");
  }

  $originalFullPath = __DIR__ . "/../../" . ltrim($originalPath, '/');

  if ($isOwner) {
    ensureVirtualPathExists($originalPath);

    if (!is_file($trashPath)) {
      if (file_exists($originalFullPath)) {
        updateRestoreMetadata($pdo, $fileId, $originalPath, $parentId);
        logRestore($pdo, $userId, $fileId, 'File already present, DB state updated');
        return;
      }
      throw new Exception("Restore failed: file missing from trash.");
    }

    if (!is_writable(dirname($originalFullPath))) {
      throw new Exception("Restore failed: destination folder is not writable.");
    }

    if (file_exists($originalFullPath)) {
      throw new Exception("Restore failed: destination file already exists.");
    }

    rename($trashPath, $originalFullPath);
    updateRestoreMetadata($pdo, $fileId, $originalPath, $parentId);
    logRestore($pdo, $userId, $fileId, 'Owner restored file from trash');
  } elseif ($isRecipient) {
    $stmtCheck = $pdo->prepare("SELECT path FROM files WHERE id = ?");
    $stmtCheck->execute([$fileId]);
    $currentPath = $stmtCheck->fetchColumn();

    if ($currentPath === $originalPath && file_exists($originalFullPath)) {
      updateRestoreMetadata($pdo, $fileId, $originalPath, $parentId);
      logRestore($pdo, $userId, $fileId, 'Recipient restored file (no disk move)');
      return;
    }

    updateRestoreMetadata($pdo, $fileId, $originalPath, $parentId);
    logRestore($pdo, $userId, $fileId, 'Recipient restored file (metadata only)');
  }
}

// 🔧 Update metadata after restore
function updateRestoreMetadata(PDO $pdo, string $fileId, string $path, ?string $parentId): void {
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
  $stmt->execute([$path, $parentId, $fileId]);
}

// 🔧 Log restore action
function logRestore(PDO $pdo, int $userId, string $fileId, string $details): void {
  $stmt = $pdo->prepare("
    INSERT INTO logs (id, file_id, user_id, action, details, source)
    VALUES (UUID(), ?, ?, 'restore', ?, 'dashboard')
  ");
  $stmt->execute([$fileId, $userId, $details]);
}