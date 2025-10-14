<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

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
  SELECT name, path, mime_type, is_deleted, owner_id
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

// 🔐 Access control: owner or granted 'read' or 'download' permission
$hasAccess = $file['owner_id'] == $currentUserId;

if (!$hasAccess) {
  $accessStmt = $pdo->prepare("
    SELECT 1 FROM access_control
    WHERE file_id = ? AND user_id = ? AND is_revoked = 0
      AND permission IN ('read', 'download')
    LIMIT 1
  ");
  $accessStmt->execute([$fileId, $currentUserId]);
  $hasAccess = $accessStmt->fetchColumn();
}

if (!$hasAccess) {
  http_response_code(403);
  echo "Forbidden.";
  exit;
}

// 🧩 Resolve full path
$storedPath = $file['path'];
$relativePath = ltrim(str_replace('/srv/burol-storage/', '', $storedPath), '/');
$fullPath = realpath(__DIR__ . '/../../srv/burol-storage/' . $relativePath);
$storageRoot = realpath(__DIR__ . '/../../srv/burol-storage');

if (!$fullPath || strpos($fullPath, $storageRoot) !== 0 || !file_exists($fullPath)) {
  http_response_code(404);
  echo "File not found on disk.";
  exit;
}

// 🧠 MIME and filename
$mimeType = $file['mime_type'] ?: mime_content_type($fullPath);
$originalName = $file['name'] ?? basename($fullPath);
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

error_log("Download accessed: file_id=$fileId, user_id=$currentUserId, filename=$safeName");

// 📦 Serve file
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: attachment; filename="' . $safeName . '"');
readfile($fullPath);
exit;