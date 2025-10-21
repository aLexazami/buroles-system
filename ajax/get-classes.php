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

$query = "SELECT id, name FROM classes WHERE adviser_id = ?";
$params = [$adviserId];

if ($schoolYearId && ctype_digit($schoolYearId)) {
  $query .= " AND school_year_id = ?";
  $params[] = $schoolYearId;
}

$query .= " ORDER BY name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['classes' => $classes]);