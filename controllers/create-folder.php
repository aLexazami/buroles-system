<?php
session_start();
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php'; // ✅ for getUserUploadBase()

$userId = $_SESSION['user_id'] ?? '';
$folderName = trim($_POST['folder_name'] ?? '');
$currentPath = trim($_POST['path'] ?? '', '/');

// ✅ Auth check
if (!$userId) {
  setFlash('error', 'Unauthorized access.');
  header("Location: /pages/staff/file-manager.php");
  exit;
}

// ✅ Validate folder name
if ($folderName === '') {
  setFlash('error', 'Folder name is required.');
  header("Location: /pages/staff/file-manager.php?path=" . urlencode($currentPath));
  exit;
}

// ✅ Validate folder name format
if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $folderName)) {
  setFlash('error', 'Invalid folder name. Use only letters, numbers, dashes, and spaces.');
  header("Location: /pages/staff/file-manager.php?path=" . urlencode($currentPath));
  exit;
}


// ✅ Build full relative path
$fullRelativePath = $currentPath !== '' ? $currentPath . '/' . $folderName : $folderName;
$basePath = getUserUploadBase($userId); // ✅ replaces manual __DIR__ logic

if (createFolder($basePath, $fullRelativePath)) {
  setFlash('success', "Folder '$folderName' created successfully.");
} else {
  setFlash('warning', "Folder '$folderName' already exists or could not be created.");
}

// ✅ Redirect back to current folder
header("Location: /pages/staff/file-manager.php?path=" . urlencode($currentPath));
exit;
?>