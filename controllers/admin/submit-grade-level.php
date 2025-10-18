<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

$levelRaw = $_POST['level'] ?? null;
$label = $_POST['label'] ?? null;

// Basic presence check
if ($levelRaw === null || $label === null || trim($label) === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Missing required fields']);
  exit;
}

// Validate that level is numeric
if (!ctype_digit($levelRaw)) {
  http_response_code(400);
  echo json_encode(['error' => 'Grade level must be a number']);
  exit;
}

$level = (int) $levelRaw;
$label = trim($label);

// Check for duplicate level or label
$check = $pdo->prepare("SELECT id FROM grade_levels WHERE level = ? OR LOWER(label) = LOWER(?)");
$check->execute([$level, $label]);
if ($check->fetchColumn()) {
  echo json_encode(['error' => 'Grade level or label already exists']);
  exit;
}

// Insert new grade level
$insert = $pdo->prepare("INSERT INTO grade_levels (level, label) VALUES (?, ?)");
$insert->execute([$level, $label]);

echo json_encode(['success' => true]);