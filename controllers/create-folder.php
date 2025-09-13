<?php
session_start();

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';

$userId      = $_SESSION['user_id'] ?? '';
$activeRole  = $_SESSION['active_role_id'] ?? '';
$targetId    = $_POST['user_id'] ?? $userId;
$folderName  = trim($_POST['folder_name'] ?? '');
$currentPath = sanitizePath($_POST['path'] ?? '');

// 🔐 Auth check
if (!$userId || !$activeRole) {
  setFlash('error', 'Unauthorized access.');
  redirectToManager($userId, '');
}

// 🔐 Staff can only create folders for themselves
if ($activeRole == 1 && $targetId !== $userId) {
  setFlash('error', 'Access denied. You can only manage your own folders.');
  redirectToManager($userId, '');
}

// ✅ Validate folder name
if ($folderName === '') {
  setFlash('error', 'Folder name is required.');
  redirectToManager($targetId, $currentPath);
}

if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $folderName)) {
  setFlash('error', 'Invalid folder name. Use only letters, numbers, dashes, and spaces.');
  redirectToManager($targetId, $currentPath);
}

// ✅ Build full relative path
$sanitizedFolderName = sanitizeSegment($folderName);
$fullRelativePath = $currentPath !== '' ? $currentPath . '/' . $sanitizedFolderName : $sanitizedFolderName;

// ✅ Resolve base path using role-first logic
$basePath = getUploadBaseByRoleUser('1', $targetId);

// ✅ Create folder
if (createFolder($basePath, $fullRelativePath)) {
  setFlash('success', "Folder '$sanitizedFolderName' created successfully.");
} else {
  setFlash('warning', "Folder '$sanitizedFolderName' already exists or could not be created.");
}

// ✅ Redirect back to current folder
redirectToManager($targetId, $currentPath);

// ✅ Helper
function redirectToManager(string $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}
?>