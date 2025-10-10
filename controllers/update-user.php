<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/flash.php';

$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
  setFlash('error', 'Invalid user ID.');
  header("Location: /pages/admin/manage-users.php");
  exit;
}

// Collect and sanitize input
$formData = [
  'id'          => $id,
  'first_name'  => trim($_POST['first_name'] ?? ''),
  'middle_name' => trim($_POST['middle_name'] ?? ''),
  'last_name'   => trim($_POST['last_name'] ?? ''),
  'username'    => trim($_POST['username'] ?? ''),
  'email'       => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
  'role_id'     => $_POST['role_id'] ?? '',
  'is_archived' => $_POST['is_archived'] ?? 0
];

// Validate input
$errors = [];

if (!$formData['first_name'])  $errors['first_name']  = 'First name is required.';
if (!$formData['last_name'])   $errors['last_name']   = 'Last name is required.';
if (!$formData['username'])    $errors['username']    = 'Username is required.';
if (!$formData['email'])       $errors['email']       = 'Email is required.';
if (!$formData['role_id'] || !is_numeric($formData['role_id'])) {
  $errors['role_id'] = 'Please select a valid role.';
}

// Validate email format
if ($formData['email'] && !preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $formData['email'])) {
  $errors['email'] = 'Email must be a valid @gmail.com address.';
}

if (!empty($errors)) {
  setFlashData('form_data', $formData);
  setFlashData('form_errors', $errors);
  setFlash('error', 'Please fix the highlighted fields.');
  header("Location: /pages/admin/edit-user.php?id=$id&formMode=edit");
  exit;
}

// Attempt update
try {
  $stmt = $pdo->prepare("
    UPDATE users SET
      first_name = ?, middle_name = ?, last_name = ?,
      username = ?, email = ?, role_id = ?,
      is_archived = ?
    WHERE id = ?
  ");

  $stmt->execute([
    $formData['first_name'],
    $formData['middle_name'],
    $formData['last_name'],
    $formData['username'],
    $formData['email'],
    $formData['role_id'],
    $formData['is_archived'],
    $formData['id']
  ]);

  setFlash('success', 'User updated successfully.');
  header("Location: /pages/admin/manage-users.php?updated=success&id={$formData['id']}");
  exit;

} catch (PDOException $e) {
  error_log("Update failed: " . $e->getMessage());
  setFlashData('form_data', $formData);
  setFlash('error', 'Something went wrong while updating the user.');
  header("Location: /pages/admin/edit-user.php?id={$formData['id']}&formMode=edit");
  exit;
}
?>