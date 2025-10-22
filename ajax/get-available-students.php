<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// 🔐 Validate class ID
$classId = $_GET['class_id'] ?? null;
if (!$classId || !ctype_digit($classId)) {
  echo json_encode(['success' => false, 'error' => 'Invalid class ID']);
  exit;
}

try {
  // 🔍 Get grade_section_id and school_year_id from class
  $stmt = $pdo->prepare("
    SELECT grade_section_id, school_year_id
    FROM classes
    WHERE id = ?
  ");
  $stmt->execute([$classId]);
  $classMeta = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$classMeta) {
    throw new Exception('Class not found');
  }

  $gradeSectionId = $classMeta['grade_section_id'];
  $schoolYearId = $classMeta['school_year_id'];

  // 🔍 Get grade_level_id from grade_section
  $stmt = $pdo->prepare("
    SELECT grade_level_id
    FROM grade_sections
    WHERE id = ?
  ");
  $stmt->execute([$gradeSectionId]);
  $gradeLevelId = $stmt->fetchColumn();

  if (!$gradeLevelId) {
    throw new Exception('Grade level not found');
  }

  // 🎯 Fetch students matching grade section and school year, but not yet assigned to any class
  $stmt = $pdo->prepare("
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
    WHERE enrollments.grade_section_id = ?
      AND enrollments.school_year_id = ?
      AND enrollments.class_id IS NULL
    ORDER BY students.last_name ASC, students.first_name ASC
  ");
  $stmt->execute([$gradeSectionId, $schoolYearId]);
  $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'students' => $students
  ]);
} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'error' => 'Failed to fetch available students.',
    'details' => $e->getMessage()
  ]);
}