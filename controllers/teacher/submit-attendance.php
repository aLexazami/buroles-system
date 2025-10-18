<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

$studentId = $_POST['student_id'] ?? null;
$status = $_POST['status'] ?? null;
$teacherId = $_SESSION['user_id'];

if (!$studentId || !$status) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing student or status']);
  exit;
}

$query = "INSERT INTO attendance (student_id, date, status, recorded_by)
          VALUES (?, CURDATE(), ?, ?)";
$stmt = $pdo->prepare($query);
$stmt->execute([$studentId, $status, $teacherId]);

echo json_encode(['success' => true]);