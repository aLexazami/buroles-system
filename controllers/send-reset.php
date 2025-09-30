<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/email-helper.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);

  // Basic validation
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reset_message'] = 'Invalid email address.';
    header('Location: /pages/reset-password.php');
    exit;
  }

  // Check if email exists in users table
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if (!$user) {
    $_SESSION['reset_message'] = 'Email not found.';
    header('Location: /pages/reset-password.php');
    exit;
  }

  // Generate secure token
  $token = bin2hex(random_bytes(32));
  $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

  // Store token in password_resets table
  $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
  $stmt->execute([$user['id'], $token, $expires]);

  // Send email with reset link
  $resetLink = "https://yourdomain.com/pages/new-password.php?token=$token";
  $subject = "Password Reset Request";
  $message = "Click the link below to reset your password:\n\n$resetLink\n\nThis link expires in 1 hour.";

  // Use helper or native mail()
sendEmail($email, $subject, $message);

  $_SESSION['reset_message'] = 'Reset link sent! Please check your email.';
  header('Location: /pages/reset-password.php');
  exit;
}
?>