<?php
require_once __DIR__ . '/../config/database.php';

$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
  header("Location: /pages/super-admin/manage-users.php?error=invalid_id");
  exit;
}

$first_name = trim($_POST['first_name']);
$middle_name = trim($_POST['middle_name']);
$last_name = trim($_POST['last_name']);
$username = trim($_POST['username']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$role_id = $_POST['role_id'];
$is_archived = $_POST['is_archived'] ?? 0;

try {
  $stmt = $pdo->prepare("
    UPDATE users SET
      first_name = ?, middle_name = ?, last_name = ?,
      username = ?, email = ?, role_id = ?,
      is_archived = ?
    WHERE id = ?
  ");

  $stmt->execute([
    $first_name, $middle_name, $last_name,
    $username, $email, $role_id,
    $is_archived,
    $id
  ]);

  header("Location: /pages/super-admin/manage-users.php?updated=success&id=$id");
  exit;

} catch (PDOException $e) {
  error_log("Update failed: " . $e->getMessage());
  header("Location: /pages/super-admin/manage-users.php?error=update_failed");
  exit;
}
?>