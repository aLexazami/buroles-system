<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/user-utils.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/folder-utils.php';
require_once __DIR__ . '/../../helpers/logger.php';

function redirectToManager(int $userId): void {
  header("Location: /pages/staff/file-manager.php?user_id=$userId");
  exit;
}

$ownerId     = (int)($_POST['owner_id'] ?? 0);
$itemPath    = sanitizePath($_POST['item_path'] ?? '');
$type        = $_POST['type'] ?? '';
$recipient   = trim($_POST['recipient_email'] ?? '');
$accessLevel = $_POST['access_level'] ?? 'view';

if (!$ownerId || !$itemPath || !$recipient || !$accessLevel || !in_array($type, ['file', 'folder'])) {
  setFlash('error', 'Missing or invalid sharing data.');
  return redirectToManager($ownerId);
}

$recipientUser = getUserByEmail($pdo, $recipient);
if (!$recipientUser || (int)$recipientUser['role_id'] !== 1) {
  setFlash('error', 'Recipient must be a valid staff user.');
  return redirectToManager($ownerId);
}

$scopedPath = getScopedPath('1', (string)$ownerId, $itemPath);
logDebug("Sharing $type → $scopedPath → recipient={$recipientUser['id']} ({$recipientUser['email']}) → access=$accessLevel");
$isFolder = $type === 'folder';
$itemId = getItemIdByPath($pdo, $scopedPath, $ownerId, $isFolder);

if (!$itemId) {
  setFlash('error', 'Item not found.');
  return redirectToManager($ownerId);
}

$metaTable = $isFolder ? 'folders' : 'files';
$stmt = $pdo->prepare("SELECT name, path FROM $metaTable WHERE id = ?");
$stmt->execute([$itemId]);
$meta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$meta || !$meta['name'] || !$meta['path']) {
  setFlash('error', ucfirst($type) . ' metadata missing. Cannot share.');
  return redirectToManager($ownerId);
}

$table  = $isFolder ? 'shared_folders' : 'shared_files';
$column = $isFolder ? 'folder_id' : 'file_id';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = ? AND shared_with = ?");
$stmt->execute([$itemId, $recipientUser['id']]);
if ($stmt->fetchColumn() == 0) {
  $stmt = $pdo->prepare("INSERT INTO $table ($column, shared_by, shared_with, access_level, is_root) VALUES (?, ?, ?, ?, 1)");
  $stmt->execute([$itemId, $ownerId, $recipientUser['id'], $accessLevel]);
}

if ($isFolder) {
  $contents = getFolderContentsRecursive($pdo, $itemId);

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM shared_folders WHERE folder_id = ? AND shared_with = ?");
  $insert = $pdo->prepare("INSERT INTO shared_folders (folder_id, shared_by, shared_with, access_level) VALUES (?, ?, ?, ?)");

  foreach ($contents['folders'] as $folderId) {
    $check = $pdo->prepare("SELECT name, path FROM folders WHERE id = ?");
    $check->execute([$folderId]);
    $meta = $check->fetch(PDO::FETCH_ASSOC);
    if (!$meta || !$meta['name'] || !$meta['path']) continue;

    $stmt->execute([$folderId, $recipientUser['id']]);
    if ($stmt->fetchColumn() == 0) {
      $insert->execute([$folderId, $ownerId, $recipientUser['id'], $accessLevel]);
    }
  }

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM shared_files WHERE file_id = ? AND shared_with = ?");
  $insert = $pdo->prepare("INSERT INTO shared_files (file_id, shared_by, shared_with, access_level, is_root) VALUES (?, ?, ?, ?, 0)");

  foreach ($contents['files'] as $fileId) {
    $check = $pdo->prepare("SELECT name, path FROM files WHERE id = ?");
    $check->execute([$fileId]);
    $meta = $check->fetch(PDO::FETCH_ASSOC);
    if (!$meta || !$meta['name'] || !$meta['path']) continue;

    $stmt->execute([$fileId, $recipientUser['id']]);
    if ($stmt->fetchColumn() == 0) {
      $insert->execute([$fileId, $ownerId, $recipientUser['id'], $accessLevel]);
    }
  }
}

setFlash('success', ucfirst($type) . ' shared successfully.');
redirectToManager($ownerId);