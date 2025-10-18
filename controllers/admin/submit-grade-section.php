<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Get POST data
$grade_level_id = $_POST['grade_level_id'] ?? null;
$section_label = $_POST['section_label'] ?? null;

// Validate input
if (!$grade_level_id || !$section_label || !ctype_digit($grade_level_id)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid input']);
  exit;
}

$section_label = trim($section_label);

// Check if grade level exists
$exists = $pdo->prepare("SELECT id FROM grade_levels WHERE id = ?");
$exists->execute([$grade_level_id]);
if (!$exists->fetchColumn()) {
  http_response_code(404);
  echo json_encode(['error' => 'Grade level not found']);
  exit;
}

// Check for duplicate section
$check = $pdo->prepare("
  SELECT id FROM grade_sections
  WHERE grade_level_id = ? AND LOWER(section_label) = LOWER(?) AND is_active = TRUE
");
$check->execute([$grade_level_id, $section_label]);
if ($check->fetchColumn()) {
  echo json_encode(['error' => 'Section already exists for this grade level']);
  exit;
}

// Insert new section
$insert = $pdo->prepare("
  INSERT INTO grade_sections (grade_level_id, section_label)
  VALUES (?, ?)
");
$insert->execute([$grade_level_id, $section_label]);

echo json_encode(['success' => true]);