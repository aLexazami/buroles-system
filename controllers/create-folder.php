<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/folder-utils.php';
require_once __DIR__ . '/../helpers/path.php';
require_once __DIR__ . '/../helpers/permissions.php';

$userId         = (int)($_SESSION['user_id'] ?? 0);
$activeRoleId   = (int)($_SESSION['active_role_id'] ?? 0);
$originalRoleId = (int)($_SESSION['original_role_id'] ?? 0);
$targetId       = (int)($_POST['user_id'] ?? $userId);
$folderName     = trim($_POST['folder_name'] ?? '');
$currentPath    = sanitizePath($_POST['path'] ?? '');

if (!$userId || !$activeRoleId || !$originalRoleId) {
  setFlash('error', 'Unauthorized access.');
  return redirectToManager($userId, '');
}

if (!canCreateFolder($userId, $targetId, $activeRoleId, $originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to create folders here.');
  return redirectToManager($userId, $currentPath);
}

if ($folderName === '') {
  setFlash('error', 'Folder name is required.');
  return redirectToManager($userId, $currentPath);
}

if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $folderName)) {
  setFlash('error', 'Invalid folder name. Use only letters, numbers, dashes, and spaces.');
  return redirectToManager($userId, $currentPath);
}

$sanitizedFolderName = sanitizeSegment($folderName);
$relativePath = sanitizePath($currentPath !== '' ? "$currentPath/$sanitizedFolderName" : $sanitizedFolderName);
$basePath = getUploadBaseByRoleUser($activeRoleId, $targetId);

if (!createFolder($basePath, $relativePath)) {
  setFlash('warning', "Folder '$sanitizedFolderName' already exists or could not be created.");
  return redirectToManager($userId, $currentPath);
}

function getFolderIdByPath(PDO $pdo, int $ownerId, string $path): ?int {
  $stmt = $pdo->prepare("SELECT id FROM folders WHERE owner_id = ? AND path = ?");
  $stmt->execute([$ownerId, $path]);
  return $stmt->fetchColumn() ?: null;
}

$scopedPath = "uploads/staff/$userId/" . ltrim($relativePath, '/');
$parentFolderId = $currentPath !== '' ? getFolderIdByPath($pdo, $userId, "uploads/staff/$userId/" . ltrim($currentPath, '/')) : null;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM folders WHERE owner_id = ? AND parent_id " . ($parentFolderId ? "= ?" : "IS NULL") . " AND name = ?");
$stmt->execute($parentFolderId ? [$userId, $parentFolderId, $sanitizedFolderName] : [$userId, $sanitizedFolderName]);
$exists = $stmt->fetchColumn();

if ($exists) {
  setFlash('warning', "Folder '$sanitizedFolderName' already exists in this location.");
  return redirectToManager($userId, $currentPath);
}

$stmt = $pdo->prepare("INSERT INTO folders (name, parent_id, owner_id, path, type, created_at) VALUES (?, ?, ?, ?, 'folder', NOW())");
$stmt->execute([$sanitizedFolderName, $parentFolderId, $userId, $scopedPath]);

setFlash('success', "Folder '$sanitizedFolderName' created successfully.");
redirectToManager($userId, $currentPath);

function redirectToManager(int $userId, string $path): void {
  $url = "/pages/staff/file-manager.php?user_id=$userId";
  if ($path !== '') $url .= '&path=' . urlencode($path);
  header("Location: $url");
  exit;
}