<?php
session_start();

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';

$userId     = $_SESSION['user_id'] ?? '';
$activeRole = $_SESSION['active_role_id'] ?? '';
$targetId   = $_POST['user_id'] ?? $userId;
$path       = sanitizePath($_POST['path'] ?? '');
$file       = $_FILES['file'] ?? null;

// ✅ Validate session and input
if (!$userId || !$activeRole) {
  setFlash('error', 'Unauthorized access.');
  redirectToManager($targetId, $path);
}

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
  setFlash('error', 'File upload failed.');
  redirectToManager($targetId, $path);
}

// 🔐 Staff can only upload to their own folder
if ($activeRole == 1 && $targetId !== $userId) {
  setFlash('error', 'Access denied. You can only upload to your own folder.');
  redirectToManager($userId, $path);
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
$filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', basename($file['name'])); // ✅ Strip unsafe characters

if (!array_key_exists($mimeType, $allowedTypes)) {
  error_log("Upload rejected: unsupported MIME type → $mimeType");
  setFlash('error', 'Unsupported file type.');
  redirectToManager($targetId, $path);
}

// ✅ Handle upload
try {
  $baseDir    = getUploadBaseByRoleUser('1', $targetId);
  $targetPath = resolveUploadPathFromBase($baseDir, $path, $filename);

  if (!file_exists(dirname($targetPath))) {
    error_log("Upload failed: target folder missing → " . dirname($targetPath));
    setFlash('error', 'Target folder does not exist.');
    redirectToManager($targetId, $path);
  }

  if (file_exists($targetPath)) {
    setFlash('warning', "File '$filename' already exists.");
    redirectToManager($targetId, $path);
  }

  if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    setFlash('success', "File '$filename' uploaded successfully.");
  } else {
    error_log("Upload failed: move_uploaded_file() returned false");
    setFlash('error', 'Failed to move uploaded file.');
  }
} catch (RuntimeException $e) {
  error_log("Upload error: " . $e->getMessage());
  setFlash('error', 'Upload failed due to server error.');
}

redirectToManager($targetId, $path);

// ✅ Redirect helper
function redirectToManager(string $userId, string $path = ''): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}
?>