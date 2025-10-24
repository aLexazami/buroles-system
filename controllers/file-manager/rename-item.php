<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/uuid.php';         // isValidUuid()
require_once __DIR__ . '/../../helpers/file-utils.php';   // getFileById(),
require_once __DIR__ . '/../../helpers/log.php';          // logAction()
require_once __DIR__ . '/../../helpers/access-utils.php'; // canPerformAction()
header('Content-Type: application/json');

// 🛡️ Auth check
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit;
}

// 📥 Input
$input = json_decode(file_get_contents('php://input'), true);
$fileId = $input['id'] ?? null;
$newName = trim($input['name'] ?? '');

if (!isValidUuid($fileId) || !$newName) {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit;
}

// 📄 Fetch file
$file = getFileById($pdo, $fileId);
if (!$file) {
  echo json_encode(['success' => false, 'error' => 'File not found']);
  exit;
}

// 🔐 Permission check
$canRename = canPerformAction($pdo, $fileId, $userId, 'rename');
if (!$canRename) {
  echo json_encode(['success' => false, 'error' => 'Permission denied']);
  exit;
}

// 🧠 Validate and preserve extension for files
if ($file['type'] === 'file') {
  $originalExt = pathinfo($file['name'], PATHINFO_EXTENSION);
  $newExt = pathinfo($newName, PATHINFO_EXTENSION);
  $base = pathinfo($newName, PATHINFO_FILENAME);
  $expectedName = $base . '.' . $originalExt;

  // 🛡️ Reject tampered extensions
  if (!$newExt || strtolower($newExt) !== strtolower($originalExt) || strtolower($newName) !== strtolower($expectedName)) {
    echo json_encode(['success' => false, 'error' => 'Invalid extension. Rename rejected.']);
    exit;
  }
} else {
  // 🧼 Strip accidental trailing dots from folder names
  $newName = rtrim($newName, '.');
}

// 📝 Update name
$stmt = $pdo->prepare("UPDATE files SET name = ?, updated_at = NOW() WHERE id = ?");
$success = $stmt->execute([$newName, $fileId]);

if ($success) {
  logAction($pdo, $userId, $fileId, $file['name'], 'rename', json_encode([
    'old_name' => $file['name'],
    'new_name' => $newName
  ]));

  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'Rename failed']);
}