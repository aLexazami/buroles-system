<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// 🔐 Role check
if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['error' => 'Access denied']);
  exit;
}

// 🧾 Extract and sanitize POST data
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$label = $_POST['label'] ?? '';

if (
  !$startDate || !$endDate || !$label ||
  !preg_match('/^SY\d{4}-\d{4}$/', $label)
) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing or invalid fields']);
  exit;
}

// 🗓️ Parse years from dates
$startYear = (int) date('Y', strtotime($startDate));
$endYear = (int) date('Y', strtotime($endDate));

// 🔍 Validate logical year range
if ($endYear <= $startYear) {
  echo json_encode(['error' => 'End year must be greater than start year']);
  exit;
}

// 🔍 Check for duplicate label
$stmt = $pdo->prepare("SELECT id FROM school_years WHERE label = ?");
$stmt->execute([$label]);
if ($stmt->fetchColumn()) {
  echo json_encode(['error' => 'School year already exists']);
  exit;
}

// ✅ Insert school year
$stmt = $pdo->prepare("
  INSERT INTO school_years (label, start_year, end_year, start_date, end_date, is_active)
  VALUES (?, ?, ?, ?, ?, 1)
");
$stmt->execute([$label, $startYear, $endYear, $startDate, $endDate]);

echo json_encode(['success' => true]);