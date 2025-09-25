<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';

$selectedIds = $_POST['selected_ids'] ?? [];

if (!empty($selectedIds)) {
  $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
  $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders)");
  $stmt->execute($selectedIds);

  $count = count($selectedIds);
  if ($count === 1) {
    setFlash('success', 'Notification marked as read.');
  } else {
    setFlash('success', "$count Notifications marked as read.");
  }
} else {
  setFlash('warning', 'No notifications selected to mark as read.');
}

header('Location: /pages/header/notifications.php');
exit;