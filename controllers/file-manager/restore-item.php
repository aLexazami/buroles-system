<?php
ob_start();
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
  // 🔍 Fetch file info
  $stmt = $pdo->prepare("SELECT path, name, type, is_deleted FROM files WHERE id = ? AND owner_id = ?");
  $stmt->execute([$fileId, $userId]);
  $file = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$file || !$file['is_deleted']) {
    echo json_encode(['success' => false, 'message' => 'File not found or not deleted']);
    exit;
  }

  if ($file['type'] !== 'file') {
    echo json_encode(['success' => false, 'message' => 'Only files can be restored']);
    exit;
  }

  // 🧩 Reconstruct original path
  $trashPath = __DIR__ . "/../../" . ltrim($file['path'], '/');
  $ext = pathinfo($trashPath, PATHINFO_EXTENSION);
  $originalPath = "/srv/burol-storage/$userId/$fileId.$ext";
  $originalFullPath = __DIR__ . "/../../" . ltrim($originalPath, '/');

  // 🧼 Ensure target folder exists
  $targetDir = dirname($originalFullPath);
  if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);

  // 🔁 Move file back from trash
  if (!is_file($trashPath)) {
    echo json_encode(['success' => false, 'message' => 'File missing from trash']);
    exit;
  }

  rename($trashPath, $originalFullPath);

  // ✅ Update DB record
  $stmt = $pdo->prepare("UPDATE files SET is_deleted = 0, path = ?, updated_at = NOW() WHERE id = ?");
  $stmt->execute([$originalPath, $fileId]);

  // 📝 Log restore
  $log = $pdo->prepare("
    INSERT INTO logs (id, file_id, user_id, action, details, source)
    VALUES (UUID(), ?, ?, 'restore', ?, 'dashboard')
  ");
  $log->execute([$fileId, $userId, "File restored from trash"]);

  http_response_code(200);
  echo json_encode(['success' => true, 'message' => 'File restored successfully']);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}