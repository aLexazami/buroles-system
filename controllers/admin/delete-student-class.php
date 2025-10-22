<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// 🔍 Capture incoming POST values
$studentId = $_POST['student_id'] ?? null;
$classId = $_POST['class_id'] ?? null;

// 🧪 Debug log
error_log("student_id: " . var_export($studentId, true));
error_log("class_id: " . var_export($classId, true));

// ✅ Validate input
if (!$studentId || !$classId || !ctype_digit((string)$studentId) || !ctype_digit((string)$classId)) {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE enrollments SET class_id = NULL WHERE student_id = ? AND class_id = ?");
  $stmt->execute([$studentId, $classId]);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'error' => 'Failed to remove student']);
}