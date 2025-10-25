<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
if (!is_numeric($userId)) {
  echo json_encode(['success' => false, 'error' => 'Invalid session']);
  exit;
}

$stmt = $pdo->prepare("SELECT storage_limit, storage_used FROM user_storage WHERE user_id = ?");
$stmt->execute([$userId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  echo json_encode(['success' => false, 'error' => 'Storage info not found']);
  exit;
}

echo json_encode([
  'success' => true,
  'storage_limit' => (int) $row['storage_limit'],   // bytes
  'storage_used' => (int) $row['storage_used'],     // bytes
  'max_file_size' => 1073741824                     // 1GB in bytes
]);