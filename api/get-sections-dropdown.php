<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$gradeLevelId = $_GET['grade_level_id'] ?? null;
if (!$gradeLevelId || !ctype_digit($gradeLevelId)) {
  echo json_encode(['error' => 'Invalid grade level']);
  exit;
}

$stmt = $pdo->prepare("SELECT id, section_label FROM grade_sections WHERE grade_level_id = ? AND is_active = 1 ORDER BY section_label ASC");
$stmt->execute([$gradeLevelId]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['sections' => $sections]);