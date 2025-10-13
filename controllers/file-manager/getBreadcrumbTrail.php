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

switch ($view) {
  case 'trash':
    $trail[] = ['id' => null, 'name' => 'Trash'];
    $query = "SELECT id, name, parent_id, is_deleted FROM files WHERE id = ? AND owner_id = ?";
    $checkDeleted = true;
    break;

  case 'shared-with-me':
    $trail[] = ['id' => null, 'name' => 'Shared with Me'];
    $query = "SELECT f.id, f.name, f.parent_id
            FROM files f
            JOIN access_control ac ON ac.file_id = f.id
            WHERE f.id = ? AND ac.user_id = ? AND ac.is_revoked = FALSE AND f.is_deleted = FALSE";
    $checkDeleted = false;
    break;

  case 'shared-by-me':
    $trail[] = ['id' => null, 'name' => 'Shared by Me'];
    $query = "SELECT f.id, f.name, f.parent_id FROM files f
              JOIN access_control ac ON ac.file_id = f.id
              WHERE f.id = ? AND ac.granted_by = ?";
    $checkDeleted = false;
    break;

  default:
    $trail[] = ['id' => null, 'name' => 'My Files'];
    $query = "SELECT id, name, parent_id FROM files WHERE id = ? AND owner_id = ?";
    $checkDeleted = false;
    break;
}

if (isValidUuid($folderId)) {
  $currentId = $folderId;

  while ($currentId) {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$currentId, $userId]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$folder) break;
    if ($checkDeleted && !$folder['is_deleted']) break;

    array_splice($trail, 1, 0, [[
      'id' => $folder['id'],
      'name' => $folder['name']
    ]]);

    $currentId = $folder['parent_id'];
  }
}

header('Content-Type: application/json');
echo json_encode($trail);
