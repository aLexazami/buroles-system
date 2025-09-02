<?php

function getRespondentCount($type, $pdo)
{
    switch ($type) {
        case 'new':
            $sql = "SELECT COUNT(*) FROM feedback_respondents WHERE DATE(submitted_at) = CURDATE()";
            break;
        case 'weekly':
            $sql = "SELECT COUNT(*) FROM feedback_respondents WHERE YEAR(submitted_at) = YEAR(CURDATE()) AND WEEK(submitted_at, 1) = WEEK(CURDATE(), 1)";
            break;
        case 'annual':
            $sql = "SELECT COUNT(*) FROM feedback_respondents WHERE YEAR(submitted_at) = YEAR(CURDATE())";
            break;
        default:
            return 0;
    }

    return $pdo->query($sql)->fetchColumn();
}

function getCustomerTypeCounts($pdo)
{
    $sql = "SELECT customer_type, COUNT(*) AS count FROM feedback_respondents GROUP BY customer_type";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $counts = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $counts[$row['customer_type']] = $row['count'];
    }
    return $counts;
}

function getAgeGroupCounts($pdo)
{
    $sql = "
        SELECT
            CASE
                WHEN age <= 19 THEN 'under-19'
                WHEN age BETWEEN 20 AND 34 THEN '20-34'
                WHEN age BETWEEN 35 AND 49 THEN '35-49'
                WHEN age BETWEEN 50 AND 64 THEN '50-64'
                ELSE '65-up'
            END AS age_group,
            COUNT(*) AS count
        FROM feedback_respondents
        GROUP BY age_group
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $counts = [
        'under-19' => 0,
        '20-34' => 0,
        '35-49' => 0,
        '50-64' => 0,
        '65-up' => 0
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $counts[$row['age_group']] = $row['count'];
    }

    return $counts;
}

function getCharterAwarenessCounts($pdo)
{
    $sql = "SELECT citizen_charter_awareness, COUNT(*) AS count FROM feedback_answers GROUP BY citizen_charter_awareness";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $counts = [
        'yes' => 0,
        'no' => 0
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = strtolower($row['citizen_charter_awareness']); // normalize to lowercase
        if (isset($counts[$key])) {
            $counts[$key] = $row['count'];
        }
    }

    return $counts;
}

function getCitizenCharterResponses($pdo)
{
    $response = [];

    foreach (['cc1', 'cc2', 'cc3'] as $field) {
        $sql = "SELECT $field, COUNT(*) AS count FROM feedback_answers WHERE $field IS NOT NULL GROUP BY $field";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = "{$field}-{$row[$field]}";
            $response[$key] = $row['count'];
        }
    }

    return $response;
}

function getSQDMatrixCounts($pdo)
{
    $sqdItems = ['sqd1', 'sqd2', 'sqd3', 'sqd4', 'sqd5', 'sqd6', 'sqd7', 'sqd8'];
    $ratings = ['5', '4', '3', '2', '1', 'na'];
    $counts = [];

    foreach ($sqdItems as $sqd) {
        foreach ($ratings as $rating) {
            $sql = "SELECT COUNT(*) FROM feedback_answers WHERE $sqd = :rating";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['rating' => $rating]);
            $counts["{$sqd}-{$rating}"] = $stmt->fetchColumn();
        }
    }

    return $counts;
}

function getServicesByCustomerType($pdo, $type)
{
    $sql = "SELECT id, name FROM services WHERE customer_type = :type ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['type' => $type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
