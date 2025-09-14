<?php
session_start();

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';

// 🧠 Extract session and POST data
$userId      = $_SESSION['user_id'] ?? '';
$activeRole  = $_SESSION['active_role_id'] ?? '';
$targetId    = $_POST['user_id'] ?? $userId;
$folderName  = trim($_POST['folder_name'] ?? '');
$currentPath = sanitizePath($_POST['path'] ?? '');

// 🔐 Validate session
if (!$userId || !$activeRole) {
  setFlash('error', 'Unauthorized access.');
  return redirectToManager($userId, '');
}

// 🔐 Staff can only manage their own folders
if ($activeRole === '1') {
  $targetId = $userId;
}

// ✅ Validate folder name
if ($folderName === '') {
  setFlash('error', 'Folder name is required.');
  return redirectToManager($targetId, $currentPath);
}

if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $folderName)) {
  setFlash('error', 'Invalid folder name. Use only letters, numbers, dashes, and spaces.');
  return redirectToManager($targetId, $currentPath);
}

// 📁 Build full relative path
$sanitizedFolderName = sanitizeSegment($folderName);
$fullRelativePath = $currentPath !== '' ? "$currentPath/$sanitizedFolderName" : $sanitizedFolderName;

// 📍 Resolve base path
$basePath = getUploadBaseByRoleUser($activeRole, $targetId);

// 🔄 Create folder
if (createFolder($basePath, $fullRelativePath)) {
  setFlash('success', "Folder '$sanitizedFolderName' created successfully.");
} else {
  setFlash('warning', "Folder '$sanitizedFolderName' already exists or could not be created.");
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