<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// ✅ Only admins can toggle status
if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Access denied']);
  exit;
}

$id = intval($_POST['id'] ?? 0);
$isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : null;

if ($id <= 0 || !in_array($isActive, [0, 1], true)) {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit;
}

try {
  $pdo->beginTransaction();

  if ($isActive === 1) {
    // ✅ Deactivate all other school years
    $pdo->prepare("UPDATE school_years SET is_active = 0 WHERE id != :id")->execute(['id' => $id]);
  }

  // ✅ Update selected school year
  $pdo->prepare("UPDATE school_years SET is_active = :active WHERE id = :id")->execute([
    'active' => $isActive,
    'id' => $id
  ]);

  $pdo->commit();
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  $pdo->rollBack();
  error_log('Toggle status error: ' . $e->getMessage());
  echo json_encode(['success' => false, 'error' => 'Database error']);
}