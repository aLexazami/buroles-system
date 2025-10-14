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
    // 🧠 Recipient logic: metadata-only restore
    $stmtCheck = $pdo->prepare("SELECT path FROM files WHERE id = ?");
    $stmtCheck->execute([$fileId]);
    $currentPath = $stmtCheck->fetchColumn();

    if ($currentPath === $originalPath && file_exists($originalFullPath)) {
      updateRestoreMetadata($pdo, $fileId, $originalPath, $parentId);
      logRestore($pdo, $userId, $fileId, 'Recipient restored file (no disk move)');
      return;
    }

    // Final fallback: metadata-only restore without disk validation
    updateRestoreMetadata($pdo, $fileId, $originalPath, $parentId);
    logRestore($pdo, $userId, $fileId, 'Recipient restored file (metadata only)');
  }
}

// 🔧 Restore folder and its contents recursively
function restoreFolderAndContents(PDO $pdo, int $userId, string $folderId): void {
  $stmt = $pdo->prepare("SELECT path, original_path FROM files WHERE id = ?");
  $stmt->execute([$folderId]);
  $folder = $stmt->fetch(PDO::FETCH_ASSOC);

  $restorePath = $folder['original_path'] ?? $folder['path'];
  ensureVirtualPathExists($restorePath);

  updateRestoreMetadata($pdo, $folderId, $restorePath, null);
  logRestore($pdo, $userId, $folderId, 'Folder restored from trash');

  // 🔁 Restore children with deleted_by_parent = 1
  $stmt = $pdo->prepare("
    SELECT * FROM files
    WHERE parent_id = ? AND is_deleted = 1 AND deleted_by_parent = 1
  ");
  $stmt->execute([$folderId]);
  $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($children as $child) {
    if ($child['type'] === 'folder') {
      restoreFolderAndContents($pdo, $userId, $child['id']);
    } else {
      restoreFile($pdo, $userId, $child);
    }
  }

  // 🧠 Scan for orphaned standalone items that belong under this folder
  $stmt = $pdo->prepare("
    SELECT * FROM files
    WHERE is_deleted = 1 AND deleted_by_parent = 0 AND original_path LIKE CONCAT(?, '/%')
  ");
  $stmt->execute([$restorePath]);
  $orphans = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($orphans as $orphan) {
    if ($orphan['type'] === 'folder') {
      restoreFolderAndContents($pdo, $userId, $orphan['id']);
    } else {
      restoreFile($pdo, $userId, $orphan);
    }
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