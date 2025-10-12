<?php
ob_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/folder-utils.php'; // deleteFolderAndContents()

header('Content-Type: application/json');

// 📥 Parse input
$input = json_decode(file_get_contents('php://input'), true);
$fileId = $input['id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$fileId) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Missing user or file ID']);
  exit;
}

try {
  // 🔍 Fetch file metadata
  $stmt = $pdo->prepare("SELECT id, name, path, type FROM files WHERE id = ? AND owner_id = ?");
  $stmt->execute([$fileId, $userId]);
  $file = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$file) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File or folder not found']);
    exit;
  }

  // 🔐 Begin transaction
  $pdo->beginTransaction();

  // 📝 Log deletion BEFORE removing from DB
  $log = $pdo->prepare("
    INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
    VALUES (UUID(), ?, ?, ?, 'delete-permanent', ?, 'dashboard')
  ");
  $log->execute([
    $fileId,
    $file['name'],
    $userId,
    'Item permanently deleted'
  ]);

  if ($file['type'] === 'folder') {
    // 🗂️ Recursively delete folder and contents
    $success = deleteFolderAndContents($pdo, $userId, $fileId, false); // false = not root transaction
    if (!$success) {
      throw new Exception("Folder deletion failed — see logs for details");
    }

    // 🗑️ Delete folder itself
    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND owner_id = ? AND type = 'folder'");
    $stmt->execute([$fileId, $userId]);
  } else {
    // 🧹 Delete file from disk
    $fullPath = __DIR__ . "/../../" . ltrim($file['path'], '/');
    if (is_file($fullPath)) {
      unlink($fullPath);
    }

    // 🗑️ Delete file from DB
    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
  }

  // ✅ Commit transaction
  $pdo->commit();
  http_response_code(200);
  echo json_encode(['success' => true, 'message' => 'Item permanently deleted']);
} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  error_log("❌ Permanent delete failed: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}