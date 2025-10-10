<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /pages/admin/create-account.php");
  exit;
}

// Collect and sanitize input
$formData = [
  'first_name'   => trim($_POST['first_name'] ?? ''),
  'middle_name'  => trim($_POST['middle_name'] ?? ''),
  'last_name'    => trim($_POST['last_name'] ?? ''),
  'username'     => trim($_POST['username'] ?? ''),
  'email'        => trim($_POST['email'] ?? ''),
  'password'     => $_POST['password'] ?? '',
  'role_id'      => $_POST['role_id'] ?? null
];


// Validate fields
$errors = [];

if (!$formData['first_name']) $errors['first_name'] = 'First name is required.';
if (!$formData['last_name'])  $errors['last_name']  = 'Last name is required.';
if (!$formData['username'])   $errors['username']   = 'Username is required.';
if (!$formData['email'])      $errors['email']      = 'Email is required.';
if (!$formData['password'])   $errors['password']   = 'Password is required.';
if (!$formData['role_id'] || !is_numeric($formData['role_id'])) {
  $errors['role_id'] = 'Please select a valid role.';
}

// Validate email format
if ($formData['email'] && !preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $formData['email'])) {
  $errors['email'] = 'Email must be a valid @gmail.com address.';
}

// Preserve input for repopulating form
if (!empty($errors)) {
  setFlashData('form_data', $formData);
  setFlashData('form_errors', $errors);
  setFlash('error', 'Please fix the highlighted fields.');
  header("Location: /pages/admin/create-account.php");
  exit;
}

// Check for duplicate email or username
$check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
$check_stmt->execute([$formData['email'], $formData['username']]);

if ($check_stmt->fetchColumn() > 0) {
  setFlashData('form_data', $formData);
  setFlashData('form_errors', ['email' => 'Email or username already exists.']);
  setFlash('error', 'Email or username already exists.');
  header("Location: /pages/admin/create-account.php");
  exit;
}

// Hash password
$hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);

// Insert user
try {
  $insert_stmt = $pdo->prepare("
    INSERT INTO users (
      first_name, middle_name, last_name,
      username, password, email, role_id,
      must_change_password, is_archived, failed_attempts, is_locked
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");

  $insert_stmt->execute([
    $formData['first_name'],
    $formData['middle_name'],
    $formData['last_name'],
    $formData['username'],
    $hashedPassword,
    $formData['email'],
    $formData['role_id'],
    1, // must_change_password
    0, // is_archived
    0, // failed_attempts
    0  // is_locked
  ]);

  setFlash('success', 'Account created successfully.');
} catch (PDOException $e) {
  error_log("User creation failed: " . $e->getMessage());
  setFlash('error', 'Something went wrong while creating the account.');
}

header("Location: /pages/admin/create-account.php");
exit;
?>