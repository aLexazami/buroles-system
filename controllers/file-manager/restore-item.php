<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

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
  $stmt = $pdo->prepare("SELECT id, path, name, type, is_deleted FROM files WHERE id = ? AND owner_id = ?");
  $stmt->execute([$fileId, $userId]);
  $item = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$item || !$item['is_deleted']) {
    echo json_encode(['success' => false, 'message' => 'Item not found or not deleted']);
    exit;
  }

  // ✅ Restore file
  if ($item['type'] === 'file') {
    $trashPath = __DIR__ . "/../../" . ltrim($item['path'], '/');
    $ext = pathinfo($trashPath, PATHINFO_EXTENSION);
    $originalPath = "/srv/burol-storage/$userId/$fileId.$ext";
    $originalFullPath = __DIR__ . "/../../" . ltrim($originalPath, '/');

    if (!is_file($trashPath)) {
      echo json_encode(['success' => false, 'message' => 'File missing from trash']);
      exit;
    }

    if (!is_dir(dirname($originalFullPath))) {
      mkdir(dirname($originalFullPath), 0775, true);
    }

    rename($trashPath, $originalFullPath);

    $stmt = $pdo->prepare("UPDATE files SET is_deleted = 0, path = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$originalPath, $fileId]);

    $log = $pdo->prepare("
      INSERT INTO logs (id, file_id, user_id, action, details, source)
      VALUES (UUID(), ?, ?, 'restore', ?, 'dashboard')
    ");
    $log->execute([$fileId, $userId, "File restored from trash"]);

    echo json_encode(['success' => true, 'message' => 'File restored successfully']);
    exit;
  }

  // ✅ Restore folder recursively
  function restoreFolderAndContents(PDO $pdo, int $userId, string $folderId): void {
    // Restore this folder
    $stmt = $pdo->prepare("UPDATE files SET is_deleted = 0, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$folderId]);

    $log = $pdo->prepare("
      INSERT INTO logs (id, file_id, user_id, action, details, source)
      VALUES (UUID(), ?, ?, 'restore', ?, 'dashboard')
    ");
    $log->execute([$folderId, $userId, "Folder restored from trash"]);

    // Restore files inside this folder
    $stmt = $pdo->prepare("SELECT id, path FROM files WHERE parent_id = ? AND type = 'file' AND is_deleted = 1");
    $stmt->execute([$folderId]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($files as $file) {
      $trashPath = __DIR__ . "/../../" . ltrim($file['path'], '/');
      $ext = pathinfo($trashPath, PATHINFO_EXTENSION);
      $originalPath = "/srv/burol-storage/$userId/{$file['id']}.$ext";
      $originalFullPath = __DIR__ . "/../../" . ltrim($originalPath, '/');

      if (is_file($trashPath)) {
        if (!is_dir(dirname($originalFullPath))) {
          mkdir(dirname($originalFullPath), 0775, true);
        }
        rename($trashPath, $originalFullPath);
      }

      $stmt = $pdo->prepare("UPDATE files SET is_deleted = 0, path = ?, updated_at = NOW() WHERE id = ?");
      $stmt->execute([$originalPath, $file['id']]);

      $log = $pdo->prepare("
        INSERT INTO logs (id, file_id, user_id, action, details, source)
        VALUES (UUID(), ?, ?, 'restore', ?, 'dashboard')
      ");
      $log->execute([$file['id'], $userId, "File restored from trash"]);
    }

    // Recursively restore subfolders
    $stmt = $pdo->prepare("SELECT id FROM files WHERE parent_id = ? AND type = 'folder' AND is_deleted = 1");
    $stmt->execute([$folderId]);
    $subfolders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subfolders as $subfolder) {
      restoreFolderAndContents($pdo, $userId, $subfolder['id']);
    }
  }

  restoreFolderAndContents($pdo, $userId, $fileId);

  echo json_encode(['success' => true, 'message' => 'Folder and contents restored successfully']);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}