<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$stmt = $pdo->query("
  SELECT
    gs.id,
    gs.grade_level_id,
    gs.section_label,
    gl.label AS grade_label
  FROM grade_sections gs
  JOIN grade_levels gl ON gs.grade_level_id = gl.id
  WHERE gs.is_active = TRUE
  ORDER BY gl.level ASC, gs.section_label ASC
");

$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['gradeSections' => $sections]);