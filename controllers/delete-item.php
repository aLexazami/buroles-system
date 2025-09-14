<?php
session_start();

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';

// 🧠 Extract session and POST data
$userId     = $_SESSION['user_id'] ?? '';
$activeRole = $_SESSION['active_role_id'] ?? '';
$targetId   = $_POST['user_id'] ?? $userId;
$type       = $_POST['type'] ?? '';
$name       = $_POST['name'] ?? '';
$path       = sanitizePath($_POST['path'] ?? '');

// 🔐 Validate session and input
if (!$userId || !$activeRole || !$type || !$name) {
  setFlash('error', 'Invalid deletion request.');
  return redirectToManager($targetId, $path);
}

// 🔐 Staff can only delete their own items
if ($activeRole === '1') {
  $targetId = $userId;
}

try {
  // 📁 Resolve base path using role and target
  $baseDir    = getUploadBaseByRoleUser($activeRole, $targetId);
  $targetPath = resolveUploadPathFromBase($baseDir, $path, $name);

  if (!$targetPath || !file_exists($targetPath)) {
    error_log("Deletion failed: user=$userId role=$activeRole path=$targetPath");
    setFlash('error', "Item '$name' could not be found.");
    return redirectToManager($targetId, $path);
  }

  // 🔄 Perform deletion
  $success = match ($type) {
    'file'   => handleFileDeletion($targetPath, $name),
    'folder' => handleFolderDeletion($targetPath, $name),
    default  => handleUnknownType($type)
  };

  if (!$success) {
    return redirectToManager($targetId, $path);
  }

} catch (RuntimeException $e) {
  error_log("Deletion error: " . $e->getMessage());
  setFlash('error', 'An error occurred while deleting the item.');
}

redirectToManager($targetId, $path);

// ✅ Helpers
function handleFileDeletion(string $targetPath, string $name): bool {
  if (is_file($targetPath)) {
    unlink($targetPath);
    setFlash('success', "File '$name' deleted successfully.");
    return true;
  }
  setFlash('error', "File '$name' could not be found.");
  return false;
}

function handleFolderDeletion(string $targetPath, string $name): bool {
  if (is_dir($targetPath)) {
    if (deleteFolderRecursive($targetPath)) {
      setFlash('success', "Folder '$name' deleted successfully.");
      return true;
    } else {
      error_log("Folder deletion failed: $targetPath");
      setFlash('error', "Failed to delete folder '$name'.");
      return false;
    }
  }
  setFlash('error', "Folder '$name' could not be found.");
  return false;
}

function handleUnknownType(string $type): bool {
  error_log("Unknown deletion type: $type");
  setFlash('error', 'Unknown item type.');
  return false;
}

function redirectToManager(string $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}
?>