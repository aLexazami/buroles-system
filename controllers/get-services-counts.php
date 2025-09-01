<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$sql = "
  SELECT s.id, COUNT(r.id) AS count
  FROM feedback_respondents r
  JOIN services s ON r.service_availed_id = s.id
  GROUP BY s.id
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$response = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $response["service-" . $row['id']] = $row['count'];
}

echo json_encode($response);