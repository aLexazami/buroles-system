<?php
session_start();
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';

$userId = $_SESSION['user_id'] ?? '';
$path = trim($_POST['path'] ?? '', '/');
$file = $_FILES['file'] ?? null;

// ✅ Validate session and input
if (!$userId) {
  setFlash('error', 'Unauthorized access.');
  redirectToManager();
}

if (preg_match('/\.\.|[<>:"|?*]/', $path)) {
  setFlash('error', 'Invalid folder path.');
  redirectToManager();
}

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
  setFlash('error', 'File upload failed.');
  redirectToManager($path);
}

// ✅ Validate MIME type
$allowedTypes = [
  'application/pdf' => 'pdf',
  'text/csv' => 'csv',
  'application/vnd.ms-excel' => 'xls',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
  'application/msword' => 'doc',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
  'image/jpeg' => 'jpg',
  'image/png' => 'png'
];

$mimeType = mime_content_type($file['tmp_name']);
$filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', basename($file['name']));

if (!array_key_exists($mimeType, $allowedTypes)) {
  setFlash('error', 'Unsupported file type.');
  redirectToManager($path);
}

// ✅ Handle upload
try {
  $targetPath = resolveUploadPath($userId, $path, $filename);

  if (!file_exists(dirname($targetPath))) {
    setFlash('error', 'Target folder does not exist.');
    redirectToManager($path);
  }

  if (file_exists($targetPath)) {
    setFlash('warning', "File '$filename' already exists.");
    redirectToManager($path);
  }

  if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    setFlash('success', "File '$filename' uploaded successfully.");
  } else {
    setFlash('error', 'Failed to move uploaded file.');
  }
} catch (RuntimeException $e) {
  error_log("Upload error: " . $e->getMessage());
  setFlash('error', 'Upload failed due to server error.');
}

redirectToManager($path);

// ✅ Redirect helper
function redirectToManager(string $path = ''): void {
  $url = '/pages/staff/file-manager.php';
  if ($path !== '') $url .= '?path=' . urlencode($path);
  header("Location: $url");
  exit; // ✅ Ensures no double execution
}