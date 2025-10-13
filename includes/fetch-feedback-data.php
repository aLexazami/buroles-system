<?php
require_once __DIR__ . '/../config/database.php';

function getFeedbackData(PDO $pdo): array {
  try {
    $query = "
      SELECT
        r.id, r.name, r.date, r.age, r.sex, r.customer_type,
        COALESCE(s.name, 'Not Specified') AS service_availed,
        CONCAT(reg.code, ' - ', reg.name) AS region,
        reg.slug AS region_slug,
        r.submitted_at, r.is_read,
        a.citizen_charter_awareness,
        a.cc1, a.cc2, a.cc3,
        a.sqd1, a.sqd2, a.sqd3, a.sqd4,
        a.sqd5, a.sqd6, a.sqd7, a.sqd8,
        a.remarks
      FROM feedback_respondents r
      LEFT JOIN services s ON r.service_availed_id = s.id
      LEFT JOIN feedback_answers a ON r.id = a.respondent_id
      LEFT JOIN regions reg ON r.region_id = reg.id
      ORDER BY r.id DESC
    ";

    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Feedback fetch error: " . $e->getMessage());
    return [];
  }
}