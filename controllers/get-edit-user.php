<?php
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
  header("Location: /pages/super-admin/manage-user.php?error=invalid_id");
  exit;
}

$stmt = $pdo->prepare("SELECT users.*, roles.name AS role_name FROM users JOIN roles ON users.role_id = roles.id WHERE users.id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
?>