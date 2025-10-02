<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../auth/session.php';

$userId = $_SESSION['user_id'] ?? null;
$announcementId = $_POST['announcement_id'] ?? null;

if ($userId && $announcementId) {
  $stmt = $pdo->prepare("INSERT IGNORE INTO announcement_reads (announcement_id, user_id) VALUES (?, ?)");
  $stmt->execute([$announcementId, $userId]);
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error']);
}