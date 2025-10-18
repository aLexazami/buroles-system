<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Validate ID
$id = $_POST['id'] ?? null;

if (!$id || !ctype_digit($id)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid section ID']);
  exit;
}

// Check if section exists and is active
$check = $pdo->prepare("SELECT id FROM grade_sections WHERE id = ? AND is_active = TRUE");
$check->execute([$id]);

if (!$check->fetchColumn()) {
  http_response_code(404);
  echo json_encode(['error' => 'Section not found or already deleted']);
  exit;
}

// Soft delete
$delete = $pdo->prepare("UPDATE grade_sections SET is_active = FALSE WHERE id = ?");
$delete->execute([$id]);

echo json_encode(['success' => true]);