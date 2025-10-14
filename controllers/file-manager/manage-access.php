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

// 🔍 Check if file has direct access
$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_control WHERE file_id = ? AND is_revoked = FALSE");
$stmt->execute([$fileId]);
$hasDirectAccess = $stmt->fetchColumn() > 0;

// 🔁 If no direct access, check for parent
if (!$hasDirectAccess) {
    $stmt = $pdo->prepare("SELECT parent_id FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $parentId = $stmt->fetchColumn();

    if ($parentId) {
        error_log("⚠️ Attempted access update on inherited file_id: $fileId (parent: $parentId)");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Access is inherited from parent folder and cannot be modified here.']);
        exit;
    }
}

// ✅ Perform update
updateAccess($pdo, $fileId, $updates, $newShare);

http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Access updated successfully']);