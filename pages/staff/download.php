<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/file-utils.php'; // resolveStoragePath(), hasAccessToFile()

if (!isset($_GET['id'])) {
  http_response_code(400);
  echo "Missing file ID.";
  exit;
}

$fileId = $_GET['id'];
$currentUserId = $_SESSION['user_id'] ?? null;

if (!$currentUserId) {
  http_response_code(401);
  echo "Unauthorized.";
  exit;
}

// 📄 Fetch file metadata
$stmt = $pdo->prepare("
  SELECT id, name, path, mime_type, is_deleted, owner_id, type
  FROM files
  WHERE id = ? LIMIT 1
");
$stmt->execute([$fileId]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file || $file['is_deleted']) {
  http_response_code(404);
  echo "File not found.";
  exit;
}

// 🚫 Reject folders
if ($file['type'] !== 'file') {
  http_response_code(400);
  echo "This endpoint only supports file downloads.";
  exit;
}

// 🔐 Access control
$hasAccess = $file['owner_id'] == $currentUserId || hasAccessToFile($pdo, $fileId, $currentUserId);

if (!$hasAccess) {
  error_log("Access denied: file_id=$fileId, user_id=$currentUserId");
  http_response_code(403);
  echo "Forbidden.";
  exit;
}

error_log("Access granted: file_id=$fileId, user_id=$currentUserId");

// 🧠 Safe filename
$originalName = $file['name'] ?? 'download';
$safeName = str_replace(["\r", "\n"], '', $originalName);

// 🧾 Log download
$logStmt = $pdo->prepare("
  INSERT INTO downloads (id, file_id, user_id, view_context, folder_id, ip_address, user_agent)
  VALUES (UUID(), ?, ?, ?, ?, ?, ?)
");
$logStmt->execute([
  $fileId,
  $currentUserId,
  $_GET['view'] ?? null,
  $_GET['folder'] ?? null,
  $_SERVER['REMOTE_ADDR'] ?? null,
  $_SERVER['HTTP_USER_AGENT'] ?? null
]);

// 📤 Serve file
$fullPath = resolveStoragePath($file['path']);
if (!$fullPath) {
  http_response_code(404);
  echo "File not found on disk.";
  exit;
}

$mimeType = $file['mime_type'] ?: mime_content_type($fullPath);

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: attachment; filename="' . $safeName . '"');
readfile($fullPath);
exit;