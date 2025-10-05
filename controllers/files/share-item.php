<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/user-utils.php'; // for getUserByEmail(), getItemIdByPath()
require_once __DIR__ . '/../../helpers/path.php';       // for sanitizePath()

// 🧠 Extract POST data
$ownerId      = (int)($_POST['owner_id'] ?? 0);
$itemPath     = sanitizePath($_POST['item_path'] ?? '');
$isFolder     = (int)($_POST['is_folder'] ?? 0);
$recipient    = trim($_POST['recipient_email'] ?? '');
$accessLevel  = $_POST['access_level'] ?? 'view';

// 🔐 Validate input
if (!$ownerId || !$itemPath || !$recipient || !$accessLevel) {
  setFlash('error', 'Missing sharing data.');
  return redirectToManager($ownerId);
}

// 🔍 Lookup recipient
$recipientUser = getUserByEmail($pdo, $recipient);
if (!$recipientUser || (int)$recipientUser['role_id'] !== 1) {
  setFlash('error', 'Recipient must be a valid staff user.');
  return redirectToManager($ownerId);
}

// 🔍 Lookup item ID
$itemId = getItemIdByPath($pdo, $itemPath, $ownerId, $isFolder === 1);
if (!$itemId) {
  setFlash('error', 'Item not found.');
  return redirectToManager($ownerId);
}


// 📝 Insert into sharing table
$table  = $isFolder ? 'shared_folders' : 'shared_files';
$column = $isFolder ? 'folder_id' : 'file_id';

$stmt = $pdo->prepare("INSERT INTO $table ($column, shared_by, shared_with, access_level) VALUES (?, ?, ?, ?)");
$stmt->execute([$itemId, $ownerId, $recipientUser['id'], $accessLevel]);

setFlash('success', 'Item shared successfully.');
redirectToManager($ownerId);

// 🔁 Redirect helper
function redirectToManager(int $userId): void {
  header("Location: /pages/staff/file-manager.php?user_id=$userId");
  exit;
}
?>