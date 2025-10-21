<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// ✅ Only admins can delete school years
if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Access denied']);
  exit;
}

// ✅ Validate input
$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
  echo json_encode(['success' => false, 'error' => 'Invalid school year ID']);
  exit;
}

try {
  $stmt = $pdo->prepare("DELETE FROM school_years WHERE id = :id");
  $stmt->execute(['id' => $id]);

  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  error_log('Delete error: ' . $e->getMessage());
  echo json_encode(['success' => false, 'error' => 'Database error']);
}