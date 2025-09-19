<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';

if (!isset($_SESSION['user_id'])) {
  setFlash('error', 'Session expired. Please log in again.');
  header('Location: /index.php');
  exit;
}

$userId = $_SESSION['user_id'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// ✅ Basic validation
if (!$currentPassword || !$newPassword || !$confirmPassword) {
  setFlash('error', 'All fields are required.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

if ($newPassword !== $confirmPassword) {
  setFlash('error', 'New password and confirmation do not match.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

if (strlen($newPassword) < 8) {
  setFlash('error', 'Password must be at least 8 characters.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Fetch current password hash
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user['password'])) {
  setFlash('error', 'Current password is incorrect.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Update password
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
if ($stmt->execute([$newHash, $userId])) {
  setFlash('success', '✅ Password updated successfully.');
} else {
  setFlash('error', 'Something went wrong while updating your password.');
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;