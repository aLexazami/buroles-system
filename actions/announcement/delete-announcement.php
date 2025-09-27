<?php
// 🔐 Session and DB setup
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';

// 🛡️ Authorization: Only Super Admin (role_id 99)
if (empty($_SESSION['user']) || (int) $_SESSION['user']['role_id'] !== 99) {
  setFlash('error', 'Unauthorized access.');
  header('Location: /pages/main-super-admin.php');
  exit;
}

// 🧼 Validate and sanitize input
$announcementId = $_POST['announcement_id'] ?? null;
if (!is_numeric($announcementId)) {
  setFlash('error', 'Invalid announcement ID.');
  header('Location: /pages/main-super-admin.php');
  exit;
}

try {
  // 🗑️ Delete announcement
  $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
  $stmt->execute([':id' => (int) $announcementId]);

  // ✅ Flash success and redirect
  setFlash('success', 'Announcement deleted successfully.');
  header('Location: /pages/main-super-admin.php');
  exit;

} catch (PDOException $e) {
  // 🐞 Log error and fail gracefully
  error_log('Announcement deletion failed: ' . $e->getMessage());
  setFlash('error', 'Server error. Please try again.');
  header('Location: /pages/main-super-admin.php');
  exit;
}