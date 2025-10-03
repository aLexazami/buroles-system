<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/password-utils.php';

if (!isset($_SESSION['user_id'])) {
  setFlash('error', 'Session expired. Please log in again.');
  header('Location: /index.php');
  exit;
}

$userId = $_SESSION['user_id'];
$currentPassword = trim($_POST['current_password'] ?? '');
$newPassword     = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

// ✅ Basic field check
if (!$currentPassword || !$newPassword || !$confirmPassword) {
  setFlash('error', 'All fields are required.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Validate new password
$passwordErrors = getPasswordErrors($newPassword);
if ($newPassword !== $confirmPassword) {
  setFlash('error', 'Passwords do not match.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
} elseif (!empty($passwordErrors)) {
  setFlash('error', 'Password must include: ' . formatPasswordErrors($passwordErrors) . '.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Verify current password
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user['password'])) {
  setFlash('error', 'Current password is incorrect.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Update password
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
if ($stmt->execute([$hashed, $userId])) {
  setFlash('success', '✅ Password updated successfully.');
} else {
  setFlash('error', 'Something went wrong while updating your password.');
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;