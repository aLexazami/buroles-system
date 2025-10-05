<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/user-utils.php';     // getUserByEmail(), getItemIdByPath()
require_once __DIR__ . '/../../helpers/path.php';           // sanitizePath()
require_once __DIR__ . '/../../helpers/folder-utils.php';   // getFolderContentsRecursive()

// ✅ Logging helper
function logShareAction(string $type, int $ownerId, int $recipientId, string $path): void {
  $logFile = __DIR__ . '/../../logs/share_actions.log';
  $timestamp = date('Y-m-d H:i:s');
  $message = "[$timestamp] Shared $type → $path FROM: $ownerId TO: $recipientId\n";
  error_log($message, 3, $logFile);
}

// 🔁 Redirect helper
function redirectToManager(int $userId): void {
  header("Location: /pages/staff/file-manager.php?user_id=$userId");
  exit;
}

// 🧠 Extract POST data
$ownerId     = (int)($_POST['owner_id'] ?? 0);
$itemPath    = sanitizePath($_POST['item_path'] ?? '');
$type        = $_POST['type'] ?? ''; // 'file' or 'folder'
$recipient   = trim($_POST['recipient_email'] ?? '');
$accessLevel = $_POST['access_level'] ?? 'view';

// 🔐 Validate input
if (!$ownerId || !$itemPath || !$recipient || !$accessLevel || !in_array($type, ['file', 'folder'])) {
  setFlash('error', 'Missing or invalid sharing data.');
  return redirectToManager($ownerId);
}

// 🔍 Lookup recipient
$recipientUser = getUserByEmail($pdo, $recipient);
if (!$recipientUser || (int)$recipientUser['role_id'] !== 1) {
  setFlash('error', 'Recipient must be a valid staff user.');
  return redirectToManager($ownerId);
}

// 📁 Scope item path
$scopedPath = "uploads/staff/$ownerId/" . ltrim($itemPath, '/');

// 🔍 Lookup item ID
$isFolder = $type === 'folder';
$itemId = getItemIdByPath($pdo, $scopedPath, $ownerId, $isFolder);
if (!$itemId) {
  setFlash('error', 'Item not found.');
  return redirectToManager($ownerId);
}

// 🗂️ Insert into sharing table (if not already shared)
$table  = $isFolder ? 'shared_folders' : 'shared_files';
$column = $isFolder ? 'folder_id' : 'file_id';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = ? AND shared_with = ?");
$stmt->execute([$itemId, $recipientUser['id']]);
if ($stmt->fetchColumn() == 0) {
  $stmt = $pdo->prepare("INSERT INTO $table ($column, shared_by, shared_with, access_level, is_root) VALUES (?, ?, ?, ?, 1)");
  $stmt->execute([$itemId, $ownerId, $recipientUser['id'], $accessLevel]);
}

// 🔁 Cascade if folder
if ($isFolder) {
  $contents = getFolderContentsRecursive($pdo, $itemId);

  // Share subfolders
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM shared_folders WHERE folder_id = ? AND shared_with = ?");
  $insert = $pdo->prepare("INSERT INTO shared_folders (folder_id, shared_by, shared_with, access_level) VALUES (?, ?, ?, ?)");
  foreach ($contents['folders'] as $folderId) {
    $stmt->execute([$folderId, $recipientUser['id']]);
    if ($stmt->fetchColumn() == 0) {
      $insert->execute([$folderId, $ownerId, $recipientUser['id'], $accessLevel]);
    }
  }

  // Share files
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM shared_files WHERE file_id = ? AND shared_with = ?");
  $insert = $pdo->prepare("INSERT INTO shared_files (file_id, shared_by, shared_with, access_level, is_root) VALUES (?, ?, ?, ?, 0)");
  foreach ($contents['files'] as $fileId) {
    $stmt->execute([$fileId, $recipientUser['id']]);
    if ($stmt->fetchColumn() == 0) {
      $insert->execute([$fileId, $ownerId, $recipientUser['id'], $accessLevel]);
    }
  }
}

logShareAction($type, $ownerId, $recipientUser['id'], $scopedPath);
setFlash('success', ucfirst($type) . ' shared successfully.');
redirectToManager($ownerId);