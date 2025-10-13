<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/log.php'; // logAction()

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
  echo json_encode(['success' => false, 'message' => 'Missing user session']);
  exit;
}

try {
  // 🔍 Fetch all deleted items owned by user
  $stmt = $pdo->prepare("SELECT id, type, path FROM files WHERE owner_id = ? AND is_deleted = 1");
  $stmt->execute([$userId]);
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($items as $item) {
    $itemId = $item['id'];
    $itemType = $item['type'];
    $relativePath = ltrim($item['path'], '/');
    $fullPath = __DIR__ . '/../../' . $relativePath;

    // 🧹 Delete from disk
    if (file_exists($fullPath)) {
      if (is_dir($fullPath)) {
        deleteFolderRecursive($fullPath);
      } else {
        unlink($fullPath);
      }
    }

    // 🧠 Delete from database
    $stmtDelete = $pdo->prepare("DELETE FROM files WHERE id = ? AND owner_id = ?");
    $stmtDelete->execute([$itemId, $userId]);

    // 🧾 Log deletion
    logAction($pdo, $userId, $itemId, $itemName, 'emptyTrash', 'Item permanently deleted via Empty Trash');
  }

  echo json_encode(['success' => true, 'message' => 'Trash emptied successfully']);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// 🔧 Recursively delete folder contents
function deleteFolderRecursive(string $folderPath): void {
  $items = array_diff(scandir($folderPath), ['.', '..']);
  foreach ($items as $item) {
    $itemPath = $folderPath . DIRECTORY_SEPARATOR . $item;
    if (is_dir($itemPath)) {
      deleteFolderRecursive($itemPath);
    } else {
      unlink($itemPath);
    }
  }
  rmdir($folderPath);
}