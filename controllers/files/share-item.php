<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/user-utils.php'; // getUserByEmail(), getItemIdByPath()
require_once __DIR__ . '/../../helpers/path.php';       // sanitizePath()
require_once __DIR__ . '/../../helpers/folder-utils.php'; // getFolderContentsRecursive()

//  Extract POST data
$ownerId     = (int)($_POST['owner_id'] ?? 0);
$itemPath    = sanitizePath($_POST['item_path'] ?? '');
$type        = $_POST['type'] ?? ''; // 'file' or 'folder'
$recipient   = trim($_POST['recipient_email'] ?? '');
$accessLevel = $_POST['access_level'] ?? 'view';

//  Validate input
if (!$ownerId || !$itemPath || !$recipient || !$accessLevel || !in_array($type, ['file', 'folder'])) {
  setFlash('error', 'Missing or invalid sharing data.');
  return redirectToManager($ownerId);
}

//  Lookup recipient
$recipientUser = getUserByEmail($pdo, $recipient);
if (!$recipientUser || (int)$recipientUser['role_id'] !== 1) {
  setFlash('error', 'Recipient must be a valid staff user.');
  return redirectToManager($ownerId);
}

//  Lookup item ID
$isFolder = $type === 'folder';
$itemId = getItemIdByPath($pdo, $itemPath, $ownerId, $isFolder);
if (!$itemId) {
  setFlash('error', 'Item not found.');
  return redirectToManager($ownerId);
}

// Insert into sharing table
$table  = $isFolder ? 'shared_folders' : 'shared_files';
$column = $isFolder ? 'folder_id' : 'file_id';

$stmt = $pdo->prepare("INSERT INTO $table ($column, shared_by, shared_with, access_level, is_root) VALUES (?, ?, ?, ?, 1)");
$stmt->execute([$itemId, $ownerId, $recipientUser['id'], $accessLevel]);

// Cascade if folder
if ($type === 'folder') {
  $contents = getFolderContentsRecursive($pdo, $itemId);

  // Share subfolders
  $stmt = $pdo->prepare("INSERT INTO shared_folders (folder_id, shared_by, shared_with, access_level) VALUES (?, ?, ?, ?)");
  foreach ($contents['folders'] as $folderId) {
    $stmt->execute([$folderId, $ownerId, $recipientUser['id'], $accessLevel]);
  }

  // Share files
  $stmt = $pdo->prepare("INSERT INTO shared_files (file_id, shared_by, shared_with, access_level, is_root) VALUES (?, ?, ?, ?, 0)");
  foreach ($contents['files'] as $fileId) {
    $stmt->execute([$fileId, $ownerId, $recipientUser['id'], $accessLevel]);
  }
}

setFlash('success', ucfirst($type) . ' shared successfully.');
redirectToManager($ownerId);

// Redirect helper
function redirectToManager(int $userId): void
{
  header("Location: /pages/staff/file-manager.php?user_id=$userId");
  exit;
}
