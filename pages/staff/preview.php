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

// 🧭 Optional context
$view = $_GET['view'] ?? 'my-files';
$folderId = $_GET['folder'] ?? null;

// 📄 Fetch file metadata
$stmt = $pdo->prepare("
  SELECT path, mime_type, is_deleted
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

// 🧠 Resolve disk path
$storedPath = $file['path']; // e.g. /srv/burol-storage/2/filename.jpg
$relativePath = ltrim(str_replace('/srv/burol-storage/', '', $storedPath), '/');
$fullPath = __DIR__ . '/../../srv/burol-storage/' . $relativePath;

$mimeType = $file['mime_type'] ?? 'application/octet-stream';

if (!file_exists($fullPath)) {
  http_response_code(404);
  echo "File not found on disk.";
  exit;
}

// 🧾 Optional: Log access context for audit/debug
error_log("Preview accessed: file_id=$fileId, user_id=$currentUserId, view=$view, folder=$folderId");

// 📤 Serve file
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
readfile($fullPath);
exit;