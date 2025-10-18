<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$teacherId = $_SESSION['user_id'];

if (!$firstName || !$lastName || !$birthdate) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing required fields']);
  exit;
}

// Get advisory class ID
$classQuery = "SELECT id FROM classes WHERE adviser_id = ?";
$classStmt = $pdo->prepare($classQuery);
$classStmt->execute([$teacherId]);
$classId = $classStmt->fetchColumn();

if (!$classId) {
  echo json_encode(['error' => 'No advisory class found']);
  exit;
}

$insert = "INSERT INTO students (first_name, last_name, birthdate, class_id)
           VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($insert);
$stmt->execute([$firstName, $lastName, $birthdate, $classId]);

echo json_encode(['success' => true]);