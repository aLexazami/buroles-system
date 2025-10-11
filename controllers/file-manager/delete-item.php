<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/folder-utils.php'; // deleteFolderAndContents()

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$fileId = $input['id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$fileId) {
  echo json_encode(['success' => false, 'message' => 'Missing user or file ID']);
  exit;
}

try {
  // 🔐 Check ownership or delegated delete permission
  $check = $pdo->prepare("
    SELECT
      f.type,
      f.path,
      CASE
        WHEN f.owner_id = ? THEN 'owner'
        WHEN ac.permission = 'delete' THEN 'delete'
        ELSE NULL
      END AS effective_permission
    FROM files f
    LEFT JOIN access_control ac
      ON ac.file_id = f.id AND ac.user_id = ? AND ac.is_revoked = 0
    WHERE f.id = ?
  ");
  $check->execute([$userId, $userId, $fileId]);
  $result = $check->fetch(PDO::FETCH_ASSOC);

  if (!$result || !$result['effective_permission']) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
  }

  $type = $result['type'];
  $path = $result['path'];
  $source = ($result['effective_permission'] === 'owner') ? 'owner-delete' : 'delegated-delete';

  $success = false;

  if ($type === 'folder') {
    // 🧹 Recursively delete folder and contents
    $success = deleteFolderAndContents($pdo, $userId, $fileId);
  } elseif ($type === 'file') {
    // 🧹 Move file to trash instead of deleting
    $realPath = __DIR__ . "/../../" . ltrim($path, '/');
    $trashPath = "/srv/burol-storage/$userId/trash/" . basename($realPath);
    $trashFullPath = __DIR__ . "/../../" . ltrim($trashPath, '/');

    // Ensure trash folder exists
    $trashDir = dirname($trashFullPath);
    if (!is_dir($trashDir)) mkdir($trashDir, 0775, true);

    // Move file to trash
    if (is_file($realPath)) {
      rename($realPath, $trashFullPath);
    }

    // 🗑️ Soft delete in DB and update path
    $stmt = $pdo->prepare("UPDATE files SET is_deleted = 1, path = ?, updated_at = NOW() WHERE id = ?");
    $success = $stmt->execute([$trashPath, $fileId]);
  }

  // 📝 Log the action
  $log = $pdo->prepare("
    INSERT INTO logs (id, file_id, user_id, action, details, source)
    VALUES (UUID(), ?, ?, 'delete', ?, 'dashboard')
  ");
  $log->execute([$fileId, $userId, "Soft delete triggered via $source"]);

  echo json_encode([
    'success' => $success,
    'message' => $success ? 'Item deleted successfully' : 'Failed to delete item'
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Server error: ' . $e->getMessage()
  ]);
}