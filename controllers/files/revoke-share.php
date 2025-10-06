<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  setFlash('Invalid request method.', 'error');
  header('Location: /pages/staff/shared-file.php');
  exit;
}

$sharedId    = $_POST['item_id'] ?? null;         // shared_files.id or shared_folders.id
$type        = $_POST['type'] ?? null;            // 'file' or 'folder'
$sharedWith  = $_POST['shared_with'] ?? null;
$sharedBy    = $_SESSION['user_id'] ?? 0;

if (!$sharedId || !$type || !$sharedWith || !in_array($type, ['file', 'folder'], true)) {
  setFlash('Missing or invalid data for revocation.', 'error');
  header('Location: /pages/staff/shared-file.php');
  exit;
}

$table = $type === 'file' ? 'shared_files' : 'shared_folders';

$sql = "DELETE FROM $table WHERE id = ? AND shared_by = ? AND shared_with = ?";
$stmt = $pdo->prepare($sql);
$success = $stmt->execute([$sharedId, $sharedBy, $sharedWith]);

if ($success && $stmt->rowCount() > 0) {
  setFlash('success', 'Access successfully revoked.');
} else {
  setFlash('error', 'Failed to revoke access or no matching record found.');
}

header('Location: /pages/staff/shared-file.php?view=by');
exit;