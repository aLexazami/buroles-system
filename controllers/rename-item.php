<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/logger.php';

// ✅ Local path sanitization
function sanitizePath(string $path): string {
  $path = trim($path);
  $path = str_replace(['\\', '//'], '/', $path);
  $path = preg_replace('#/+#', '/', $path); // collapse multiple slashes
  $path = preg_replace('#\.\./#', '', $path); // remove parent traversal
  return ltrim($path, '/');
}

// ✅ Safety check for filesystem paths
function isSafePath(string $path): bool {
  $realBase = realpath(__DIR__ . '/../uploads');
  $checkPath = file_exists($path) ? $path : dirname($path);
  $realPath = realpath($checkPath);

  if ($realPath === false || $realBase === false) return false;

  $realPath = str_replace('\\', '/', $realPath);
  $realBase = str_replace('\\', '/', $realBase);

  return str_starts_with($realPath, $realBase);
}

// ✅ Recursive folder move fallback
function moveFolderRecursively(string $source, string $destination): bool {
  if (!is_dir($source)) return false;
  if (!mkdir($destination, 0755, true)) return false;

  foreach (scandir($source) as $item) {
    if ($item === '.' || $item === '..') continue;

    $src = $source . DIRECTORY_SEPARATOR . $item;
    $dst = $destination . DIRECTORY_SEPARATOR . $item;

    if (is_dir($src)) {
      if (!moveFolderRecursively($src, $dst)) return false;
    } else {
      if (!rename($src, $dst)) return false;
    }
  }

  return rmdir($source);
}

