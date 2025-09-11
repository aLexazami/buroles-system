<?php
$targetPath = $_POST['path'] ?? '';
$files = $_FILES['folder'] ?? [];

if (trim($targetPath) === '') {
  die('Missing target path.');
}

if (empty($files['name']) || !is_array($files['name']) || count($files['name']) === 0) {
  die('No files detected in the uploaded folder.');
}

// Optional: log the structure for debugging
// file_put_contents('upload-log.txt', print_r($files, true), FILE_APPEND);

foreach ($files['name'] as $index => $name) {
  $tmpName = $files['tmp_name'][$index];
  $error = $files['error'][$index];

  if ($error !== UPLOAD_ERR_OK) continue;

  // Try to use full_path if available
  $relativePath = isset($files['full_path'][$index]) ? $files['full_path'][$index] : $name;

  // Sanitize path to prevent traversal
  $relativePath = str_replace(['../', './'], '', $relativePath);

  $destination = rtrim($targetPath, '/') . '/' . $relativePath;
  $destinationDir = dirname($destination);

  if (!is_dir($destinationDir)) {
    mkdir($destinationDir, 0777, true);
  }

  move_uploaded_file($tmpName, $destination);
}

header("Location: /file-manager.php?path=" . urlencode($targetPath));
exit;