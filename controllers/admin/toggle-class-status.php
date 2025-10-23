<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Access denied']);
  exit;
}

$id = $_POST['id'] ?? null;
$status = $_POST['is_active'] ?? null;

if (!$id || !ctype_digit($id) || !in_array($status, ['0', '1'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit;
}

// Optional: enforce only one active class per adviser per school year
if ($status === '1') {
  // 🔍 Get school year and adviser for this class
  $stmt = $pdo->prepare("SELECT adviser_id, school_year_id FROM classes WHERE id = ?");
  $stmt->execute([$id]);
  $class = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($class) {
    $pdo->prepare("UPDATE classes SET is_active = 0 WHERE adviser_id = ? AND school_year_id = ?")->execute([
      $class['adviser_id'],
      $class['school_year_id']
    ]);
  }
}

// ✅ Update status
$stmt = $pdo->prepare("UPDATE classes SET is_active = ? WHERE id = ?");
$success = $stmt->execute([$status, $id]);

echo json_encode(['success' => $success]);