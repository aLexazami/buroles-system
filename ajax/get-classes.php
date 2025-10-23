<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$adviserId = $_GET['adviser_id'] ?? null;
$schoolYearId = $_GET['school_year_id'] ?? null;

if (!$adviserId || !ctype_digit($adviserId)) {
  echo json_encode(['error' => 'Invalid adviser ID']);
  exit;
}

$query = "
  SELECT classes.id, classes.name, classes.is_active,
         CONCAT(gl.label, ' - ', gs.section_label) AS display_name
  FROM classes
  JOIN grade_sections gs ON classes.grade_section_id = gs.id
  JOIN grade_levels gl ON gs.grade_level_id = gl.id
  WHERE classes.adviser_id = ?
";
$params = [$adviserId];

if ($schoolYearId && ctype_digit($schoolYearId)) {
  $query .= " AND classes.school_year_id = ?";
  $params[] = $schoolYearId;
}

$query .= " ORDER BY gl.level ASC, gs.section_label ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ensure is_active is cast to integer for frontend logic
$classes = array_map(function ($row) {
  return [
    'id' => $row['id'],
    'name' => $row['display_name'],
    'is_active' => (int)$row['is_active']
  ];
}, $rows);

echo json_encode(['classes' => $classes]);