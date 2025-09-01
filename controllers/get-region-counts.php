<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$stmt = $pdo->query("
  SELECT slug, COUNT(r.id) AS count
  FROM regions reg
  LEFT JOIN feedback_respondents r ON r.region_id = reg.id
  GROUP BY reg.slug
");

$counts = [];
while ($row = $stmt->fetch()) {
  $counts[$row['slug']] = $row['count'];
}

echo json_encode($counts);
?>