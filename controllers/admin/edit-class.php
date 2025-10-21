<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// 🔐 Role check
if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Access denied']);
  exit;
}

// 📥 Extract and validate input
$id = $_POST['id'] ?? null;
$gradeLevelId = $_POST['grade_level'] ?? null;
$sectionId = $_POST['section_id'] ?? null;
$schoolYearId = $_POST['school_year_id'] ?? null;

if (
  !$id || !$gradeLevelId || !$sectionId || !$schoolYearId ||
  !ctype_digit($id) || !ctype_digit($gradeLevelId) ||
  !ctype_digit($sectionId) || !ctype_digit($schoolYearId)
) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Missing or invalid fields']);
  exit;
}

// 🔍 Validate school year exists and is active
$stmt = $pdo->prepare("SELECT label FROM school_years WHERE id = ? AND is_active = 1");
$stmt->execute([$schoolYearId]);
$schoolYear = $stmt->fetchColumn();
if (!$schoolYear) {
  echo json_encode(['success' => false, 'error' => 'Invalid or inactive school year']);
  exit;
}

// 🔍 Validate grade level and section match
$stmt = $pdo->prepare("
  SELECT gs.id AS grade_section_id, gs.section_label, gl.label AS grade_label
  FROM grade_sections gs
  JOIN grade_levels gl ON gs.grade_level_id = gl.id
  WHERE gs.id = ? AND gs.grade_level_id = ? AND gs.is_active = 1
");
$stmt->execute([$sectionId, $gradeLevelId]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
  echo json_encode(['success' => false, 'error' => 'Grade level and section mismatch or inactive']);
  exit;
}

$gradeSectionId = $match['grade_section_id'];
$className = $match['grade_label'] . ' - Section ' . $match['section_label'];

// 🛠️ Update class
try {
  $stmt = $pdo->prepare("
    UPDATE classes
    SET name = ?, grade_section_id = ?, school_year_id = ?
    WHERE id = ?
  ");
  $stmt->execute([$className, $gradeSectionId, $schoolYearId, $id]);

  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  error_log("Edit class error: " . $e->getMessage());
  echo json_encode(['success' => false, 'error' => 'Server error']);
}