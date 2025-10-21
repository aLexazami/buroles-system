<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// 🧾 Extract and sanitize POST data
$adviserId = $_POST['adviser_id'] ?? null;
$gradeLevelId = $_POST['grade_level'] ?? null;
$sectionId = $_POST['section_id'] ?? null;
$schoolYearId = $_POST['school_year_id'] ?? null;

if (
  !$adviserId || !$gradeLevelId || !$sectionId || !$schoolYearId ||
  !ctype_digit($adviserId) || !ctype_digit($gradeLevelId) ||
  !ctype_digit($sectionId) || !ctype_digit($schoolYearId)
) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing or invalid fields']);
  exit;
}

// 🔍 Validate adviser exists and is active
$stmt = $pdo->prepare("
  SELECT id FROM users
  WHERE id = ? AND role_id = 1 AND is_archived = 0 AND is_locked = 0
");
$stmt->execute([$adviserId]);
if (!$stmt->fetchColumn()) {
  echo json_encode(['error' => 'Adviser not found or inactive']);
  exit;
}

// 🔍 Validate school year exists
$stmt = $pdo->prepare("
  SELECT label FROM school_years
  WHERE id = ? AND is_active = 1
");
$stmt->execute([$schoolYearId]);
$schoolYear = $stmt->fetchColumn();
if (!$schoolYear) {
  echo json_encode(['error' => 'Invalid or inactive school year']);
  exit;
}

// 🔍 Validate grade level and section match, and get grade_section_id
$stmt = $pdo->prepare("
  SELECT gs.id AS grade_section_id, gs.section_label, gl.label AS grade_label
  FROM grade_sections gs
  JOIN grade_levels gl ON gs.grade_level_id = gl.id
  WHERE gs.id = ? AND gs.grade_level_id = ?
");
$stmt->execute([$sectionId, $gradeLevelId]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
  echo json_encode(['error' => 'Grade level and section mismatch']);
  exit;
}

$gradeSectionId = $match['grade_section_id'];
$className = $match['grade_label'] . ' - Section ' . $match['section_label'];

// 🔍 Check for duplicate advisory class for this adviser, grade_section, and school_year
$check = $pdo->prepare("
  SELECT id FROM classes
  WHERE adviser_id = ? AND grade_section_id = ? AND school_year_id = ?
");
$check->execute([$adviserId, $gradeSectionId, $schoolYearId]);
if ($check->fetchColumn()) {
  echo json_encode(['error' => "You already created $className for $schoolYear"]);
  exit;
}

// ✅ Insert advisory class
$insert = $pdo->prepare("
  INSERT INTO classes (name, grade_section_id, adviser_id, school_year_id)
  VALUES (?, ?, ?, ?)
");
$insert->execute([$className, $gradeSectionId, $adviserId, $schoolYearId]);

echo json_encode(['success' => true]);