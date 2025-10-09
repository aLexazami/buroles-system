<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['id'])) {
  http_response_code(400);
  echo "Missing file ID.";
  exit;
}

$fileId = $_GET['id'];
$currentUserId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
  SELECT path, mime_type, is_deleted, owner_id
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

if ($file['owner_id'] !== $currentUserId) {
  http_response_code(403);
  echo "Forbidden.";
  exit;
}

// actual Windows path
$storedPath = $file['path']; // e.g. /srv/burol-storage/2/filename.jpg
$relativePath = ltrim(str_replace('/srv/burol-storage/', '', $storedPath), '/');
$fullPath = __DIR__ . '/../../srv/burol-storage/' . $relativePath;

$mimeType = $file['mime_type'] ?? 'application/octet-stream';

if (!file_exists($fullPath)) {
  http_response_code(404);
  echo "File not found on disk.";
  exit;
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
readfile($fullPath);
exit;