<?php
session_start();

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';
require_once __DIR__ . '/../helpers/permissions.php'; // 🔐 Modular access logic

// 🧠 Extract session and POST data with type safety
$userId         = (int)($_SESSION['user_id'] ?? 0);
$activeRoleId   = (int)($_SESSION['active_role_id'] ?? 0);
$originalRoleId = (int)($_SESSION['original_role_id'] ?? 0);
$targetId       = (int)($_POST['user_id'] ?? $userId);
$folderName     = trim($_POST['folder_name'] ?? '');
$currentPath    = sanitizePath($_POST['path'] ?? '');

// 🔐 Validate session
if (!$userId || !$activeRoleId || !$originalRoleId) {
  setFlash('error', 'Unauthorized access.');
  return redirectToManager($userId, '');
}

// 🔐 Access control
if (!canCreateFolder($userId, $targetId, $activeRoleId, $originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to create folders here.');
  return redirectToManager($userId, $currentPath); // ✅ Redirect to self
}

// ✅ Validate folder name
if ($folderName === '') {
  setFlash('error', 'Folder name is required.');
  return redirectToManager($userId, $currentPath);
}

if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $folderName)) {
  setFlash('error', 'Invalid folder name. Use only letters, numbers, dashes, and spaces.');
  return redirectToManager($userId, $currentPath);
}

// 📁 Build full relative path
$sanitizedFolderName = sanitizeSegment($folderName);
$fullRelativePath = $currentPath !== '' ? "$currentPath/$sanitizedFolderName" : $sanitizedFolderName;

// 📍 Resolve base path
$basePath = getUploadBaseByRoleUser($activeRoleId, $targetId);

// 🔄 Create folder
if (createFolder($basePath, $fullRelativePath)) {
  setFlash('success', "Folder '$sanitizedFolderName' created successfully.");
} else {
  setFlash('warning', "Folder '$sanitizedFolderName' already exists or could not be created.");
}

// ✅ Redirect to self to preserve UI state
redirectToManager($userId, $currentPath);

// 🔁 Redirect helper
function redirectToManager(int $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}