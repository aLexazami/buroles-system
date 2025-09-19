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
$currentEmail = trim($_POST['current_email'] ?? '');
$newEmail     = trim($_POST['new_email'] ?? '');

// ✅ Validate both emails
if (!filter_var($currentEmail, FILTER_VALIDATE_EMAIL) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
  setFlash('error', 'Please enter valid email addresses.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Confirm current email matches session
if (strcasecmp($currentEmail, $_SESSION['email']) !== 0) {
  setFlash('error', 'Current email does not match our records.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Check if new email is already taken
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$newEmail, $userId]);
if ($stmt->fetch()) {
  setFlash('error', 'This email is already in use by another account.');
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
}

// ✅ Update email
$stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
if ($stmt->execute([$newEmail, $userId])) {
  $_SESSION['email'] = $newEmail;
  setFlash('success', 'Email updated successfully.');
} else {
  setFlash('error', 'Something went wrong while updating your email.');
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;