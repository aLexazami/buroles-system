<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/uuid.php'; // generateUuid(), isValidUuid()

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
if (!is_numeric($userId)) {
  echo json_encode(['success' => false, 'error' => 'Invalid session']);
  exit;
}

$folderName = trim($_POST['folder_name'] ?? '');
if ($folderName === '') {
  echo json_encode(['success' => false, 'error' => 'Folder name is required']);
  exit;
}

$parentId = $_POST['parent_id'] ?? null;
if (!isValidUuid($parentId)) {
  $parentId = null;
}

$uuid = generateUuid();
$virtualPath = "/virtual/$userId/$uuid";

// Confirm user exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
$stmt->execute([$userId]);
if ($stmt->fetchColumn() == 0) {
  echo json_encode(['success' => false, 'error' => 'User not found']);
  exit;
}

// Insert folder
$stmt = $pdo->prepare("
  INSERT INTO files (id, name, type, path, parent_id, owner_id)
  VALUES (?, ?, 'folder', ?, ?, ?)
");
$stmt->execute([$uuid, $folderName, $virtualPath, $parentId, $userId]);

echo json_encode([
  'success' => true,
  'item' => [
    'id' => $uuid,
    'name' => $folderName,
    'type' => 'folder',
    'permissions' => ['delete', 'share', 'comment'],
    'owner_id' => $userId,
    'parent_id' => $parentId,
    'path' => $virtualPath
  ]
]);