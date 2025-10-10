<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/role-checker.php';

if (!hasRoleSlug('admin') && !hasRoleSlug('admin')) {
    header('Location: /unauthorized.php');
    exit;
}

$pdo = Database::connect();

$userId = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
$newPassword = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

if (!is_numeric($userId)) {
    setFlash('error', 'Invalid user ID.');
    header("Location: /pages/admin/manage-password.php");
    exit;
}

if (!$userId || !$newPassword || !$confirmPassword) {
    setFlash('error', 'Missing fields.');
    header("Location: /pages/admin/manage-password.php?id=" . urlencode($userId));
    exit;
}

if ($newPassword !== $confirmPassword) {
    setFlash('error', 'Passwords do not match.');
    header("Location: /pages/admin/manage-password.php?id=" . urlencode($userId));
    exit;
}

$hashed = password_hash($newPassword, PASSWORD_DEFAULT);
$adminId = $_SESSION['user_id'];
$timestamp = date('Y-m-d H:i:s');
$unlockUser = isset($_POST['unlock_user']) && $_POST['unlock_user'] == '1';

if ($unlockUser) {
  $stmt = $pdo->prepare("
    UPDATE users
    SET password = ?, must_change_password = 1, is_locked = 0, updated_by = ?, updated_at = ?
    WHERE id = ?
  ");
  $stmt->execute([$hashed, $adminId, $timestamp, $userId]);
} else {
  $stmt = $pdo->prepare("
    UPDATE users
    SET password = ?, must_change_password = 1, updated_by = ?, updated_at = ?
    WHERE id = ?
  ");
  $stmt->execute([$hashed, $adminId, $timestamp, $userId]);
}

if ($stmt->rowCount() === 0) {
    setFlash('error', 'No changes made. User may not exist.');
    header("Location: /pages/admin/manage-password.php?id=" . urlencode($userId));
    exit;
}

$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$reason = htmlspecialchars(trim($_POST['reason'] ?? 'Manual password reset by admin'));

$auditStmt = $pdo->prepare("
    INSERT INTO user_password_audit (user_id, updated_by, ip_address, reason)
    VALUES (?, ?, ?, ?)
");
$auditStmt->execute([$userId, $adminId, $ipAddress, $reason]);

setFlash('success', 'Password updated successfully.');
header("Location: /pages/admin/manage-users.php");
exit;