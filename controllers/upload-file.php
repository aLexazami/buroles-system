<?php
session_start();

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';

// 🧠 Extract session and POST data
$userId     = $_SESSION['user_id'] ?? '';
$activeRole = $_SESSION['active_role_id'] ?? '';
$targetId   = $_POST['user_id'] ?? $userId;
$path       = sanitizePath($_POST['path'] ?? '');
$file       = $_FILES['file'] ?? null;

// 🔐 Validate session and file input
if (!$userId || !$activeRole) {
  setFlash('error', 'Unauthorized access.');
  return redirectToManager($targetId, $path);
}

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
  setFlash('error', 'File upload failed.');
  return redirectToManager($targetId, $path);
}

// 🔐 Staff can only upload to their own folder
if ($activeRole === '1') {
  $targetId = $userId;
}

// ✅ Validate MIME type
$allowedTypes = [
  'application/pdf',
  'text/csv',
  'application/vnd.ms-excel',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'image/jpeg',
  'image/png'
];

$mimeType = mime_content_type($file['tmp_name']);
$filename = sanitizeSegment(pathinfo($file['name'], PATHINFO_FILENAME));
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$finalName = $extension ? "$filename.$extension" : $filename;

if (!in_array($mimeType, $allowedTypes)) {
  error_log("Upload rejected: user=$userId role=$activeRole MIME=$mimeType");
  setFlash('error', 'Unsupported file type.');
  return redirectToManager($targetId, $path);
}

// 📁 Handle upload
try {
  $baseDir    = getUploadBaseByRoleUser($activeRole, $targetId);
  $targetPath = resolveUploadPathFromBase($baseDir, $path, $finalName);

  if (!is_dir(dirname($targetPath))) {
    error_log("Upload failed: missing folder → " . dirname($targetPath));
    setFlash('error', 'Target folder does not exist.');
    return redirectToManager($targetId, $path);
  }

  if (file_exists($targetPath)) {
    setFlash('warning', "File '$finalName' already exists.");
    return redirectToManager($targetId, $path);
  }

  if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    setFlash('success', "File '$finalName' uploaded successfully.");
  } else {
    error_log("Upload failed: move_uploaded_file() failed for $targetPath");
    setFlash('error', 'Failed to move uploaded file.');
  }

} catch (RuntimeException $e) {
  error_log("Upload error: " . $e->getMessage());
  setFlash('error', 'Upload failed due to server error.');
}

redirectToManager($targetId, $path);

// 🔁 Redirect helper
function redirectToManager(string $userId, string $path = ''): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}
?>