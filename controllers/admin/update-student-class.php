<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$studentId = $_POST['student_id'] ?? null;
$gradeSectionId = $_POST['grade_section_id'] ?? null;
$schoolYearId = $_POST['school_year_id'] ?? null;

if (!$studentId || !$gradeSectionId || !$schoolYearId) {
  echo json_encode(['success' => false, 'error' => 'Missing required fields']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    UPDATE enrollments
    SET grade_section_id = ?
    WHERE student_id = ? AND school_year_id = ?
  ");
  $stmt->execute([$gradeSectionId, $studentId, $schoolYearId]);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'error' => 'Update failed']);
}