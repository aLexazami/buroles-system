<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../includes/fetch-feedback-data.php';
require_once __DIR__ . '/../includes/CSVExporter.php';
require_once __DIR__ . '/../helpers/flash.php'; // ✅ Include flash helpers

// ✅ Define fields
$fields = [
  'id', 'name', 'date', 'age', 'sex', 'customer_type',
  'service_availed', 'region', 'submitted_at',
  'citizen_charter_awareness', 'cc1', 'cc2', 'cc3',
  'sqd1', 'sqd2', 'sqd3', 'sqd4', 'sqd5', 'sqd6', 'sqd7', 'sqd8',
  'remarks'
];

// ✅ Fetch data
$results = getFeedbackData($pdo);

// ✅ Fallback if no data
if (empty($results)) {
  setFlash('warning', 'No feedback data available to export.');
  header('Location: /pages/admin/feedback-respondents.php');
  exit;
}

// ✅ Export using the class
$exporter = new CSVExporter($fields, 'feedback_full_export.csv');
$exporter->stream($results);