<?php
  require_once __DIR__ . '/../config/database.php';
  require_once __DIR__ . '/../auth/session.php';
  require_once __DIR__ . '/../helpers/flash.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize and collect form inputs
  $first_name   = trim($_POST['first_name']);
  $middle_name  = trim($_POST['middle_name'] ?? '');
  $last_name    = trim($_POST['last_name']);
  $username     = trim($_POST['username']);
  $email        = trim($_POST['email']);
  $raw_password = $_POST['password'];
  $role_id      = $_POST['role_id'] ?? null;

  // Validate required fields
  if (!$first_name || !$last_name || !$username || !$email || !$raw_password || !$role_id || !is_numeric($role_id)) {
    setFlash('error', 'Please fill in all required fields correctly.');
    header("Location: /pages/super-admin/create-account.php");
    exit;
  }

  // Hash the password securely
  $password = password_hash($raw_password, PASSWORD_DEFAULT);

  // Check for duplicate email or username
  $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
  $check_stmt->execute([$email, $username]);

  if ($check_stmt->fetchColumn() > 0) {
    setFlash('error', 'Email or username already exists.');
    header("Location: /pages/super-admin/create-account.php");
    exit;
  }

  // Insert new user into users table
  try {
    $insert_stmt = $pdo->prepare("
      INSERT INTO users (
        first_name, middle_name, last_name,
        username, password, email, role_id,
        must_change_password, is_archived
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insert_stmt->execute([
      $first_name,
      $middle_name,
      $last_name,
      $username,
      $password,
      $email,
      $role_id,
      1, // must_change_password
      0  // is_archived
    ]);

    setFlash('success', 'Account created successfully.');
  } catch (PDOException $e) {
    error_log("User creation failed: " . $e->getMessage());
    setFlash('error', 'Something went wrong while creating the account.');
  }

  header("Location: /pages/super-admin/create-account.php");
  exit;
}
?>