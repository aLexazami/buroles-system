<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/folder-utils.php'; // softDeleteFolderAndContents()
require_once __DIR__ . '/../../helpers/path.php'; // resolveDiskPath, ensureVirtualPathExists
require_once __DIR__ . '/../../helpers/access-utils.php'; // canPerformAction(), getEffectivePermissionsWithSource()

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$fileId = $input['id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$fileId) {
  echo json_encode(['success' => false, 'message' => 'Missing user or file ID']);
  exit;
}

try {
  // 🔐 Check permission using modular logic
  if (!canPerformAction($pdo, $fileId, $userId, 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
  }

  // 📦 Fetch file metadata
  $stmt = $pdo->prepare("SELECT name, type, path FROM files WHERE id = ?");
  $stmt->execute([$fileId]);
  $file = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$file) {
    echo json_encode(['success' => false, 'message' => 'File not found']);
    exit;
  }

  $type = $file['type'];
  $path = $file['path'];
  $name = $file['name'];
  $success = false;

  // 🧠 Determine permission source
  $access = getEffectivePermissionsWithSource($pdo, $fileId, $userId);
  $source = $access['sourceType'] === 'owner' ? 'owner-delete' : ($access['sourceType'] === 'direct' ? 'direct-delete' : 'inherited-delete');

  if ($type === 'folder') {
    $trashRoot = "/srv/burol-storage/$userId/trash/$fileId";
    $success = softDeleteFolderAndContents($pdo, $userId, $fileId, false, $trashRoot);
  } elseif ($type === 'file') {
    $realPath = resolveDiskPath($path);
    $relativePath = substr($path, strlen("/srv/burol-storage/$userId"));
    $trashPath = "/srv/burol-storage/$userId/trash" . $relativePath;
    $trashFullPath = resolveDiskPath($trashPath);

    ensureVirtualPathExists($trashPath);

    if (is_file($realPath)) {
      rename($realPath, $trashFullPath);
    }

    $stmt = $pdo->prepare("
      UPDATE files
      SET is_deleted = 1,
          original_path = path,
          path = ?,
          deleted_by_parent = 0,
          updated_at = NOW()
      WHERE id = ?
    ");
    $success = $stmt->execute([$trashPath, $fileId]);
  }

  // 📝 Log deletion
  $log = $pdo->prepare("
    INSERT INTO logs (id, file_id, file_name, user_id, action, details, source)
    VALUES (UUID(), ?, ?, ?, 'delete', ?, 'dashboard')
  ");
  $log->execute([
    $fileId,
    $name,
    $userId,
    "Soft delete triggered via $source"
  ]);

  echo json_encode([
    'success' => $success,
    'message' => $success ? 'Item deleted successfully' : 'Failed to delete item'
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Server error: ' . $e->getMessage()
  ]);
}