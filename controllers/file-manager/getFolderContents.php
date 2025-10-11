<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php';         // isValidUuid()
require_once __DIR__ . '/../../helpers/file-utils.php';   // getFilesForView()

header('Content-Type: application/json');

// 🛡️ Auth check
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// 📥 Input sanitization
$folderId = $_GET['folder_id'] ?? null;
$folderId = isValidUuid($folderId) ? $folderId : null;

$view     = $_GET['view']     ?? 'my-files';
$sortBy   = $_GET['sort_by']  ?? 'updated_at';
$sortDir  = $_GET['sort_dir'] ?? 'DESC';

$folderIsDeleted = false;

// 🧼 Trash view: check if parent folder is deleted
if ($view === 'trash' && $folderId) {
  $check = $pdo->prepare("SELECT is_deleted FROM files WHERE id = ? AND owner_id = ?");
  $check->execute([$folderId, $userId]);
  $folderIsDeleted = (int) $check->fetchColumn() === 1;

  if ($folderIsDeleted) {
    echo json_encode([
      'items' => [],
      'folder_is_deleted' => true
    ]);
    exit;
  }
}

// 📄 Fetch files scoped to view and folder
$files = getFilesForView($userId, $view, $folderId, $sortBy, $sortDir);

// 📤 Respond with items and folder status
echo json_encode([
  'items' => $files,
  'folder_is_deleted' => $folderIsDeleted
]);