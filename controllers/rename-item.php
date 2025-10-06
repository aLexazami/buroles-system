<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';
require_once __DIR__ . '/../helpers/folder-utils.php';

function canRenameItem(string $userId, string $targetId, int $activeRoleId, int $originalRoleId): bool {
  if (in_array($originalRoleId, [2, 99])) return true;
  return $activeRoleId === 1 && $userId === $targetId;
}

function redirectToManager(string $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}

$userId         = $_SESSION['user_id'] ?? '';
$activeRoleId   = $_SESSION['active_role_id'] ?? '';
$originalRoleId = $_SESSION['original_role_id'] ?? '';
$targetId       = $_POST['user_id'] ?? $userId;
$type           = $_POST['type'] ?? '';
$rawOldName     = $_POST['old_name'] ?? '';
$rawNewName     = $_POST['new_name'] ?? '';
$currentPath    = sanitizePath($_POST['path'] ?? '');

if (!$userId || !$activeRoleId || !$originalRoleId || !$type || !$rawOldName || !$rawNewName) {
  setFlash('error', 'Invalid rename request.');
  return redirectToManager($userId, $currentPath);
}

if (!canRenameItem($userId, $targetId, (int)$activeRoleId, (int)$originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to rename items here.');
  return redirectToManager($userId, $currentPath);
}

try {
  $baseDir = getUploadBaseByRoleUser((int)$activeRoleId, $targetId);

  $oldName      = trim($rawOldName, '/');
  $newExtension = pathinfo($rawNewName, PATHINFO_EXTENSION);
  $newBaseName  = sanitizeSegment(pathinfo($rawNewName, PATHINFO_FILENAME));
  $newName      = $newExtension ? "$newBaseName.$newExtension" : $newBaseName;

  $oldExtension = pathinfo($oldName, PATHINFO_EXTENSION);
  if (!$newExtension && $oldExtension) {
    $newName .= ".$oldExtension";
  }

  $oldPath = resolveUploadPathFromBase($baseDir, $currentPath, $oldName);
  $newPath = resolveUploadPathFromBase($baseDir, $currentPath, $newName);

  if (!$oldPath || !$newPath) {
    setFlash('error', 'Unable to resolve file paths for renaming.');
    return redirectToManager($targetId, $currentPath);
  }

  $success = is_dir($oldPath)
    ? moveFolderRecursively($oldPath, $newPath)
    : rename($oldPath, $newPath);

  if (!$success && strtolower($oldName) === strtolower($newName) && $oldName !== $newName) {
    $tempName = $newName . '__temp__' . uniqid();
    $tempPath = resolveUploadPathFromBase($baseDir, $currentPath, $tempName);

    if (rename($oldPath, $tempPath) && rename($tempPath, $newPath)) {
      $success = true;
    }
  }

  if (!$success) {
    setFlash('error', "Failed to rename $type '$oldName'.");
    return redirectToManager($targetId, $currentPath);
  }

  $relativeOldPath = sanitizePath($currentPath !== '' ? "$currentPath/$oldName" : $oldName);
  $relativeNewPath = sanitizePath($currentPath !== '' ? "$currentPath/$newName" : $newName);

  $scopedOldPath = "uploads/staff/$userId/" . ltrim($relativeOldPath, '/');
  $scopedNewPath = "uploads/staff/$userId/" . ltrim($relativeNewPath, '/');

  if ($type === 'file') {
    $stmt = $pdo->prepare("UPDATE files SET name = ?, path = ?, updated_at = NOW() WHERE path = ? AND owner_id = ?");
    $stmt->execute([$newName, $scopedNewPath, $scopedOldPath, $userId]);

  } elseif ($type === 'folder') {
    $stmt = $pdo->prepare("UPDATE folders SET name = ?, path = ?, updated_at = NOW() WHERE path = ? AND owner_id = ?");
    $stmt->execute([$newName, $scopedNewPath, $scopedOldPath, $userId]);

    $oldPrefix = $scopedOldPath;
    $newPrefix = $scopedNewPath;

    $stmt = $pdo->prepare("UPDATE folders SET path = REPLACE(path, ?, ?), updated_at = NOW() WHERE path LIKE ?");
    $stmt->execute([$oldPrefix, $newPrefix, "$oldPrefix/%"]);

    $stmt = $pdo->prepare("UPDATE files SET path = REPLACE(path, ?, ?), updated_at = NOW() WHERE path LIKE ?");
    $stmt->execute(["$oldPrefix/", "$newPrefix/", "$oldPrefix/%"]);
  }

  setFlash('success', ucfirst($type) . " renamed to '$newName'.");

} catch (RuntimeException) {
  setFlash('error', 'An error occurred while renaming.');
}

redirectToManager($targetId, $currentPath);