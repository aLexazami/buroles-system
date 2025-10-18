<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

$name = $_POST['name'] ?? '';
$grade = $_POST['grade_level'] ?? '';
$section = $_POST['section'] ?? '';
$adviserId = $_SESSION['user_id'];

if (!$name || !$grade || !$section) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing required fields']);
  exit;
}

// Check if this grade-level + section already exists for this adviser
$check = $pdo->prepare("SELECT id FROM classes WHERE adviser_id = ? AND grade_level = ? AND section = ?");
$check->execute([$adviserId, $grade, $section]);
if ($check->fetchColumn()) {
  echo json_encode(['error' => 'You already created Grade ' . $grade . ' - Section ' . htmlspecialchars($section)]);
  exit;
}

$insert = $pdo->prepare("INSERT INTO classes (name, grade_level, section, adviser_id) VALUES (?, ?, ?, ?)");
$insert->execute([$name, $grade, $section, $adviserId]);

echo json_encode(['success' => true]);