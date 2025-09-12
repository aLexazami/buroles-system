<?php
session_start();
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';

define('FILE_MANAGER_PATH', '/pages/staff/file-manager.php');

$userId = $_SESSION['user_id'] ?? '';
$targetPath = trim($_POST['path'] ?? '', '/');
$files = $_FILES['folder'] ?? [];

if (!$userId) {
  setFlash('error', 'Unauthorized access.');
  redirectToManager();
}

if (empty($files['name']) || !is_array($files['name']) || count($files['name']) === 0) {
  setFlash('error', 'The folder is empty. Please select a folder with files.');
  redirectToManager($targetPath);
}

// ✅ Create folder structure
$folderPaths = array_unique(array_filter(array_map(function ($name) {
  $clean = ltrim(str_replace(['../', './'], '', $name), '/');
  $folder = dirname($clean);
  return $folder !== '.' ? $folder : null;
}, $files['name'])));

foreach ($folderPaths as $folderPath) {
  try {
    $fullFolderPath = resolveUploadPath($userId, $targetPath, $folderPath);
    if (!is_dir($fullFolderPath)) {
      mkdir($fullFolderPath, 0755, true);
    }
  } catch (RuntimeException $e) {
    error_log("Folder creation error: " . $e->getMessage());
  }
}

// ✅ Save files
$validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'text/plain'];
$validFileCount = 0;

foreach ($files['name'] as $index => $name) {
  $tmpName = $files['tmp_name'][$index];
  $error = $files['error'][$index];
  $type = $files['type'][$index];

  if ($error !== UPLOAD_ERR_OK) continue;

  $cleanName = ltrim(str_replace(['../', './'], '', $name), '/');

  try {
    $destination = resolveUploadPath($userId, $targetPath, $cleanName);
    $destinationDir = dirname($destination);

    if (!is_dir($destinationDir)) {
      mkdir($destinationDir, 0755, true);
    }

    if (in_array($type, $validTypes)) {
      move_uploaded_file($tmpName, $destination);
      $validFileCount++;
    }
  } catch (RuntimeException $e) {
    error_log("Upload path error: " . $e->getMessage());
  }
}

// ✅ Flash message
if ($validFileCount > 0) {
  setFlash('success', "Folder uploaded with $validFileCount valid file" . ($validFileCount > 1 ? 's' : '') . '.');
} else {
  setFlash('warning', 'Folder structure created, but no valid files were uploaded.');
}

redirectToManager($targetPath);

// ✅ Redirect helper
function redirectToManager(string $path = ''): void {
  $url = FILE_MANAGER_PATH;
  if ($path !== '') $url .= '?path=' . urlencode($path);
  header("Location: $url");
  exit;
}
?>