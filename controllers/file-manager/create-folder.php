<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';      // setFlash()
require_once __DIR__ . '/../../helpers/uuid.php';       // generateUuid(), isValidUuid()

// ✅ Validate session
$userId = $_SESSION['user_id'] ?? null;
if (!is_numeric($userId)) {
  setFlash('error', 'Invalid session. Please log in again.');
  header('Location: /login.php');
  exit;
}

// ✅ Validate folder name
$folderName = trim($_POST['folder_name'] ?? '');
if ($folderName === '') {
  setFlash('error', 'Folder name is required.');
  header('Location: file-manager.php?view=my-files');
  exit;
}

// ✅ Normalize parentId
$parentId = $_POST['parent_id'] ?? null;
if (!isValidUuid($parentId)) {
  $parentId = null;
}

// ✅ Generate UUID and virtual path
$uuid = generateUuid();
$virtualPath = "/virtual/$userId/$uuid";

// ✅ Confirm user exists (foreign key safety)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
$stmt->execute([$userId]);
if ($stmt->fetchColumn() == 0) {
  setFlash('error', 'User not found. Cannot create folder.');
  header('Location: /pages/staff/file-manager.php?view=my-files');
  exit;
}

// ✅ Insert folder
$stmt = $pdo->prepare("
  INSERT INTO files (id, name, type, path, parent_id, owner_id)
  VALUES (?, ?, 'folder', ?, ?, ?)
");
$stmt->execute([$uuid, $folderName, $virtualPath, $parentId, $userId]);

setFlash('success', 'Folder created successfully.');
header('Location: /pages/staff/file-manager.php?view=my-files');