function canRenameItem(string $userId, string $targetId, int $activeRoleId, int $originalRoleId): bool {
  return in_array($originalRoleId, [2, 99]) || ($activeRoleId === 1 && $userId === $targetId);
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
$scopedPath     = sanitizePath($_POST['path'] ?? '');

if (!$userId || !$activeRoleId || !$originalRoleId || !$type || !$rawOldName || !$rawNewName) {
  setFlash('error', 'Invalid rename request.');
  logRenameEvent('Invalid rename request', compact('userId', 'type', 'rawOldName', 'rawNewName', 'scopedPath'), true);
  return redirectToManager($userId, $scopedPath);
}

if (!canRenameItem($userId, $targetId, (int)$activeRoleId, (int)$originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to rename items here.');
  logRenameEvent('Access denied for rename', compact('userId', 'targetId', 'activeRoleId', 'originalRoleId'), true);
  return redirectToManager($userId, $scopedPath);
}

try {
  $expectedPrefix = "uploads/staff/$userId/";
  if (!str_starts_with($scopedPath, $expectedPrefix)) {
    setFlash('error', 'Invalid scoped path.');
    logRenameEvent('Invalid scoped path received', compact('scopedPath', 'expectedPrefix'), true);
    return redirectToManager($userId, '');
  }

  $relativePath     = sanitizePath(substr($scopedPath, strlen($expectedPrefix)));
  $oldName          = trim($rawOldName, '/');
  $newExtension     = pathinfo($rawNewName, PATHINFO_EXTENSION);
  $newBaseName      = trim(preg_replace('/[^a-zA-Z0-9_\- ]/', '', pathinfo($rawNewName, PATHINFO_FILENAME)));
  $newName          = $newExtension ? "$newBaseName.$newExtension" : $newBaseName;

  $oldExtension = pathinfo($oldName, PATHINFO_EXTENSION);
  if (!$newExtension && $oldExtension) {
    $newName .= ".$oldExtension";
  }

  $relativeOldPath  = $relativePath;
  $relativeNewPath  = dirname($relativeOldPath) === '.' ? $newName : dirname($relativeOldPath) . '/' . $newName;

  $baseDir = __DIR__ . '/../uploads/staff/' . $userId;
  $targetOldPath = $baseDir . '/' . $relativeOldPath;
  $targetNewPath = $baseDir . '/' . $relativeNewPath;

  logRenameEvent('Attempting rename', compact(
    'userId', 'type', 'scopedPath', 'oldName', 'newName',
    'relativeOldPath', 'relativeNewPath', 'targetOldPath', 'targetNewPath'
  ));

  if (!isSafePath($targetOldPath) || !isSafePath($targetNewPath)) {
    setFlash('error', 'Unsafe path detected.');
    logRenameEvent('Unsafe path detected', compact('targetOldPath', 'targetNewPath'), true);
    return redirectToManager($userId, $scopedPath);
  }

  if (!file_exists($targetOldPath)) {
    setFlash('error', "Item '$oldName' could not be found.");
    logRenameEvent('Rename failed — source not found', compact('targetOldPath'), true);
    return redirectToManager($userId, $scopedPath);
  }

  $success = is_dir($targetOldPath)
    ? rename($targetOldPath, $targetNewPath)
    : rename($targetOldPath, $targetNewPath);

  // ✅ Fallback for case-insensitive or locked folders
  if (!$success && is_dir($targetOldPath)) {
    logRenameEvent('Fallback to moveFolderRecursively', compact('targetOldPath', 'targetNewPath'));
    $success = moveFolderRecursively($targetOldPath, $targetNewPath);
  }

  if (!$success && strtolower($oldName) === strtolower($newName) && $oldName !== $newName) {
    $tempName = $newName . '__temp__' . uniqid();
    $tempPath = $baseDir . '/' . sanitizePath(dirname($relativeOldPath) . '/' . $tempName);

    if (rename($targetOldPath, $tempPath) && rename($tempPath, $targetNewPath)) {
      $success = true;
    }
  }

  if (!$success) {
    setFlash('error', "Failed to rename $type '$oldName'.");
    logRenameEvent('Rename failed — rename() returned false', compact('targetOldPath', 'targetNewPath'), true);
    return redirectToManager($userId, $scopedPath);
  }

  $scopedOldPath = $expectedPrefix . $relativeOldPath;
  $scopedNewPath = $expectedPrefix . $relativeNewPath;

  if ($type === 'file') {
    $stmt = $pdo->prepare("UPDATE files SET name = ?, path = ?, updated_at = NOW() WHERE path = ? AND owner_id = ?");
    $stmt->execute([$newName, $scopedNewPath, $scopedOldPath, $userId]);

  } elseif ($type === 'folder') {
    $stmt = $pdo->prepare("UPDATE folders SET name = ?, path = ?, updated_at = NOW() WHERE path = ? AND owner_id = ?");
    $stmt->execute([$newName, $scopedNewPath, $scopedOldPath, $userId]);

    $stmt = $pdo->prepare("UPDATE folders SET path = REPLACE(path, ?, ?), updated_at = NOW() WHERE path LIKE ?");
    $stmt->execute([$scopedOldPath, $scopedNewPath, "$scopedOldPath/%"]);

    $stmt = $pdo->prepare("UPDATE files SET path = REPLACE(path, ?, ?), updated_at = NOW() WHERE path LIKE ?");
    $stmt->execute([$scopedOldPath . '/', $scopedNewPath . '/', "$scopedOldPath/%"]);
  }

  logRenameEvent('Rename successful', compact('userId', 'type', 'scopedOldPath', 'scopedNewPath'));
    setFlash('success', ucfirst($type) . " renamed to '$newName'.");

} catch (RuntimeException $e) {
  setFlash('error', 'An error occurred while renaming.');
  logRenameEvent('Rename failed — exception thrown', [
    'userId' => $userId,
    'type' => $type,
    'error' => $e->getMessage(),
    'scopedPath' => $scopedPath
  ], true);
}

$relativeParentPath = $type === 'folder'
  ? $relativeNewPath // stay inside the renamed folder
  : dirname($relativeNewPath); // go to parent folder

$relativeParentPath = dirname($relativeNewPath);
$redirectPath = $relativeParentPath === '.' ? '' : $relativeParentPath;
$scopedRedirectPath = $redirectPath === '' ? '' : "uploads/staff/$userId/$redirectPath";

redirectToManager($userId, $scopedRedirectPath);