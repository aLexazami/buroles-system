<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['id'])) {
  http_response_code(400);
  echo "Missing file ID.";
  exit;
}

$fileId = $_GET['id']; // UUID-safe
$currentUserId = $_SESSION['user_id'] ?? null;

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

// 🔐 Access control: must be owner
if ($file['owner_id'] !== $currentUserId) {
  http_response_code(403);
  echo "Forbidden.";
  exit;
}

// 🧩 Resolve full path
$storedPath = $file['path'];
$relativePath = ltrim(str_replace('/srv/burol-storage/', '', $storedPath), '/');
$fullPath = realpath(__DIR__ . '/../../srv/burol-storage/' . $relativePath);
$storageRoot = realpath(__DIR__ . '/../../srv/burol-storage');

// 🔒 Validate path integrity
if (!$fullPath || strpos($fullPath, $storageRoot) !== 0 || !file_exists($fullPath)) {
  http_response_code(404);
  echo "File not found on disk.";
  exit;
}

// 🧠 MIME and filename
$mimeType = $file['mime_type'] ?: mime_content_type($fullPath);
$originalName = $file['name'] ?? basename($fullPath);
$safeName = str_replace(["\r", "\n"], '', $originalName);

// 🧾 Log download to database
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

// 🧾 Optional: Log to error_log for debugging
error_log("Download accessed: file_id=$fileId, user_id=$currentUserId, filename=$safeName");

// 📦 Serve file as download
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: attachment; filename="' . $safeName . '"');
readfile($fullPath);
exit;