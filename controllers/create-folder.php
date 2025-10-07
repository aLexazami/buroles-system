<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';
require_once __DIR__ . '/../helpers/permissions.php';
require_once __DIR__ . '/../helpers/logger.php';

$userId         = (int)($_SESSION['user_id'] ?? 0);
$activeRoleId   = (int)($_SESSION['active_role_id'] ?? 0);
$originalRoleId = (int)($_SESSION['original_role_id'] ?? 0);
$targetId       = (int)($_POST['user_id'] ?? $userId);
$folderName     = trim($_POST['folder_name'] ?? '');
$currentPath    = sanitizePath($_POST['path'] ?? '');

if (!$userId || !$activeRoleId || !$originalRoleId) {
  logFolderEvent('Missing session data', [
    'userId' => $userId,
    'activeRoleId' => $activeRoleId,
    'originalRoleId' => $originalRoleId
  ], true);
  setFlash('error', 'Unauthorized access.');
  return redirectToManager($userId, '');
}

if (!canCreateFolder($userId, $targetId, $activeRoleId, $originalRoleId)) {
  logFolderEvent('Access denied', [
    'userId' => $userId,
    'targetId' => $targetId,
    'activeRoleId' => $activeRoleId,
    'originalRoleId' => $originalRoleId
  ], true);
  setFlash('error', 'Access denied. You do not have permission to create folders here.');
  return redirectToManager($userId, $currentPath);
}

if ($folderName === '') {
  logFolderEvent('Missing folder name', [
    'userId' => $userId,
    'currentPath' => $currentPath
  ], true);
  setFlash('error', 'Folder name is required.');
  return redirectToManager($userId, $currentPath);
}

if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $folderName)) {
  logFolderEvent('Invalid folder name', [
    'userId' => $userId,
    'folderName' => $folderName,
    'currentPath' => $currentPath
  ], true);
  setFlash('error', 'Invalid folder name. Use only letters, numbers, dashes, and spaces.');
  return redirectToManager($userId, $currentPath);
}

// ✅ Build relative path with duplication guard
$sanitizedFolderName = sanitizeSegment($folderName);
$currentPath = sanitizePath($currentPath);

$lastSegment = basename($currentPath);
if ($lastSegment === $sanitizedFolderName) {
  $relativePath = $currentPath;
} else {
  $relativePath = sanitizePath($currentPath !== '' ? "$currentPath/$sanitizedFolderName" : $sanitizedFolderName);
}

$basePath   = getUploadBaseByRoleUser($activeRoleId, $targetId);
$scopedPath = getScopedPath('1', (string)$userId, $relativePath);

// ✅ Attempt folder creation
if (!createFolder($basePath, $relativePath)) {
  logFolderEvent('Failed to create folder on disk', [
    'userId' => $userId,
    'scopedPath' => $scopedPath,
    'basePath' => $basePath,
    'relativePath' => $relativePath
  ], true);
  setFlash('warning', "Folder '$sanitizedFolderName' already exists or could not be created.");
  return redirectToManager($userId, $currentPath);
}

// ✅ Resolve parent folder ID safely
$parentFolderId = null;
if ($currentPath !== '') {
  $parentScopedPath = getScopedPath('1', (string)$userId, $currentPath);
  $resolvedId = getFolderIdByPath($pdo, $parentScopedPath, $userId);
  $parentFolderId = is_numeric($resolvedId) ? (int)$resolvedId : null;
}

// ✅ Check for duplicates in DB with safe SQL
if ($parentFolderId !== null) {
  $stmt = $pdo->prepare("
    SELECT COUNT(*) FROM folders
    WHERE owner_id = ?
      AND parent_id = ?
      AND name = ?
  ");
  $stmt->execute([$userId, $parentFolderId, $sanitizedFolderName]);
} else {
  $stmt = $pdo->prepare("
    SELECT COUNT(*) FROM folders
    WHERE owner_id = ?
      AND parent_id IS NULL
      AND name = ?
  ");
  $stmt->execute([$userId, $sanitizedFolderName]);
}

$exists = $stmt->fetchColumn();

if ($exists) {
  logFolderEvent('Duplicate folder detected', [
    'userId' => $userId,
    'scopedPath' => $scopedPath,
    'parentFolderId' => $parentFolderId,
    'sanitizedFolderName' => $sanitizedFolderName
  ], true);
  setFlash('warning', "Folder '$sanitizedFolderName' already exists in this location.");
  return redirectToManager($userId, $currentPath);
}

// ✅ Insert folder record
$stmt = $pdo->prepare("
  INSERT INTO folders (name, parent_id, owner_id, path, type, created_at)
  VALUES (?, ?, ?, ?, 'folder', NOW())
");
$stmt->execute([$sanitizedFolderName, $parentFolderId, $userId, $scopedPath]);

logFolderEvent('Folder created successfully', [
  'userId' => $userId,
  'scopedPath' => $scopedPath,
  'basePath' => $basePath,
  'relativePath' => $relativePath
], false);

setFlash('success', "Folder '$sanitizedFolderName' created successfully.");
redirectToManager($userId, $currentPath);

// ✅ Redirect helper
function redirectToManager(int $userId, string $path): void
{
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}
