<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

$id = $_POST['id'] ?? null;
$levelRaw = $_POST['level'] ?? null;
$label = $_POST['label'] ?? null;

if (!$id || !$levelRaw || !$label || !ctype_digit($levelRaw)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid input']);
  exit;
}

$level = (int) $levelRaw;
$label = trim($label);

// Check for duplicate level or label (excluding current ID)
$check = $pdo->prepare("SELECT id FROM grade_levels WHERE (level = ? OR LOWER(label) = LOWER(?)) AND id != ?");
$check->execute([$level, $label, $id]);
if ($check->fetchColumn()) {
  echo json_encode(['error' => 'Grade level or label already exists']);
  exit;
}

// Update
$update = $pdo->prepare("UPDATE grade_levels SET level = ?, label = ? WHERE id = ?");
$update->execute([$level, $label, $id]);

echo json_encode(['success' => true]);