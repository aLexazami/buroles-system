<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['message'])) {
  http_response_code(400);
  exit('Missing message');
}

$logDir = __DIR__;
$logFile = $logDir . '/client-errors.log';

// ✅ Create folder if missing
if (!is_dir($logDir)) {
  mkdir($logDir, 0755, true);
}

$logEntry = json_encode([
  'timestamp' => date('c'),
  'user_id' => $_SESSION['user_id'] ?? null,
  'message' => $data['message'],
  'error' => $data['error'] ?? null,
  'userAgent' => $data['userAgent'] ?? null
]);

file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND);

http_response_code(200);
echo 'Logged';