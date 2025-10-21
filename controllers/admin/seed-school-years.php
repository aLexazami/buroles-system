<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  exit('Access denied');
}

header('Content-Type: text/plain');

$currentYear = date('Y');
$startYear = 2023;
$endYear = $currentYear + 5;

$stmt = $pdo->prepare("
  INSERT IGNORE INTO school_years (label, start_year, end_year, is_active)
  VALUES (?, ?, ?, 1)
");

$inserted = 0;
for ($year = $startYear; $year < $endYear; $year++) {
  $label = "SY{$year}-" . ($year + 1);
  $stmt->execute([$label, $year, $year + 1]);
  $inserted++;
}

echo "✅ Seeded {$inserted} school years from SY{$startYear}-" . ($endYear) . ".";