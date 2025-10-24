<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/storage-utils.php'; // getStorageStats(),getBarColor(),getBoxHighlight()

$userId = $_SESSION['user_id'];
$stats = getStorageStats($pdo, $userId);

echo json_encode([
  'success' => true,
  'usedDisplay' => $stats['used_display'],
  'limitDisplay' => $stats['limit_display'],
  'percentUsed' => $stats['percent_used'],
  'barColor' => getBarColor($stats['percent_used']),
  'boxHighlight' => getBoxHighlight($stats['percent_used']),
]);