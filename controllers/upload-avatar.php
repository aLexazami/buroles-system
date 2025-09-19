<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

// 🔍 Confirm GD extension is loaded
if (!extension_loaded('gd')) {
  file_put_contents(__DIR__ . '/../debug.txt', "❌ GD extension not loaded\n", FILE_APPEND);
  exit;
}

// 🔍 Confirm Intervention Image class availability
if (!class_exists('Intervention\Image\ImageManager')) {
  file_put_contents(__DIR__ . '/../debug.txt', "❌ Intervention\\Image\\ImageManager class not found\n", FILE_APPEND);
  exit;
}

// ✅ Ensure session is valid
if (!isset($_SESSION['user_id'])) {
  file_put_contents(__DIR__ . '/../debug.txt', "❌ Missing user_id in session\n", FILE_APPEND);
  header('Location: /index.php');
  exit;
}

$userId = $_SESSION['user_id'];

// 🔍 Log incoming file
file_put_contents(__DIR__ . '/../debug.txt', print_r($_FILES, true), FILE_APPEND);

// ✅ Config
$baseUploadDir   = __DIR__ . '/../assets/img/uploads';
$userUploadDir   = $baseUploadDir . DIRECTORY_SEPARATOR . $userId;
$webPathPrefix   = '/assets/img/uploads/' . $userId . '/';
$allowedTypes    = ['image/jpeg', 'image/png', 'image/gif'];
$validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$maxSize         = 5 * 1024 * 1024; // 5MB

// ✅ Validate file presence
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
  file_put_contents(__DIR__ . '/../debug.txt', "❌ No file uploaded or upload error\n", FILE_APPEND);
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

$file = $_FILES['avatar'];

// ✅ Validate MIME type and size
if (!in_array($file['type'], $allowedTypes) || $file['size'] > $maxSize) {
  file_put_contents(__DIR__ . '/../debug.txt', "❌ Invalid file type or size\n", FILE_APPEND);
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Ensure upload directory exists
if (!is_dir($userUploadDir)) {
  mkdir($userUploadDir, 0775, true);
  file_put_contents(__DIR__ . '/../debug.txt', "📁 Created user upload directory: $userUploadDir\n", FILE_APPEND);
}

// 🧹 Remove old avatar files
$existingFiles = glob($userUploadDir . '/avatar_' . $userId . '_*.{jpg,jpeg,png,gif}', GLOB_BRACE);
foreach ($existingFiles as $oldFile) {
  if (is_file($oldFile)) {
    unlink($oldFile);
    file_put_contents(__DIR__ . '/../debug.txt', "🗑️ Deleted old avatar: $oldFile\n", FILE_APPEND);
  }
}

// ✅ Generate unique filename
$originalName = basename($file['name']);
$extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $validExtensions)) {
  file_put_contents(__DIR__ . '/../debug.txt', "❌ Invalid file extension: $extension\n", FILE_APPEND);
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

$filename   = 'avatar_' . $userId . '_' . time() . '.' . $extension;
$targetPath = $userUploadDir . DIRECTORY_SEPARATOR . $filename;

// ✅ Move file securely
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
  file_put_contents(__DIR__ . '/../debug.txt', "❌ Failed to move file to $targetPath\n", FILE_APPEND);
  exit;
}

file_put_contents(__DIR__ . '/../debug.txt', "✅ File saved to $targetPath\n", FILE_APPEND);

// 🖼️ Resize image to 256×256
try {
  $manager = new ImageManager(new Driver());
  $manager->read($targetPath)
          ->scale(256, 256)
          ->save($targetPath);

  file_put_contents(__DIR__ . '/../debug.txt', "✅ Image resized to 256x256\n", FILE_APPEND);
} catch (Exception $e) {
  file_put_contents(__DIR__ . '/../debug.txt', "❌ Image resize failed: " . $e->getMessage() . "\n", FILE_APPEND);
}

// ✅ Update session and DB
$avatarPath = $webPathPrefix . $filename;
$_SESSION['avatar_path'] = $avatarPath;

$stmt = $pdo->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
$stmt->execute([$avatarPath, $userId]);

file_put_contents(__DIR__ . '/../debug.txt', "✅ DB updated for user_id: $userId\n", FILE_APPEND);

// ✅ Redirect with success flag
$redirect = $_SERVER['HTTP_REFERER'] ?? '/pages/profile.php';
header("Location: $redirect?avatar=updated");
exit;
