<?php
session_start();
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';

$userId = $_SESSION['user_id'] ?? '';
$type = $_POST['type'] ?? '';
$name = $_POST['name'] ?? '';
$path = trim($_POST['path'] ?? '', '/');

// ✅ Validate request
if (!$userId || !$type || !$name) {
  setFlash('error', 'Invalid deletion request.');
  redirectToManager($path);
}

try {
  $targetPath = resolveUploadPath($userId, $path, $name);

  if ($type === 'file') {
    handleFileDeletion($targetPath, $name);
  } elseif ($type === 'folder') {
    handleFolderDeletion($targetPath, $name);
  } else {
    setFlash('error', 'Unknown item type.');
  }
} catch (RuntimeException $e) {
  error_log("Deletion error: " . $e->getMessage());
  setFlash('error', 'An error occurred while deleting the item.');
}

redirectToManager($path);

// ✅ Helpers
function handleFileDeletion(string $targetPath, string $name): void {
  if (is_file($targetPath)) {
    unlink($targetPath);
    setFlash('success', "File '$name' deleted successfully.");
  } else {
    setFlash('error', "File '$name' could not be found.");
  }
}

function handleFolderDeletion(string $targetPath, string $name): void {
  if (is_dir($targetPath)) {
    deleteFolderRecursive($targetPath);
    setFlash('success', "Folder '$name' deleted successfully.");
  } else {
    setFlash('error', "Folder '$name' could not be found.");
  }
}

function redirectToManager(string $path): void {
  header("Location: /pages/staff/file-manager.php?path=" . urlencode($path));
  exit;
}
?>