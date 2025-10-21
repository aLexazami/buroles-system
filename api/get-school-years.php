<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['error' => 'Access denied']);
  exit;
}

// 🧠 Updated query to include full dates
$schoolYears = $pdo->query("
  SELECT id, label, start_date, end_date, is_active
  FROM school_years
  ORDER BY start_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['schoolYears' => $schoolYears]);