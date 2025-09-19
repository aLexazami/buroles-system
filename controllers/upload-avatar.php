<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers/flash.php';

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

// 🔍 Confirm GD extension is loaded
if (!extension_loaded('gd')) {
  setFlash('error', 'GD extension not loaded.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// 🔍 Confirm Intervention Image class availability
if (!class_exists('Intervention\Image\ImageManager')) {
  setFlash('error', 'ImageManager class not found.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Ensure session is valid
if (!isset($_SESSION['user_id'])) {
  setFlash('error', 'Session expired. Please log in again.');
  header('Location: /index.php');
  exit;
}

$userId = $_SESSION['user_id'];

// ✅ Config
$baseUploadDir   = __DIR__ . '/../assets/img/uploads';
$userUploadDir   = $baseUploadDir . DIRECTORY_SEPARATOR . $userId;
$webPathPrefix   = '/assets/img/uploads/' . $userId . '/';
$allowedTypes    = ['image/jpeg', 'image/png', 'image/gif'];
$validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$maxSize         = 5 * 1024 * 1024; // 5MB

// ✅ Validate file presence
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
  setFlash('error', 'No file uploaded or upload error.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

$file = $_FILES['avatar'];

// ✅ Validate MIME type and size
if (!in_array($file['type'], $allowedTypes) || $file['size'] > $maxSize) {
  setFlash('error', 'Invalid file type or size.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Ensure upload directory exists
if (!is_dir($userUploadDir)) {
  mkdir($userUploadDir, 0775, true);
}

// 🧹 Remove old avatar files
$existingFiles = glob($userUploadDir . '/avatar_' . $userId . '_*.{jpg,jpeg,png,gif}', GLOB_BRACE);
foreach ($existingFiles as $oldFile) {
  if (is_file($oldFile)) {
    unlink($oldFile);
  }
}

// ✅ Generate unique filename
$originalName = basename($file['name']);
$extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $validExtensions)) {
  setFlash('error', 'Invalid file extension.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

$filename   = 'avatar_' . $userId . '_' . time() . '.' . $extension;
$targetPath = $userUploadDir . DIRECTORY_SEPARATOR . $filename;

// ✅ Move file securely
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
  setFlash('error', 'Failed to move uploaded file.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// 🖼️ Resize image to 256×256
try {
  $manager = new ImageManager(new Driver());
  $manager->read($targetPath)
          ->scale(256, 256)
          ->save($targetPath);
} catch (Exception $e) {
  setFlash('error', 'Image resize failed: ' . $e->getMessage());
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Update session and DB
$avatarPath = $webPathPrefix . $filename;
$_SESSION['avatar_path'] = $avatarPath;

$stmt = $pdo->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
$stmt->execute([$avatarPath, $userId]);

// ✅ Flash success and redirect
setFlash('success', 'Avatar updated successfully.');
$redirect = $_SERVER['HTTP_REFERER'] ?? '/pages/profile.php';
header("Location: $redirect");
exit;