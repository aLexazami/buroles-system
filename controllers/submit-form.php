<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/notify.php';
require_once __DIR__ . '/../helpers/notification-icon.php';

//if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
if ((php_sapi_name() === 'cli' || $_SERVER["REQUEST_METHOD"] === "POST") && isset($_POST['submit'])) {
  try {
    // Respondent Info
    $name = $_POST['name'] ?? null;
    $date = $_POST['date'] ?? null;
    $age = $_POST['age'] ?? null;
    $sex = $_POST['sex'] ?? null;
    $customer_type = $_POST['customer_type'] ?? null;
    $service_availed_id = $_POST['service_availed'] ?? null;
    $region_id = $_POST['region'] ?? null;

    // Citizen Charter Awareness
    $citizenCharterAwareness = $_POST['yes_no'] ?? null;
    if ($citizenCharterAwareness !== null) {
      $citizenCharterAwareness = ucfirst(strtolower($citizenCharterAwareness));
    }

    // Conditional CC questions
    $cc1 = $cc2 = $cc3 = null;
    if ($citizenCharterAwareness === "Yes") {
      $cc1 = $_POST['cc-1'] ?? null;
      $cc2 = $_POST['cc-2'] ?? null;
      $cc3 = $_POST['cc-3'] ?? null;
    }

    // Satisfaction Questions
    $sqd1 = $_POST['SQD1'] ?? null;
    $sqd2 = $_POST['SQD2'] ?? null;
    $sqd3 = $_POST['SQD3'] ?? null;
    $sqd4 = $_POST['SQD4'] ?? null;
    $sqd5 = $_POST['SQD5'] ?? null;
    $sqd6 = $_POST['SQD6'] ?? null;
    $sqd7 = $_POST['SQD7'] ?? null;
    $sqd8 = $_POST['SQD8'] ?? null;
    $remarks = $_POST['remarks'] ?? null;

    // Insert into feedback_respondents
    $stmt1 = $pdo->prepare("
      INSERT INTO feedback_respondents (name, date, age, sex, customer_type, service_availed_id, region_id)
      VALUES (:name, :date, :age, :sex, :customer_type, :service_availed_id, :region_id)");

    $stmt1->execute([
      'name' => $name,
      'date' => $date,
      'age' => $age,
      'sex' => $sex,
      'customer_type' => $customer_type,
      'service_availed_id' => $service_availed_id,
      'region_id' => $region_id
    ]);


    $respondent_id = $pdo->lastInsertId();

    // Insert into feedback_answers
    $stmt2 = $pdo->prepare("
      INSERT INTO feedback_answers (
        respondent_id, citizen_charter_awareness, cc1, cc2, cc3,sqd1, sqd2, sqd3, sqd4, sqd5, sqd6, sqd7, sqd8, remarks)
        VALUES (:respondent_id, :citizen_charter_awareness, :cc1, :cc2, :cc3,:sqd1, :sqd2, :sqd3, :sqd4, :sqd5, :sqd6, :sqd7, :sqd8, :remarks)
    ");

    $stmt2->execute([
      'respondent_id' => $respondent_id,
      'citizen_charter_awareness' => $citizenCharterAwareness,
      'cc1' => $cc1,
      'cc2' => $cc2,
      'cc3' => $cc3,
      'sqd1' => $sqd1,
      'sqd2' => $sqd2,
      'sqd3' => $sqd3,
      'sqd4' => $sqd4,
      'sqd5' => $sqd5,
      'sqd6' => $sqd6,
      'sqd7' => $sqd7,
      'sqd8' => $sqd8,
      'remarks' => $remarks
    ]);

    // Prepare notification details
    $displayName = $name ?: 'Anonymous';

    // Optional: fetch service name
    $serviceName = 'Unknown Service';
    if ($service_availed_id) {
      $stmtService = $pdo->prepare("SELECT name FROM services WHERE id = ?");
      $stmtService->execute([$service_availed_id]);
      $serviceName = $stmtService->fetchColumn() ?: $serviceName;
    }

    $regionName = 'Unknown Region';
    if ($region_id) {
      $stmtRegion = $pdo->prepare("SELECT code FROM regions WHERE id = ?");
      $stmtRegion->execute([$region_id]);
      $regionName = $stmtRegion->fetchColumn() ?: $regionName;
    }

    $icon = getNotificationIcon(
      "New Respondent #{$respondent_id}: {$displayName}",
      "Customer Type: {$customer_type} | Service Availed: {$serviceName} | Region: {$regionName}"
    );

    // Send notification to Admins
    sendNotification(
      "New Respondent #{$respondent_id}: {$displayName}",
      "Customer Type: {$customer_type} | Service Availed: {$serviceName} | Region: {$regionName}",
      "/pages/admin/feedback-respondents.php",
      0,
      2,
      $icon
    );


    // Redirect on success
    header("Location: thank-you.php");
    exit;
  } catch (PDOException $e) {
    error_log($e->getMessage());
    die("Something went wrong. Please try again later.");
  }
}
