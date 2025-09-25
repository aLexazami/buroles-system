<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/notify.php';

// Simulated bot data
$_POST = [
  'submit' => true,
  'name' => 'Bot Tester Auto',
  'date' => date('Y-m-d'),
  'age' => '20-34',
  'sex' => 'Male',
  'customer_type' => 'Citizen',
  'service_availed' => 8,
  'region' => 17,
  'yes_no' => 'Yes',
  'cc-1' => 'Yes',
  'cc-2' => 'Yes',
  'cc-3' => 'Yes',
  'SQD1' => 5,
  'SQD2' => 4,
  'SQD3' => 5,
  'SQD4' => 4,
  'SQD5' => 5,
  'SQD6' => 4,
  'SQD7' => 5,
  'SQD8' => 4,
  'remarks' => 'Automated test submission.'
];

// Include your existing logic
include __DIR__ . '/../controllers/submit-form.php';