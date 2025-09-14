<?php
session_start();

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';
require_once __DIR__ . '/../helpers/folder-utils.php';

// 🧠 Extract session and POST data
$userId     = $_SESSION['user_id'] ?? '';
$activeRole = $_SESSION['active_role_id'] ?? '';
$targetId   = $_POST['user_id'] ?? $userId;
$type       = $_POST['type'] ?? '';
$rawOldName = $_POST['old_name'] ?? '';
$rawNewName = $_POST['new_name'] ?? '';
$path       = sanitizePath($_POST['path'] ?? '');

// 🔐 Validate session and input
if (!$userId || !$activeRole || !$type || !$rawOldName || !$rawNewName) {
  setFlash('error', 'Invalid rename request.');
  return redirectToManager($targetId, $path);
}

// 🔐 Enforce staff-only self-management
if ($activeRole === '1') {
  $targetId = $userId;
}

try {
  // 📁 Resolve base path using role and target
  $baseDir = getUploadBaseByRoleUser($activeRole, $targetId);

  // 🧼 Sanitize and prepare filenames
  $oldName      = trim($rawOldName, '/');
  $newExtension = pathinfo($rawNewName, PATHINFO_EXTENSION);
  $newBaseName  = sanitizeSegment(pathinfo($rawNewName, PATHINFO_FILENAME));
  $newName      = $newExtension ? "$newBaseName.$newExtension" : $newBaseName;

  // 🧠 Preserve original extension if missing
  $oldExtension = pathinfo($oldName, PATHINFO_EXTENSION);
  if (!$newExtension && $oldExtension) {
    $newName .= ".$oldExtension";
  }

  // 📍 Resolve full paths
  $oldPath = resolveUploadPathFromBase($baseDir, $path, $oldName);
  $newPath = resolveUploadPathFromBase($baseDir, $path, $newName);

  if (!$oldPath || !$newPath) {
    error_log("Path resolution failed: oldPath=$oldPath, newPath=$newPath");
    setFlash('error', 'Unable to resolve file paths for renaming.');
    return redirectToManager($targetId, $path);
  }

  error_log("Renaming from: $oldPath to: $newPath");

  // 🔄 Perform rename
  if (!handleRename($type, $oldPath, $newPath, $oldName, $newName)) {
    return redirectToManager($targetId, $path);
  }

} catch (RuntimeException $e) {
  error_log("Rename error: " . $e->getMessage());
  setFlash('error', 'An error occurred while renaming.');
}

redirectToManager($targetId, $path);

// 🔧 Rename logic
function handleRename(string $type, string $oldPath, string $newPath, string $oldName, string $newName): bool {
  if (!file_exists($oldPath)) {
    error_log("handleRename: '$oldPath' not found.");
    setFlash('error', ucfirst($type) . " '$oldName' not found.");
    return false;
  }

  if (file_exists($newPath)) {
    setFlash('warning', "A $type named '$newName' already exists.");
    return false;
  }

  $success = is_dir($oldPath)
    ? moveFolderRecursively($oldPath, $newPath)
    : rename($oldPath, $newPath);

  if ($success) {
    setFlash('success', ucfirst($type) . " renamed to '$newName'.");
    return true;
  } else {
    error_log("handleRename: failed to rename '$oldPath' to '$newPath'");
    setFlash('error', "Failed to rename $type '$oldName'.");
    return false;
  }
}

// 🔁 Redirect helper
function redirectToManager(string $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}
?>