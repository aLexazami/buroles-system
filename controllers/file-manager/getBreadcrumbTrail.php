<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php'; // isValidUuid()

$folderId = $_GET['folder_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$trail = [];

// ✅ Always start with root
$trail[] = [
  'id' => null,
  'name' => 'My Files'
];

// ✅ If folderId is valid, walk up the tree
if (isValidUuid($folderId)) {
  $currentId = $folderId;

  while ($currentId) {
    $stmt = $pdo->prepare("SELECT id, name, parent_id FROM files WHERE id = ? AND owner_id = ?");
    $stmt->execute([$currentId, $userId]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$folder) break;

    // Prepend to trail
    array_splice($trail, 1, 0, [[
      'id' => $folder['id'],
      'name' => $folder['name']
    ]]);

    $currentId = $folder['parent_id'];
  }
}

header('Content-Type: application/json');
echo json_encode($trail);