<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$userId = $_SESSION['user']['id'] ?? null;

// Validate session
if (!$userId) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);
$announcementId = $input['announcement_id'] ?? null;

if (!$announcementId || !is_numeric($announcementId)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid announcement ID']);
  exit;
}

try {
  // Check if already marked as read
  $checkStmt = $pdo->prepare("SELECT 1 FROM announcement_reads WHERE user_id = ? AND announcement_id = ?");
  $checkStmt->execute([$userId, $announcementId]);

  if (!$checkStmt->fetchColumn()) {
    // Insert read record
    $insertStmt = $pdo->prepare("INSERT INTO announcement_reads (user_id, announcement_id, read_at) VALUES (?, ?, NOW())");
    $insertStmt->execute([$userId, $announcementId]);
  }

  echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
}