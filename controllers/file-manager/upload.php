<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/uuid.php';      // generateUuid(), isValidUuid()
require_once __DIR__ . '/../../helpers/path.php';      // resolveFolderPath(), buildVirtualPath(), resolveDiskPath(), ensureDirectoryExists()

header('Content-Type: application/json');

// ✅ Validate session
$userId = $_SESSION['user_id'] ?? null;
if (!is_numeric($userId)) {
  echo json_encode(['success' => false, 'error' => 'Invalid session']);
  exit;
}

// ✅ Validate folder ID
$folderId = $_POST['folder_id'] ?? null;
if (!isValidUuid($folderId)) {
  $folderId = null;
}

// ✅ Validate file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success' => false, 'error' => 'Upload failed.']);
  exit;
}

$file = $_FILES['file'];
$uuid = generateUuid();
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "$uuid.$ext";
$mime = mime_content_type($file['tmp_name']);
$size = filesize($file['tmp_name']);

// ✅ Resolve parent folder path and build full virtual path
$parentPath = resolveFolderPath($pdo, $folderId, $userId);
$virtualPath = buildVirtualPath($parentPath, $userId, $uuid);
$diskPath = resolveDiskPath($virtualPath);
ensureDirectoryExists(dirname($diskPath));

// ✅ Move file to disk
if (!move_uploaded_file($file['tmp_name'], $diskPath)) {
  echo json_encode(['success' => false, 'error' => 'Failed to save file.']);
  exit;
}

// ✅ Insert file record into database
$stmt = $pdo->prepare("
  INSERT INTO files (id, name, type, path, parent_id, owner_id, size, mime_type)
  VALUES (?, ?, 'file', ?, ?, ?, ?, ?)
");
$stmt->execute([
  $uuid,
  basename($file['name']),
  $virtualPath,
  $folderId,
  $userId,
  $size,
  $mime
]);

// ✅ Respond with file info
echo json_encode([
  'success' => true,
  'item' => [
    'id' => $uuid,
    'name' => basename($file['name']),
    'type' => 'file',
    'path' => $virtualPath,
    'parent_id' => $folderId,
    'owner_id' => $userId,
    'size' => $size,
    'mime_type' => $mime,
    'permissions' => ['delete', 'share', 'comment']
  ]
]);
exit;