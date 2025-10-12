<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php'; // isValidUuid()

$folderId = $_GET['folder_id'] ?? null;
$view = $_GET['view'] ?? 'default';
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$trail = [];

if ($view === 'trash') {
  // 🗑️ Trash view starts with "Trash"
  $trail[] = [
    'id' => null,
    'name' => 'Trash'
  ];

  if (isValidUuid($folderId)) {
    $currentId = $folderId;

    while ($currentId) {
      $stmt = $pdo->prepare("SELECT id, name, parent_id, is_deleted FROM files WHERE id = ? AND owner_id = ?");
      $stmt->execute([$currentId, $userId]);
      $folder = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$folder || !$folder['is_deleted']) break; // 🚫 Stop if folder is not deleted

      // Prepend to trail after "Trash"
      array_splice($trail, 1, 0, [[
        'id' => $folder['id'],
        'name' => $folder['name']
      ]]);

      $currentId = $folder['parent_id'];
    }
  }
} else {
  // 📁 Default view starts with "My Files"
  $trail[] = [
    'id' => null,
    'name' => 'My Files'
  ];

  if (isValidUuid($folderId)) {
    $currentId = $folderId;

    while ($currentId) {
      $stmt = $pdo->prepare("SELECT id, name, parent_id FROM files WHERE id = ? AND owner_id = ?");
      $stmt->execute([$currentId, $userId]);
      $folder = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$folder) break;

      array_splice($trail, 1, 0, [[
        'id' => $folder['id'],
        'name' => $folder['name']
      ]]);

      $currentId = $folder['parent_id'];
    }
  }
}

header('Content-Type: application/json');
echo json_encode($trail);