<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';

// 🔐 Access control
function canDeleteItem(string $userId, string $targetId, int $activeRoleId, int $originalRoleId): bool {
  if (in_array($originalRoleId, [2, 99])) return true;
  return $activeRoleId === 1 && $userId === $targetId;
}

// 🔁 Redirect helper
function redirectToManager(string $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}

// 🧠 Extract session and POST data
$userId         = $_SESSION['user_id'] ?? '';
$activeRoleId   = $_SESSION['active_role_id'] ?? '';
$originalRoleId = $_SESSION['original_role_id'] ?? '';
$targetId       = $_POST['user_id'] ?? $userId;
$type           = $_POST['type'] ?? '';
$name           = $_POST['name'] ?? '';
$currentPath    = sanitizePath($_POST['path'] ?? '');

// 🔐 Validate session and input
if (!$userId || !$activeRoleId || !$originalRoleId || !$type || !$name) {
  setFlash('error', 'Invalid deletion request.');
  return redirectToManager($userId, $currentPath);
}

if (!canDeleteItem($userId, $targetId, (int)$activeRoleId, (int)$originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to delete items here.');
  return redirectToManager($userId, $currentPath);
}

// 🧠 Resolve full path
$baseDir    = getUploadBaseByRoleUser((int)$activeRoleId, $targetId);
$targetPath = resolveUploadPathFromBase($baseDir, $currentPath, $name);

if (!$targetPath || !file_exists($targetPath)) {
  setFlash('error', "Item '$name' could not be found.");
  return redirectToManager($targetId, $currentPath);
}

// 🧠 Build scoped DB path
$relativePath = sanitizePath($currentPath !== '' ? "$currentPath/$name" : $name);
$scopedPath   = "uploads/staff/$userId/" . ltrim($relativePath, '/');

try {
  $success = match ($type) {
    'file'   => handleFileDeletion($pdo, $targetPath, $scopedPath, $userId),
    'folder' => handleFolderDeletion($pdo, $targetPath, $scopedPath, $userId),
    default  => handleUnknownType($type)
  };
} catch (RuntimeException) {
  setFlash('error', 'An error occurred while deleting the item.');
}

redirectToManager($targetId, $currentPath);

// ✅ Helpers
function handleFileDeletion(PDO $pdo, string $targetPath, string $scopedPath, string $ownerId): bool {
  if (is_file($targetPath)) {
    unlink($targetPath);

    $stmt = $pdo->prepare("DELETE FROM files WHERE path = ? AND owner_id = ?");
    $stmt->execute([$scopedPath, $ownerId]);

    setFlash('success', "File deleted successfully.");
    return true;
  }

  setFlash('error', "File could not be found.");
  return false;
}

function handleFolderDeletion(PDO $pdo, string $targetPath, string $scopedPath, string $ownerId): bool {
  if (!is_dir($targetPath)) {
    setFlash('error', "Folder could not be found.");
    return false;
  }

  if (!deleteFolderRecursive($targetPath)) {
    setFlash('error', "Failed to delete folder.");
    return false;
  }

  $stmt = $pdo->prepare("DELETE FROM folders WHERE (path = ? OR path LIKE ?) AND owner_id = ?");
  $stmt->execute([$scopedPath, "$scopedPath/%", $ownerId]);

  $stmt = $pdo->prepare("DELETE FROM files WHERE path LIKE ? AND owner_id = ?");
  $stmt->execute(["$scopedPath/%", $ownerId]);

  setFlash('success', "Folder and contents deleted successfully.");
  return true;
}

function handleUnknownType(string $type): bool {
  setFlash('error', 'Unknown item type.');
  return false;
}