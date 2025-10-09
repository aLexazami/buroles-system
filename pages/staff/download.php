<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['id'])) {
  http_response_code(400);
  echo "Missing file ID.";
  exit;
}

$fileId = intval($_GET['id']);
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

// 🔐 Access control: must be owner or have download rights
if ($file['owner_id'] !== $currentUserId) {
  http_response_code(403);
  echo "Forbidden.";
  exit;
}

// 🧩 Resolve full path
$storedPath = $file['path']; // e.g. /srv/burol-storage/2/filename.docx
$relativePath = ltrim(str_replace('/srv/burol-storage/', '', $storedPath), '/');
$fullPath = __DIR__ . '/../../srv/burol-storage/' . $relativePath;

if (!file_exists($fullPath)) {
  http_response_code(404);
  echo "File not found on disk.";
  exit;
}

// 🧠 MIME and filename
$mimeType = $file['mime_type'] ?: mime_content_type($fullPath);
$originalName = basename($file['name'] ?? $fullPath);

// 📦 Serve file as download
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: attachment; filename="' . $originalName . '"');
readfile($fullPath);
exit;