<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/access-utils.php'; // updateAccess()

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 📥 Parse JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// 🧠 Debug logging
error_log("📥 Raw input: $rawInput");
error_log("🔐 Session user_id: " . ($_SESSION['user_id'] ?? 'null'));

// ✅ Extract values
$fileId = $input['file_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;
$updates = $input['updates'] ?? [];
$newShare = $input['new_share'] ?? null;

// ❌ Validate essentials
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Missing session user ID']);
    exit;
}

if (!$fileId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing file ID']);
    exit;
}

// ✅ Log intent
error_log("🔧 Access update requested by user_id: $userId for file_id: $fileId");
error_log("🔄 Updates: " . json_encode($updates));
error_log("➕ New share: " . json_encode($newShare));

// ✅ Perform update
updateAccess($pdo, $fileId, $updates, $newShare);

http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Access updated successfully']);