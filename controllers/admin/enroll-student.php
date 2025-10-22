<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$classId = $_POST['class_id'] ?? null;
$studentId = $_POST['student_id'] ?? null;

if (!$classId || !$studentId || !ctype_digit($classId) || !ctype_digit($studentId)) {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE enrollments SET class_id = ? WHERE student_id = ? AND class_id IS NULL");
  $stmt->execute([$classId, $studentId]);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'error' => 'Enrollment failed']);
}