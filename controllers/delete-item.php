<?php
session_start();

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';

$userId     = $_SESSION['user_id'] ?? '';
$activeRole = $_SESSION['active_role_id'] ?? '';
$targetId   = $_POST['user_id'] ?? $userId;
$type       = $_POST['type'] ?? '';
$name       = $_POST['name'] ?? '';
$path       = sanitizePath($_POST['path'] ?? '');

// ✅ Validate request
if (!$userId || !$activeRole || !$type || !$name) {
  setFlash('error', 'Invalid deletion request.');
  redirectToManager($targetId, $path);
}

// 🔐 Staff can only delete their own items
if ($activeRole == 1 && $targetId !== $userId) {
  setFlash('error', 'Access denied. You can only manage your own files.');
  redirectToManager($userId, $path);
}

try {
  // ✅ Resolve base path using role-first logic
  $baseDir    = getUploadBaseByRoleUser('1', $targetId);
  $targetPath = resolveUploadPathFromBase($baseDir, $path, $name);

  if (!$targetPath || !file_exists($targetPath)) {
    error_log("Deletion failed: path not found → $targetPath");
    setFlash('error', "Item '$name' could not be found.");
    redirectToManager($targetId, $path);
  }

  if ($type === 'file') {
    handleFileDeletion($targetPath, $name);
  } elseif ($type === 'folder') {
    handleFolderDeletion($targetPath, $name);
  } else {
    error_log("Unknown deletion type: $type");
    setFlash('error', 'Unknown item type.');
  }
} catch (RuntimeException $e) {
  error_log("Deletion error: " . $e->getMessage());
  setFlash('error', 'An error occurred while deleting the item.');
}

redirectToManager($targetId, $path);

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

function redirectToManager(string $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}
?>