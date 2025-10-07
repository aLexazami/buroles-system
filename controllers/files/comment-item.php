<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/sharing-utils.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/flash.php';

session_start();

$userId      = (int)($_SESSION['user_id'] ?? 0);
$targetId    = (int)($_POST['user_id'] ?? 0);
$type        = $_POST['type'] ?? '';
$path        = sanitizePath($_POST['path'] ?? '');
$commentText = trim($_POST['comment'] ?? '');
$parentId    = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : null;

$redirectUrl = "/pages/staff/file-manager.php?user_id=$targetId&path=" . urlencode($path);

// ✅ Basic validation
if (!$userId || !$targetId || !$path || !$commentText || !in_array($type, ['file', 'folder'], true)) {
  setFlash('error', 'Missing or invalid fields.');
  header("Location: $redirectUrl");
  exit;
}

try {
  if ($type === 'file') {
    $fileName = sanitizeSegment($_POST['file_name'] ?? '');
    if (!$fileName) {
      setFlash('error', 'Missing file name.');
      header("Location: $redirectUrl");
      exit;
    }

    $fullPath = resolveUploadPath('1', (string)$targetId, $path, $fileName);
    $fileId   = getItemIdByPath($pdo, $fullPath, $targetId, true);

    if (!$fileId || !canComment($pdo, $userId, 'file', $fileId)) {
      setFlash('error', 'You do not have permission to comment on this file.');
      header("Location: $redirectUrl");
      exit;
    }

    $stmt = $pdo->prepare("
      INSERT INTO file_comments (file_id, commenter_id, comment_text, parent_comment_id, commented_at)
      VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$fileId, $userId, $commentText, $parentId]);

    setFlash('success', 'Comment posted.');

  } elseif ($type === 'folder') {
    $folderId = (int)($_POST['folder_id'] ?? 0);
    if (!$folderId || !canComment($pdo, $userId, 'folder', $folderId)) {
      setFlash('error', 'You do not have permission to comment on this folder.');
      header("Location: $redirectUrl");
      exit;
    }

    $stmt = $pdo->prepare("
      INSERT INTO folder_comments (folder_id, commenter_id, comment_text, parent_comment_id, commented_at)
      VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$folderId, $userId, $commentText, $parentId]);

    setFlash('success', 'Comment posted.');
  }

} catch (Exception $e) {
  setFlash('error', 'Something went wrong.');
}

header("Location: $redirectUrl");
exit;