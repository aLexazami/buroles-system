<?php
ob_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/access-utils.php'; // getAccessListForItem()

header('Content-Type: application/json');

// 📥 Parse input
$fileId = $_GET['file_id'] ?? null;

if (!$fileId) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Missing file ID']);
  exit;
}

// 🔐 Optional: log session info for debugging
$userId = $_SESSION['user_id'] ?? null;
error_log("👤 Access list requested by user_id: " . ($userId ?? 'guest') . " for file_id: $fileId");

try {
  // 🔁 Check if file has direct access
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM access_control WHERE file_id = ? AND is_revoked = FALSE");
  $stmt->execute([$fileId]);
  $hasDirectAccess = $stmt->fetchColumn() > 0;

  // 🔁 If no direct access, fallback to parent
  if (!$hasDirectAccess) {
    $stmt = $pdo->prepare("SELECT parent_id FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $parentId = $stmt->fetchColumn();
    if ($parentId) {
      $fileId = $parentId;
      error_log("🔁 Access inherited from parent_id: $fileId");
    }
  }

  // ✅ Fetch and return access list
  $accessList = getAccessListForItem($pdo, $fileId);
  http_response_code(200);
  echo json_encode($accessList);
} catch (Exception $e) {
  error_log("❌ Access list fetch failed: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}