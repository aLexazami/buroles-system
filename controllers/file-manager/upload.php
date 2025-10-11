<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/uuid.php';       // generateUuid(), isValidUuid()

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
$folderId = $_POST['folder_id'] ?? null;
if (!isValidUuid($folderId)) $folderId = null;

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success' => false, 'error' => 'Upload failed.']);
  exit;
}

$file = $_FILES['file'];
$uuid = generateUuid();
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$mime = mime_content_type($file['tmp_name']);
$size = filesize($file['tmp_name']);

$targetDir = __DIR__ . "/../../srv/burol-storage/$userId";
if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);

$storagePath = "$targetDir/$uuid.$ext";
$dbPath = "/srv/burol-storage/$userId/$uuid.$ext";

if (!move_uploaded_file($file['tmp_name'], $storagePath)) {
  echo json_encode(['success' => false, 'error' => 'Failed to save file.']);
  exit;
}

$stmt = $pdo->prepare("INSERT INTO files (id, name, type, path, parent_id, owner_id, size, mime_type)
  VALUES (?, ?, 'file', ?, ?, ?, ?, ?)");
$stmt->execute([
  $uuid,
  basename($file['name']),
  $dbPath,
  $folderId,
  $userId,
  $size,
  $mime
]);

echo json_encode([
  'success' => true,
  'item' => [
    'id' => $uuid,
    'name' => basename($file['name']),
    'type' => 'file',
    'path' => $dbPath,
    'parent_id' => $folderId,
    'owner_id' => $userId,
    'size' => $size,
    'mime_type' => $mime,
    'permissions' => ['delete', 'share', 'comment']
  ]
]);
exit;