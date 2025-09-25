<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/notification-icon.php';


function sendNotification($title, $body, $link = '#', $userId = null, $roleId = null)
{
  global $pdo;

  if (!$userId && !$roleId) return false;

  $icon = getNotificationIcon($title, $body);

  try {
    $stmt = $pdo->prepare("
      INSERT INTO notifications (user_id, role_id, title, body, link, icon)
      VALUES (:userId, :roleId, :title, :body, :link, :icon)
    ");
    return $stmt->execute([
      'userId' => $userId,
      'roleId' => $roleId,
      'title' => $title,
      'body' => $body,
      'link' => $link,
      'icon' => $icon
    ]);
  } catch (PDOException $e) {
    error_log("Notification error: " . $e->getMessage());
    return false;
  }
}
