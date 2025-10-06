<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/sharing-utils.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/flash.php';

session_start();

$userId      = (int)($_SESSION['user_id'] ?? 0);
$targetId    = (int)($_POST['user_id'] ?? 0);
$fileName    = sanitizeSegment($_POST['file_name'] ?? '');
$path        = sanitizePath($_POST['path'] ?? '');
$commentText = trim($_POST['comment'] ?? '');

if (!$userId || !$targetId || !$fileName || !$path || !$commentText) {
  setFlash('error', 'Missing required fields.');
  header("Location: /pages/staff/file-manager.php?user_id=$targetId&path=" . urlencode($path));
  exit;
}

// 🔍 Resolve file ID
$fullPath = resolveUploadPath('1', (string)$targetId, $path, $fileName);
$fileId   = getItemIdByPath($pdo, $fullPath, $targetId, true);

if (!$fileId || !canComment($pdo, $userId, 'file', $fileId)) {
  setFlash('error', 'You do not have permission to comment on this file.');
  header("Location: /pages/staff/file-manager.php?user_id=$targetId&path=" . urlencode($path));
  exit;
}

// 📝 Insert comment
$stmt = $pdo->prepare("
  INSERT INTO file_comments (file_id, commenter_id, comment_text, commented_at)
  VALUES (?, ?, ?, NOW())
");
$stmt->execute([$fileId, $userId, $commentText]);

setFlash('success', 'Comment posted.');
header("Location: /pages/staff/file-manager.php?user_id=$targetId&path=" . urlencode($path));
exit;