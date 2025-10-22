<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
  echo json_encode([
    'success' => false,
    'error' => 'Invalid or missing student ID.'
  ]);
  exit;
}

try {
  // 🔍 Confirm student exists before deleting
  $stmt = $pdo->prepare("SELECT id FROM students WHERE id = ?");
  $stmt->execute([$id]);
  $student = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$student) {
    echo json_encode([
      'success' => false,
      'error' => 'Student not found or already deleted.'
    ]);
    exit;
  }

  // 🗑️ Delete student record
  $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);

  // 🧹 Delete related records (if not ON DELETE CASCADE)
  $pdo->prepare("DELETE FROM guardians WHERE student_id = ?")->execute([$id]);
  $pdo->prepare("DELETE FROM enrollments WHERE student_id = ?")->execute([$id]);

  echo json_encode(['success' => true]);

} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'error' => 'Deletion failed. Please try again.'
  ]);
}