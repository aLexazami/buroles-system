<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
  $gradeLevelId = $_GET['grade_level_id'] ?? null;

  $sql = "
    SELECT
      students.id,
      students.lrn,
      CONCAT(students.last_name, ', ', students.first_name, ' ', students.middle_name) AS full_name,
      students.gender,
      students.photo_path,
      grade_levels.label AS grade_label,
      grade_sections.section_label
    FROM students
    JOIN enrollments ON enrollments.student_id = students.id
    JOIN grade_sections ON enrollments.grade_section_id = grade_sections.id
    JOIN grade_levels ON grade_sections.grade_level_id = grade_levels.id
  ";

  $params = [];

  if ($gradeLevelId) {
    $sql .= " WHERE grade_levels.id = ?";
    $params[] = $gradeLevelId;
  }

  $sql .= " ORDER BY students.last_name ASC, students.first_name ASC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'students' => $students
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'error' => 'Failed to fetch students.',
    'details' => $e->getMessage()
  ]);
}