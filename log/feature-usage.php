<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);

// Basic validation
if (!isset($data['action'])) {
  http_response_code(400);
  exit('Missing action');
}

$logEntry = json_encode([
  'timestamp' => date('c'),
  'user_id' => $_SESSION['user_id'] ?? null,
  'action' => $data['action'],
  'details' => $data['details'] ?? null
]);

file_put_contents(__DIR__ . '/feature-logs.log', $logEntry . PHP_EOL, FILE_APPEND);

http_response_code(200);
echo 'Logged';