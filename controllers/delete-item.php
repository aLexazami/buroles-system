<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';

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

// 🔐 Access control
function canDeleteItem(string $userId, string $targetId, int $activeRoleId, int $originalRoleId): bool {
  if (in_array($originalRoleId, [2, 99])) return true;
  return $activeRoleId === 1 && $userId === $targetId;
}

if (!canDeleteItem($userId, $targetId, (int)$activeRoleId, (int)$originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to delete items here.');
  return redirectToManager($userId, $currentPath);
}

// 🧠 Resolve full path
$baseDir    = getUploadBaseByRoleUser((int)$activeRoleId, $targetId);
$targetPath = resolveUploadPathFromBase($baseDir, $currentPath, $name);

if (!$targetPath || !file_exists($targetPath)) {
  error_log("Deletion failed: path not found → $targetPath");
  setFlash('error', "Item '$name' could not be found.");
  return redirectToManager($targetId, $currentPath);
}

// 🧠 Resolve folder path for DB lookup
$fullRelativePath = $currentPath !== '' ? "$currentPath/$name" : $name;

try {
  $success = match ($type) {
    'file'   => handleFileDeletion($pdo, $targetPath, $fullRelativePath, $userId),
    'folder' => handleFolderDeletion($pdo, $targetPath, $fullRelativePath, $userId),
    default  => handleUnknownType($type)
  };

  if (!$success) {
    return redirectToManager($targetId, $currentPath);
  }

} catch (RuntimeException $e) {
  error_log("Deletion error: " . $e->getMessage());
  setFlash('error', 'An error occurred while deleting the item.');
}

redirectToManager($targetId, $currentPath);

// ✅ Helpers
function handleFileDeletion(PDO $pdo, string $targetPath, string $relativePath, string $ownerId): bool {
  if (is_file($targetPath)) {
    unlink($targetPath);

    // Delete metadata
    $stmt = $pdo->prepare("DELETE FROM files WHERE path = ? AND owner_id = ?");
    $stmt->execute([$targetPath, $ownerId]);

    setFlash('success', "File deleted successfully.");
    return true;
  }
  setFlash('error', "File could not be found.");
  return false;
}

function handleFolderDeletion(PDO $pdo, string $targetPath, string $relativePath, string $ownerId): bool {
  if (is_dir($targetPath)) {
    if (deleteFolderRecursive($targetPath)) {
      // Delete metadata
      $stmt = $pdo->prepare("DELETE FROM folders WHERE path = ? AND owner_id = ?");
      $stmt->execute([$relativePath, $ownerId]);

      setFlash('success', "Folder deleted successfully.");
      return true;
    } else {
      error_log("Folder deletion failed: $targetPath");
      setFlash('error', "Failed to delete folder.");
      return false;
    }
  }
  setFlash('error', "Folder could not be found.");
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