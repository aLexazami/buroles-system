<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Validate ID
$id = $_POST['id'] ?? null;
if (!$id || !ctype_digit($id)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid grade level ID']);
  exit;
}

// Delete grade level
$delete = $pdo->prepare("DELETE FROM grade_levels WHERE id = ?");
$delete->execute([$id]);

if ($delete->rowCount() === 0) {
  http_response_code(404);
  echo json_encode(['error' => 'Grade level not found or already deleted']);
  exit;
}

echo json_encode(['success' => true]);