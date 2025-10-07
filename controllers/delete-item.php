<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';
require_once __DIR__ . '/../helpers/logger.php';

$userId         = (int)($_SESSION['user_id'] ?? 0);
$activeRoleId   = (int)($_SESSION['active_role_id'] ?? 0);
$originalRoleId = (int)($_SESSION['original_role_id'] ?? 0);
$targetId       = (int)($_POST['user_id'] ?? $userId);
$type           = trim($_POST['type'] ?? '');
$itemName       = sanitizeSegment(trim($_POST['name'] ?? ''));
$currentPath    = sanitizePath($_POST['path'] ?? '');

// ✅ Validate input
if (!$userId || !$activeRoleId || !$originalRoleId || $itemName === '' || $type === '') {
  logFolderEvent('Invalid deletion request', [
    'userId' => $userId,
    'type' => $type,
    'itemName' => $itemName,
    'currentPath' => $currentPath
  ], true);
  setFlash('error', 'Invalid deletion request.');
  return redirectAfterDeletion($userId, $currentPath);
}

if (!canDeleteItem($userId, $targetId, $activeRoleId, $originalRoleId)) {
  logFolderEvent('Access denied for deletion', [
    'userId' => $userId,
    'targetId' => $targetId,
    'activeRoleId' => $activeRoleId,
    'originalRoleId' => $originalRoleId
  ], true);
  setFlash('error', 'Access denied. You do not have permission to delete items here.');
  return redirectAfterDeletion($userId, $currentPath);
}

// ✅ Build relative path with duplication guard
$currentPath = sanitizePath($currentPath);
$lastSegment = basename($currentPath);
$relativePath = ($lastSegment === $itemName)
  ? $currentPath
  : sanitizePath($currentPath !== '' ? "$currentPath/$itemName" : $itemName);

$basePath   = getUploadBaseByRoleUser($activeRoleId, $targetId);
$targetPath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
$scopedPath = getScopedPath('1', (string)$userId, $relativePath);

// ✅ Validate existence
if (!file_exists($targetPath)) {
  logFolderEvent('Target path not found', [
    'userId' => $userId,
    'scopedPath' => $scopedPath,
    'targetPath' => $targetPath,
    'itemName' => $itemName
  ], true);
  setFlash('error', "Item '$itemName' could not be found.");
  return redirectAfterDeletion($userId, $currentPath);
}

logFolderEvent('Attempting deletion', [
  'userId' => $userId,
  'type' => $type,
  'scopedPath' => $scopedPath,
  'targetPath' => $targetPath
], false);

// ✅ Perform deletion
try {
  $success = match ($type) {
    'file'   => handleFileDeletion($pdo, $targetPath, $scopedPath, $userId),
    'folder' => handleFolderDeletion($pdo, $targetPath, $scopedPath, $userId),
    default  => handleUnknownType($type)
  };
} catch (RuntimeException $e) {
  logFolderEvent('Runtime exception during deletion', [
    'userId' => $userId,
    'type' => $type,
    'error' => $e->getMessage()
  ], true);
  setFlash('error', 'An error occurred while deleting the item.');
}

// ✅ Redirect after deletion
redirectAfterDeletion($userId, $currentPath);

// ✅ Helpers
function canDeleteItem(int $userId, int $targetId, int $activeRoleId, int $originalRoleId): bool {
  return in_array($originalRoleId, [2, 99]) || ($activeRoleId === 1 && $userId === $targetId);
}

function redirectAfterDeletion(int $userId, string $deletedPath): void {
  $parentPath = dirname($deletedPath);
  $cleanParentPath = ($parentPath === '.' || $parentPath === '') ? '' : sanitizePath($parentPath);

  logFolderEvent('Redirecting after deletion', [
    'userId' => $userId,
    'deletedPath' => $deletedPath,
    'redirectTo' => $cleanParentPath
  ], false);

  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($cleanParentPath !== '') {
    $url .= '&path=' . urlencode($cleanParentPath);
  }

  header("Location: $url");
  exit;
}

function handleFileDeletion(PDO $pdo, string $targetPath, string $scopedPath, int $ownerId): bool {
  if (is_file($targetPath)) {
    unlink($targetPath);
    $stmt = $pdo->prepare("DELETE FROM files WHERE path = ? AND owner_id = ?");
    $stmt->execute([$scopedPath, $ownerId]);

    logFolderEvent('File deleted successfully', [
      'userId' => $ownerId,
      'scopedPath' => $scopedPath,
      'targetPath' => $targetPath
    ], false);

    setFlash('success', "File deleted successfully.");
    return true;
  }

  logFolderEvent('File not found during deletion', [
    'userId' => $ownerId,
    'scopedPath' => $scopedPath,
    'targetPath' => $targetPath
  ], true);

  setFlash('error', "File could not be found.");
  return false;
}

function handleFolderDeletion(PDO $pdo, string $targetPath, string $scopedPath, int $ownerId): bool {
  if (!is_dir($targetPath)) {
    logFolderEvent('Folder not found during deletion', [
      'userId' => $ownerId,
      'scopedPath' => $scopedPath,
      'targetPath' => $targetPath
    ], true);

    setFlash('error', "Folder could not be found.");
    return false;
  }

  if (!deleteFolderRecursive($targetPath)) {
    logFolderEvent('Failed to delete folder from disk', [
      'userId' => $ownerId,
      'scopedPath' => $scopedPath,
      'targetPath' => $targetPath
    ], true);

    setFlash('error', "Failed to delete folder.");
    return false;
  }

  $stmt = $pdo->prepare("DELETE FROM folders WHERE (path = ? OR path LIKE ?) AND owner_id = ?");
  $stmt->execute([$scopedPath, "$scopedPath/%", $ownerId]);

  $stmt = $pdo->prepare("DELETE FROM files WHERE path LIKE ? AND owner_id = ?");
  $stmt->execute(["$scopedPath/%", $ownerId]);

  logFolderEvent('Folder and contents deleted successfully', [
    'userId' => $ownerId,
    'scopedPath' => $scopedPath,
    'targetPath' => $targetPath
  ], false);

  setFlash('success', "Folder and contents deleted successfully.");
  return true;
}

function handleUnknownType(string $type): bool {
  logFolderEvent('Unknown item type during deletion', ['type' => $type], true);
  setFlash('error', 'Unknown item type.');
  return false;
}