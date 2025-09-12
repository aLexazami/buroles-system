<?php
session_start();
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';

$userId = $_SESSION['user_id'] ?? '';
$type = $_POST['type'] ?? '';
$oldName = $_POST['old_name'] ?? '';
$newName = $_POST['new_name'] ?? '';
$path = trim($_POST['path'] ?? '', '/');

// ✅ Validate request
if (!$userId || !$type || !$oldName || !$newName) {
  setFlash('error', 'Invalid rename request.');
  redirectToManager($path);
}

try {
  $oldPath = resolveUploadPath($userId, $path, $oldName);
  $newPath = resolveUploadPath($userId, $path, $newName);

  handleRename($type, $oldPath, $newPath, $oldName, $newName);
} catch (RuntimeException $e) {
  error_log("Rename error: " . $e->getMessage());
  setFlash('error', 'An error occurred while renaming.');
}

redirectToManager($path);

// ✅ Helpers
function handleRename(string $type, string $oldPath, string $newPath, string $oldName, string $newName): void {
  if (!file_exists($oldPath)) {
    setFlash('error', ucfirst($type) . " '$oldName' not found.");
    return;
  }

  if (file_exists($newPath)) {
    setFlash('warning', "A $type named '$newName' already exists.");
    return;
  }

  if (rename($oldPath, $newPath)) {
    setFlash('success', ucfirst($type) . " renamed to '$newName'.");
  } else {
    setFlash('error', "Failed to rename $type '$oldName'.");
  }
}

function redirectToManager(string $path): void {
  header("Location: /pages/staff/file-manager.php?path=" . urlencode($path));
  exit;
}
?>