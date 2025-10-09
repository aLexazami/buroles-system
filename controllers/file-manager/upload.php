<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';      // showFlash()
require_once __DIR__ . '/../../helpers/uuid.php';       // generateUuid(), isValidUuid()

$userId = $_SESSION['user_id'];
$folderId = $_POST['folder_id'] ?? null;

// ✅ Validate folderId format
if (!isValidUuid($folderId)) {
  $folderId = null;
}

// ✅ Validate file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
  showFlash('error', 'Upload failed.');
  header('Location: /pages/staff/file-manager.php?view=my-files');
  exit;
}

$file = $_FILES['file'];
$uuid = generateUuid();
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$mime = mime_content_type($file['tmp_name']);
$size = filesize($file['tmp_name']);

// ✅ Prepare storage paths
$targetDir = __DIR__ . "/../../srv/burol-storage/$userId";
if (!is_dir($targetDir)) {
  mkdir($targetDir, 0775, true);
}

$storagePath = "$targetDir/$uuid.$ext";                     // physical path
$dbPath = "/srv/burol-storage/$userId/$uuid.$ext";          // logical path stored in DB

// ✅ Move file to storage
if (!move_uploaded_file($file['tmp_name'], $storagePath)) {
  showFlash('error', 'Failed to save file.');
  header('Location: /pages/staff/file-manager.php?view=my-files');
  exit;
}

// ✅ Insert metadata into database
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

showFlash('success', 'File uploaded successfully.');
header('Location: /pages/staff/file-manager.php?view=my-files');