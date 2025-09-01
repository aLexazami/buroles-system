<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$service_id = $_GET['service_id'] ?? null;
$year = $_GET['year'] ?? date('Y');

if (!$service_id || !is_numeric($service_id)) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing or invalid service ID']);
  exit;
}

// 🔧 Modularized Functions
function getRespondentCount($pdo, $service_id, $year) {
  $stmt = $pdo->prepare("
    SELECT COUNT(*) FROM feedback_respondents
    WHERE service_availed_id = :service_id AND YEAR(date) = :year
  ");
  $stmt->execute(['service_id' => $service_id, 'year' => $year]);
  return (int)$stmt->fetchColumn();
}

function getDemographics($pdo, $service_id, $year) {
  $stmt = $pdo->prepare("
    SELECT sex, COUNT(*) AS count
    FROM feedback_respondents
    WHERE service_availed_id = :service_id AND YEAR(date) = :year
    GROUP BY sex
  ");
  $stmt->execute(['service_id' => $service_id, 'year' => $year]);
  $counts = ['male' => 0, 'female' => 0];
  foreach ($stmt->fetchAll() as $row) {
    $key = strtolower($row['sex']);
    if (isset($counts[$key])) $counts[$key] = (int)$row['count'];
  }
  return $counts;
}

function getAgeGroups($pdo, $service_id, $year) {
  $stmt = $pdo->prepare("
    SELECT
      CASE
        WHEN age <= 19 THEN '19_or_lower'
        WHEN age BETWEEN 20 AND 34 THEN '20_34'
        WHEN age BETWEEN 35 AND 49 THEN '35_49'
        WHEN age BETWEEN 50 AND 64 THEN '50_64'
        ELSE '65_or_higher'
      END AS age_group,
      COUNT(*) AS count
    FROM feedback_respondents
    WHERE service_availed_id = :service_id AND YEAR(date) = :year
    GROUP BY age_group
  ");
  $stmt->execute(['service_id' => $service_id, 'year' => $year]);

  $groups = [
    '19_or_lower' => 0,
    '20_34' => 0,
    '35_49' => 0,
    '50_64' => 0,
    '65_or_higher' => 0
  ];

  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = $row['age_group'];
    $groups[$key] = (int)$row['count'];
  }

  return $groups;
}

function getCustomerTypes($pdo, $service_id, $year) {
  $stmt = $pdo->prepare("
    SELECT customer_type, COUNT(*) AS count
    FROM feedback_respondents
    WHERE service_availed_id = :service_id AND YEAR(date) = :year
    GROUP BY customer_type
  ");
  $stmt->execute(['service_id' => $service_id, 'year' => $year]);

  $types = ['Citizen' => 0, 'Business' => 0, 'Government' => 0];
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = ucfirst(strtolower($row['customer_type']));
    if (isset($types[$key])) $types[$key] = (int)$row['count'];
  }

  return $types;
}

function getSQDAverages($pdo, $service_id, $year) {
  $stmt = $pdo->prepare("
    SELECT sqd1, sqd2, sqd3, sqd4, sqd5, sqd6, sqd7, sqd8
    FROM feedback_answers
    WHERE respondent_id IN (
      SELECT id FROM feedback_respondents
      WHERE service_availed_id = :service_id AND YEAR(date) = :year
    )
  ");
  $stmt->execute(['service_id' => $service_id, 'year' => $year]);

  $totals = array_fill(1, 8, 0);
  $counts = array_fill(1, 8, 0);

  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    for ($i = 1; $i <= 8; $i++) {
      $score = $row["sqd$i"];
      if (is_numeric($score)) {
        $totals[$i] += $score;
        $counts[$i]++;
      }
    }
  }

  $averages = [];
  for ($i = 1; $i <= 8; $i++) {
    $averages[] = $counts[$i] ? round($totals[$i] / $counts[$i], 2) : 0;
  }

  return $averages;
}

function getCitizenCharterResponses($pdo, $service_id, $year) {
  $stmt = $pdo->prepare("
    SELECT cc1, cc2, cc3 FROM feedback_answers
    WHERE respondent_id IN (
      SELECT id FROM feedback_respondents
      WHERE service_availed_id = :service_id AND YEAR(date) = :year
    )
  ");
  $stmt->execute(['service_id' => $service_id, 'year' => $year]);

  $responses = [
    'cc1' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
    'cc2' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
    'cc3' => [1 => 0, 2 => 0, 3 => 0, 4 => 0]
  ];

  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    foreach (['cc1', 'cc2', 'cc3'] as $key) {
      $score = (int)$row[$key];
      if (isset($responses[$key][$score])) {
        $responses[$key][$score]++;
      }
    }
  }

  return $responses;
}

function getSQDBreakdowns($pdo, $service_id, $year) {
  $stmt = $pdo->prepare("
    SELECT sqd1, sqd2, sqd3, sqd4, sqd5, sqd6, sqd7, sqd8
    FROM feedback_answers
    WHERE respondent_id IN (
      SELECT id FROM feedback_respondents
      WHERE service_availed_id = :service_id AND YEAR(date) = :year
    )
  ");
  $stmt->execute(['service_id' => $service_id, 'year' => $year]);

  $breakdowns = [];
  for ($i = 1; $i <= 8; $i++) {
    $breakdowns["sqd$i"] = [
      1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 'na' => 0
    ];
  }

  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    for ($i = 1; $i <= 8; $i++) {
      $score = $row["sqd$i"];
      if ($score === null || $score === 'na') {
        $breakdowns["sqd$i"]['na']++;
      } elseif (is_numeric($score) && isset($breakdowns["sqd$i"][(int)$score])) {
        $breakdowns["sqd$i"][(int)$score]++;
      }
    }
  }

  return $breakdowns;
}

// 🔧 Execute and Respond
$respondents = getRespondentCount($pdo, $service_id, $year);
$demographics = getDemographics($pdo, $service_id, $year);
$sqd = getSQDAverages($pdo, $service_id, $year);
$ageGroups = getAgeGroups($pdo, $service_id, $year);
$customerTypes = getCustomerTypes($pdo, $service_id, $year);
$charterResponses = getCitizenCharterResponses($pdo, $service_id, $year);
$sqdBreakdowns = getSQDBreakdowns($pdo, $service_id, $year);

echo json_encode([
  'respondents' => $respondents,
  'male' => $demographics['male'],
  'female' => $demographics['female'],
  'sqd' => $sqd,
  'age' => $ageGroups,
  'customer_types' => $customerTypes,
  'charter' => $charterResponses,
  'sqd_breakdowns' => $sqdBreakdowns
]);