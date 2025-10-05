<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';
require_once __DIR__ . '/../helpers/folder-utils.php';

// 🧠 Extract session and POST data
$userId         = $_SESSION['user_id'] ?? '';
$activeRoleId   = $_SESSION['active_role_id'] ?? '';
$originalRoleId = $_SESSION['original_role_id'] ?? '';
$targetId       = $_POST['user_id'] ?? $userId;
$type           = $_POST['type'] ?? '';
$rawOldName     = $_POST['old_name'] ?? '';
$rawNewName     = $_POST['new_name'] ?? '';
$currentPath    = sanitizePath($_POST['path'] ?? '');

// 🔐 Validate session
if (!$userId || !$activeRoleId || !$originalRoleId || !$type || !$rawOldName || !$rawNewName) {
  setFlash('error', 'Invalid rename request.');
  return redirectToManager($userId, $currentPath);
}

// 🔐 Access control
function canRenameItem(string $userId, string $targetId, int $activeRoleId, int $originalRoleId): bool {
  if (in_array($originalRoleId, [2, 99])) return true;
  return $activeRoleId === 1 && $userId === $targetId;
}

if (!canRenameItem($userId, $targetId, (int)$activeRoleId, (int)$originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to rename items here.');
  return redirectToManager($userId, $currentPath);
}

try {
  // 📁 Resolve base path
  $baseDir = getUploadBaseByRoleUser((int)$activeRoleId, $targetId);

  // 🧼 Sanitize filenames
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
  $oldPath = resolveUploadPathFromBase($baseDir, $currentPath, $oldName);
  $newPath = resolveUploadPathFromBase($baseDir, $currentPath, $newName);

  if (!$oldPath || !$newPath) {
    error_log("Rename failed: path resolution → old=$oldPath, new=$newPath");
    setFlash('error', 'Unable to resolve file paths for renaming.');
    return redirectToManager($targetId, $currentPath);
  }

  // 🔄 Perform rename
  $success = is_dir($oldPath)
    ? moveFolderRecursively($oldPath, $newPath)
    : rename($oldPath, $newPath);

  if (!$success) {
    error_log("Rename failed: $oldPath → $newPath");
    setFlash('error', "Failed to rename $type '$oldName'.");
    return redirectToManager($targetId, $currentPath);
  }

  // 🧠 Update metadata in database
  $relativeOldPath = $currentPath !== '' ? "$currentPath/$oldName" : $oldName;
  $relativeNewPath = $currentPath !== '' ? "$currentPath/$newName" : $newName;

  if ($type === 'file') {
    $stmt = $pdo->prepare("UPDATE files SET name = ?, path = ?, updated_at = NOW() WHERE path = ? AND owner_id = ?");
    $stmt->execute([$newName, $newPath, $oldPath, $userId]);
  } elseif ($type === 'folder') {
    $stmt = $pdo->prepare("UPDATE folders SET name = ?, path = ?, updated_at = NOW() WHERE path = ? AND owner_id = ?");
    $stmt->execute([$newName, $relativeNewPath, $relativeOldPath, $userId]);
  }

  setFlash('success', ucfirst($type) . " renamed to '$newName'.");

} catch (RuntimeException $e) {
  error_log("Rename error: " . $e->getMessage());
  setFlash('error', 'An error occurred while renaming.');
}

redirectToManager($targetId, $currentPath);

// 🔁 Redirect helper
function redirectToManager(string $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}
?>