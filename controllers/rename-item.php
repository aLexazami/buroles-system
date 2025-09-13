<?php
session_start();

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';
require_once __DIR__ . '/../helpers/folder-utils.php';

$viewerId   = $_SESSION['user_id'] ?? '';
$activeRole = $_SESSION['active_role_id'] ?? '';
$targetId   = $_POST['user_id'] ?? $viewerId;
$type       = $_POST['type'] ?? '';
$rawOldName = $_POST['old_name'] ?? '';
$rawNewName = $_POST['new_name'] ?? '';
$path       = sanitizePath($_POST['path'] ?? '');

// ✅ Validate request
if (!$viewerId || !$activeRole || !$type || !$rawOldName || !$rawNewName) {
  setFlash('error', 'Invalid rename request.');
  redirectToManager($targetId, $path);
}

// 🔐 Staff can only rename their own items
if ($activeRole == 1 && $targetId !== $viewerId) {
  setFlash('error', 'Access denied. You can only rename your own files.');
  redirectToManager($viewerId, $path);
}

try {
  $baseDir = getUploadBaseByRoleUser('1', $targetId);

  // ✅ Preserve original name for resolution
  $oldName = trim($rawOldName, '/');

  // ✅ Extract and sanitize new name components
  $newExtension = pathinfo($rawNewName, PATHINFO_EXTENSION);
  $newBaseName  = sanitizeSegment(pathinfo($rawNewName, PATHINFO_FILENAME));
  $newName      = $newExtension ? $newBaseName . '.' . $newExtension : $newBaseName;

  // ✅ Append original extension if missing
  $oldExtension = pathinfo($oldName, PATHINFO_EXTENSION);
  if (!$newExtension && $oldExtension) {
    $newName .= '.' . $oldExtension;
  }

  // ✅ Resolve full paths
  $oldPath = resolveUploadPathFromBase($baseDir, $path, $oldName);
  $newPath = resolveUploadPathFromBase($baseDir, $path, $newName);

  if (!$oldPath || !$newPath) {
    error_log("Path resolution failed: oldPath=$oldPath, newPath=$newPath");
    setFlash('error', 'Unable to resolve file paths for renaming.');
    redirectToManager($targetId, $path);
  }

  error_log("Renaming from: $oldPath to: $newPath");

  handleRename($type, $oldPath, $newPath, $oldName, $newName);

  redirectToManager($targetId, $path);
} catch (RuntimeException $e) {
  error_log("Rename error: " . $e->getMessage());
  setFlash('error', 'An error occurred while renaming.');
  redirectToManager($targetId, $path);
}

// ✅ Helpers
function handleRename(string $type, string $oldPath, string $newPath, string $oldName, string $newName): void {
  if (!file_exists($oldPath)) {
    error_log("handleRename: '$oldPath' not found.");
    setFlash('error', ucfirst($type) . " '$oldName' not found.");
    return;
  }

  if (file_exists($newPath)) {
    setFlash('warning', "A $type named '$newName' already exists.");
    return;
  }

  $success = false;

  if (is_dir($oldPath)) {
    error_log("handleRename: using recursive move for folder '$oldPath'");
    $success = moveFolderRecursively($oldPath, $newPath);
  } else {
    $success = rename($oldPath, $newPath);
  }

  if ($success) {
    setFlash('success', ucfirst($type) . " renamed to '$newName'.");
  } else {
    error_log("handleRename: failed to rename '$oldPath' to '$newPath'");
    setFlash('error', "Failed to rename $type '$oldName'.");
  }
}

function redirectToManager(string $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}
?>