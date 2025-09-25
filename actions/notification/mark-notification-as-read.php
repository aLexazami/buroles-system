<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

$userId = $_SESSION['user_id'] ?? null;
$roleId = $_SESSION['active_role_id'] ?? null;
$notifId = $_GET['id'] ?? null;

if ($notifId && ($userId || $roleId)) {
  $stmt = $pdo->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE id = :id AND (user_id = :userId OR role_id = :roleId)
  ");
  $stmt->execute(['id' => $notifId, 'userId' => $userId, 'roleId' => $roleId]);
}

$redirect = $_GET['redirect'] ?? '/pages/header/notifications.php';
header("Location: {$redirect}");
exit;