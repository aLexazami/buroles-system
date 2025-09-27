<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role_id'], [1, 2, 99])) {
  http_response_code(403);
  exit('Unauthorized access.');
}

// 🧼 Sanitize and validate input
$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');
$role_id = $_POST['role_id'] ?? null;

if ($title === '' || $body === '') {
  // You can redirect back with error or show a message
  exit('Title and body are required.');
}

// 🧠 Normalize role_id
$target_role = is_numeric($role_id) ? (int) $role_id : null;

try {
  $stmt = $pdo->prepare("
    INSERT INTO announcements (title, body, target_role_id, created_by, created_at)
    VALUES (:title, :body, :role_id, :created_by, NOW())
  ");

  $stmt->execute([
    ':title' => $title,
    ':body' => $body,
    ':role_id' => $target_role,
    ':created_by' => $_SESSION['user']['id']
  ]);

  // ✅ Redirect or respond
  header('Location: /pages/main-super-admin.php?announcement=success');
  exit;

} catch (PDOException $e) {
  // 🐞 Log error and fail gracefully
  error_log('Announcement creation failed: ' . $e->getMessage());
  http_response_code(500);
  exit('Server error. Please try again.');
}