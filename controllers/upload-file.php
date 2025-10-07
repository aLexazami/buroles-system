<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/path.php';

// 🔐 Access control
function canUploadFile(string $userId, string $targetId, int $activeRoleId, int $originalRoleId): bool {
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
$currentPath    = sanitizePath($_POST['path'] ?? '');
$file           = $_FILES['file'] ?? null;

// 🔐 Validate session
if (!$userId || !$activeRoleId || !$originalRoleId) {
  setFlash('error', 'Unauthorized access.');
  return redirectToManager($userId, $currentPath);
}

if (!canUploadFile($userId, $targetId, (int)$activeRoleId, (int)$originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to upload files here.');
  return redirectToManager($userId, $currentPath);
}

// ✅ Validate file input
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
  setFlash('error', 'File upload failed.');
  return redirectToManager($targetId, $currentPath);
}

// ✅ Validate MIME type
$allowedTypes = [
  'application/pdf',
  'text/csv',
  'application/vnd.ms-excel',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'image/jpeg',
  'image/png'
];

$mimeType  = mime_content_type($file['tmp_name']);
$filename  = sanitizeSegment(pathinfo($file['name'], PATHINFO_FILENAME));
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$finalName = $extension ? "$filename.$extension" : $filename;

if (!in_array($mimeType, $allowedTypes)) {
  setFlash('error', 'Unsupported file type.');
  return redirectToManager($targetId, $currentPath);
}

// 📁 Resolve upload path
$baseDir      = getUploadBaseByRoleUser((string)$activeRoleId, $targetId);
$targetPath   = resolveUploadPathFromBase($baseDir, $currentPath, $finalName);
$relativePath = sanitizePath($currentPath !== '' ? "$currentPath/$finalName" : $finalName);
$scopedPath   = getScopedPath('1', (string)$userId, $relativePath);

if (!is_dir(dirname($targetPath))) {
  setFlash('error', 'Target folder does not exist.');
  return redirectToManager($targetId, $currentPath);
}

if (file_exists($targetPath)) {
  setFlash('warning', "File '$finalName' already exists.");
  return redirectToManager($targetId, $currentPath);
}

// 🔄 Move file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
  setFlash('error', 'Failed to move uploaded file.');
  return redirectToManager($targetId, $currentPath);
}

// 🧠 Resolve folder ID from current path
function getFolderIdByPath(PDO $pdo, int $ownerId, string $path): ?int {
  $scopedFolderPath = getScopedPath('1', (string)$ownerId, $path);
  $stmt = $pdo->prepare("SELECT id FROM folders WHERE owner_id = ? AND path = ?");
  $stmt->execute([$ownerId, $scopedFolderPath]);
  return $stmt->fetchColumn() ?: null;
}

$folderId = getFolderIdByPath($pdo, (int)$userId, $currentPath);

// 🗂️ Insert file metadata into database
$stmt = $pdo->prepare("INSERT INTO files (name, folder_id, owner_id, path, size, mime_type, type, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, 'file', NOW())");
$stmt->execute([
  $finalName,
  $folderId,
  $userId,
  $scopedPath,
  $file['size'],
  $mimeType
]);

setFlash('success', "File '$finalName' uploaded successfully.");
redirectToManager($targetId, $currentPath);