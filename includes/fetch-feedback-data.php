<?php
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query("
  SELECT
    r.id, r.name, r.date, r.age, r.sex, r.customer_type,
    s.name AS service_availed,
    CONCAT(reg.code, ' - ', reg.name) AS region,
    reg.slug AS region_slug,
    r.submitted_at,
    a.citizen_charter_awareness, a.cc1, a.cc2, a.cc3,
    a.sqd1, a.sqd2, a.sqd3, a.sqd4, a.sqd5, a.sqd6, a.sqd7, a.sqd8,
    a.remarks
  FROM feedback_respondents r
  JOIN services s ON r.service_availed_id = s.id
  JOIN feedback_answers a ON r.id = a.respondent_id
  JOIN regions reg ON r.region_id = reg.id
  ORDER BY r.submitted_at DESC
");

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>