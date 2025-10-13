<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/uuid.php';         // generateUuid(), isValidUuid()
require_once __DIR__ . '/../../helpers/path.php';         // resolveFolderPath(), buildVirtualPath(), resolveDiskPath(), ensureDirectoryExists()
require_once __DIR__ . '/../../helpers/folder-utils.php'; // isValidFolderName()

header('Content-Type: application/json');

// 🧠 Validate session
$userId = $_SESSION['user_id'] ?? null;
if (!is_numeric($userId)) {
  exitWithError('Invalid session');
}

// 🧠 Validate folder name
$requestedName = trim($_POST['folder_name'] ?? '');
if ($requestedName === '') {
  exitWithError('Folder name is required');
}
if (!isValidFolderName($requestedName)) {
  exitWithError('Folder name contains invalid characters');
}

// 🧠 Validate parent ID
$parentId = $_POST['parent_id'] ?? null;
if (!isValidUuid($parentId)) {
  $parentId = null;
}

// 🧠 Confirm user exists
if (!userExists($pdo, $userId)) {
  exitWithError('User not found');
}

// 🧠 Resolve parent path and build full virtual path
$parentPath = resolveFolderPath($pdo, $parentId, $userId);
$uuid = generateUuid();
$virtualPath = buildVirtualPath($parentPath, $userId, $uuid);
$diskPath = resolveDiskPath($virtualPath);
ensureDirectoryExists($diskPath);

// 🧠 Resolve unique folder name
$folderName = getUniqueFolderName($pdo, $parentId, $requestedName);

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

// 🔧 Helpers
function exitWithError(string $message): void {
  echo json_encode(['success' => false, 'error' => $message]);
  exit;
}

function userExists(PDO $pdo, int $userId): bool {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
  $stmt->execute([$userId]);
  return $stmt->fetchColumn() > 0;
}

function getUniqueFolderName(PDO $pdo, ?string $parentId, string $baseName): string {
  $stmt = $pdo->prepare("
    SELECT name FROM files
    WHERE parent_id " . ($parentId ? "= ?" : "IS NULL") . "
    AND type = 'folder' AND is_deleted = 0
  ");
  $stmt->execute($parentId ? [$parentId] : []);
  $existingNames = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));

  if (!in_array(strtolower($baseName), $existingNames)) return $baseName;

  $counter = 1;
  do {
    $candidate = "{$baseName} ({$counter})";
    $counter++;
  } while (in_array(strtolower($candidate), $existingNames));

  return $candidate;
}