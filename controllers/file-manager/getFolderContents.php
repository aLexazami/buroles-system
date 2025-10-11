<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php';
require_once __DIR__ . '/../../helpers/file-utils.php'; // getFilesForView()

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

$view = $_GET['view'] ?? 'my-files';
$sortBy = $_GET['sort_by'] ?? 'updated_at';
$sortDir = $_GET['sort_dir'] ?? 'DESC';

// 📄 Fetch all files (no pagination)
$files = getFilesForView($userId, $view, $folderId, $sortBy, $sortDir);

// 📤 Respond with items only
echo json_encode([
  'items' => $files
]);