<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/sharing-utils.php';

session_start();

$userId    = (int)($_SESSION['user_id'] ?? 0);
$targetId  = (int)($_POST['user_id'] ?? 0);
$type      = $_POST['type'] ?? 'file';
$commentId = (int)($_POST['comment_id'] ?? 0);
$path      = trim($_POST['path'] ?? '');

if (!$userId || !$targetId || !$commentId || !$path) {
  setFlash('error', 'Missing required fields.');
  header("Location: /pages/staff/file-manager.php?user_id=$targetId&path=" . urlencode($path));
  exit;
}

try {
  $table = $type === 'folder' ? 'folder_comments' : 'file_comments';

  // 🔍 Check ownership
  $stmt = $pdo->prepare("SELECT commenter_id FROM {$table} WHERE id = ? AND is_deleted = FALSE");
  $stmt->execute([$commentId]);
  $ownerId = (int)$stmt->fetchColumn();

  if (!$ownerId) {
    setFlash('error', 'Comment not found or already deleted.');
  } elseif ($ownerId !== $userId && !canEdit($pdo, $userId, $type, $commentId)) {
    setFlash('error', 'You do not have permission to delete this comment.');
  } else {
    // 📝 Soft delete with audit
    $stmt = $pdo->prepare("
      UPDATE {$table}
      SET is_deleted = TRUE, deleted_by = ?, deleted_at = NOW()
      WHERE id = ?
    ");
    $stmt->execute([$userId, $commentId]);
    setFlash('success', 'Comment deleted.');
  }

} catch (Exception $e) {
  setFlash('error', 'Something went wrong.');
}

header("Location: /pages/staff/file-manager.php?user_id=$targetId&path=" . urlencode($path));
exit;