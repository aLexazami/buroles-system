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

// Optional: check if grade level exists
$stmt = $pdo->prepare("SELECT id FROM grade_levels WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetchColumn()) {
  http_response_code(404);
  echo json_encode(['error' => 'Grade level not found']);
  exit;
}

// Optional: check if grade level is in use (e.g. linked to sections or students)
// $check = $pdo->prepare("SELECT COUNT(*) FROM sections WHERE grade_level_id = ?");
// $check->execute([$id]);
// if ($check->fetchColumn() > 0) {
//   echo json_encode(['error' => 'Grade level is in use and cannot be deleted']);
//   exit;
// }

// Delete grade level
$delete = $pdo->prepare("DELETE FROM grade_levels WHERE id = ?");
$delete->execute([$id]);

echo json_encode(['success' => true]);