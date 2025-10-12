<?php
ini_set('display_errors', 0);
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/uuid.php';         // generateUuid(), isValidUuid()
require_once __DIR__ . '/../../helpers/path.php';         // resolveFolderPath(), buildVirtualPath(), resolveDiskPath(), ensureDirectoryExists()
require_once __DIR__ . '/../../helpers/folder-utils.php'; // isValidFolderName()

header('Content-Type: application/json');

// ✅ Validate session
$userId = $_SESSION['user_id'] ?? null;
if (!is_numeric($userId)) {
  echo json_encode(['success' => false, 'error' => 'Invalid session']);
  exit;
}

// ✅ Validate folder name
$folderName = trim($_POST['folder_name'] ?? '');
if ($folderName === '') {
  echo json_encode(['success' => false, 'error' => 'Folder name is required']);
  exit;
}
if (!isValidFolderName($folderName)) {
  echo json_encode(['success' => false, 'error' => 'Folder name contains invalid characters']);
  exit;
}

// ✅ Validate parent ID
$parentId = $_POST['parent_id'] ?? null;
if (!isValidUuid($parentId)) {
  $parentId = null;
}

// ✅ Resolve parent path and build full virtual path
$parentPath = resolveFolderPath($pdo, $parentId, $userId);
$uuid = generateUuid();
$virtualPath = buildVirtualPath($parentPath, $userId, $uuid);
$diskPath = resolveDiskPath($virtualPath);
ensureDirectoryExists($diskPath);

// ✅ Confirm user exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
$stmt->execute([$userId]);
if ($stmt->fetchColumn() == 0) {
  echo json_encode(['success' => false, 'error' => 'User not found']);
  exit;
}

// ✅ Insert folder into database
$stmt = $pdo->prepare("
  INSERT INTO files (id, name, type, path, parent_id, owner_id)
  VALUES (?, ?, 'folder', ?, ?, ?)
");
$stmt->execute([$uuid, $folderName, $virtualPath, $parentId, $userId]);

// ✅ Respond with folder info
echo json_encode([
  'success' => true,
  'item' => [
    'id' => $uuid,
    'name' => $folderName,
    'type' => 'folder',
    'permissions' => ['delete', 'share', 'comment'],
    'owner_id' => $userId,
    'parent_id' => $parentId,
    'path' => $virtualPath
  ]
]);
exit;
