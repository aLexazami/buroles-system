<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/folder-utils.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$folderId = $input['id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$folderId || !$userId) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Missing folder ID or user']);
  exit;
}

try {
  $size = getRecursiveFolderSize($pdo, $folderId, $userId);
  echo json_encode(['success' => true, 'size' => $size]);
} catch (Exception $e) {
  error_log("❌ Folder size error: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Server error']);
}