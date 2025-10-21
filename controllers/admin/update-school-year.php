<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// ✅ Ensure only admins can update
if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Access denied']);
  exit;
}

// ✅ Validate and sanitize input
$id         = intval($_POST['id'] ?? 0);
$label      = trim($_POST['label'] ?? '');
$startDate  = trim($_POST['start_date'] ?? '');
$endDate    = trim($_POST['end_date'] ?? '');
$isActive   = isset($_POST['is_active']) ? 1 : 0;

if ($id <= 0 || empty($label) || empty($startDate) || empty($endDate)) {
  echo json_encode(['success' => false, 'error' => 'Missing required fields']);
  exit;
}

// ✅ Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
  echo json_encode(['success' => false, 'error' => 'Invalid date format']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    UPDATE school_years
    SET label = :label,
        start_date = :start_date,
        end_date = :end_date,
        is_active = :is_active
    WHERE id = :id
  ");

  $stmt->execute([
    'label'       => $label,
    'start_date'  => $startDate,
    'end_date'    => $endDate,
    'is_active'   => $isActive,
    'id'          => $id
  ]);

  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  error_log('Update error: ' . $e->getMessage());
  echo json_encode(['success' => false, 'error' => 'Database error']);
}