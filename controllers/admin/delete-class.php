<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

// 🔐 Ensure only admins can delete classes
if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Access denied']);
  exit;
}

// 🧾 Validate POST input
$classId = $_POST['id'] ?? null;
if (!$classId || !ctype_digit($classId)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Invalid class ID']);
  exit;
}

// 🗑️ Delete class
try {
  $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
  $stmt->execute([$classId]);

  if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'error' => 'Class not found or already deleted']);
  }
} catch (PDOException $e) {
  error_log("Delete class error: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Server error']);
}