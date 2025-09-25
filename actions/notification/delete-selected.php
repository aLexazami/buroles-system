<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';

$selectedIds = $_POST['selected_ids'] ?? [];

if (!empty($selectedIds)) {
  $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
  $stmt = $pdo->prepare("DELETE FROM notifications WHERE id IN ($placeholders)");
  $stmt->execute($selectedIds);

  $count = count($selectedIds);
  if ($count === 1) {
    setFlash('success', 'Notification successfully deleted.');
  } else {
    setFlash('success', "$count Notifications successfully deleted.");
  }
} else {
  setFlash('warning', 'No notifications selected for deletion.');
}

header('Location: /pages/header/notifications.php');
exit;