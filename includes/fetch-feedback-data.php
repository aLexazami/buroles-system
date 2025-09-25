<?php
require_once __DIR__ . '/../config/database.php';

try {
  $query = "
    SELECT
  r.id,
  r.name,
  r.date,
  r.age,
  r.sex,
  r.customer_type,
  COALESCE(s.name, 'Not Specified') AS service_availed,
  CONCAT(reg.code, ' - ', reg.name) AS region,
  reg.slug AS region_slug,
  r.submitted_at,
  r.is_read, -- ✅ Add this line
  a.citizen_charter_awareness,
  a.cc1, a.cc2, a.cc3,
  a.sqd1, a.sqd2, a.sqd3, a.sqd4,
  a.sqd5, a.sqd6, a.sqd7, a.sqd8,
  a.remarks
    FROM feedback_respondents r
    LEFT JOIN services s ON r.service_availed_id = s.id
    LEFT JOIN feedback_answers a ON r.id = a.respondent_id
    LEFT JOIN regions reg ON r.region_id = reg.id
    ORDER BY r.submitted_at DESC
  ";

  $stmt = $pdo->query($query);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Optional: log how many rows were fetched
  error_log("Fetched " . count($results) . " feedback entries.");
} catch (PDOException $e) {
  error_log("Feedback fetch error: " . $e->getMessage());
  $results = [];
}
