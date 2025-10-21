<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id || !ctype_digit($id)) {
  echo json_encode(['success' => false, 'error' => 'Invalid class ID']);
  exit;
}

$stmt = $pdo->prepare("
  SELECT
    c.id,
    c.name,
    c.school_year_id,
    sy.label AS school_year_label,
    c.grade_section_id,
    gs.grade_level_id,
    gs.section_label
  FROM classes c
  JOIN grade_sections gs ON c.grade_section_id = gs.id
  JOIN school_years sy ON c.school_year_id = sy.id
  WHERE c.id = ?
");
$stmt->execute([$id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if ($class) {
  echo json_encode(['success' => true, 'class' => $class]);
} else {
  echo json_encode(['success' => false, 'error' => 'Class not found']);
}