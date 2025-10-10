<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/text-format.php';

// 🔐 Authorization check
$user = $_SESSION['user'] ?? null;
if (!$user || !in_array($user['role_id'], [1, 2, 99])) {
  http_response_code(403);
  exit('Unauthorized access.');
}

// 🧼 Input sanitization
$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');
$roleIds = $_POST['role_ids'] ?? [];
$validRoles = [1, 2, 100];
$filteredRoles = array_filter($roleIds, fn($r) => in_array((int)$r, $validRoles));

if ($title === '' || $body === '' || empty($filteredRoles)) {
  setFlash('error', 'Title, body, and at least one audience role are required.');
  header('Location: /pages/main-admin.php');
  exit;
}

// 🎯 Role-based dashboard links
$roleLinkMap = [
  1 => '/pages/main-staff.php',
  2 => '/pages/main-admin.php',
];

try {
  if (in_array("100", $filteredRoles)) {
    // 📣 Insert one announcement for "All"
    $stmt = $pdo->prepare("
      INSERT INTO announcements (title, body, target_role_id, created_by, created_at)
      VALUES (:title, :body, 100, :created_by, NOW())
    ");
    $stmt->execute([
      ':title' => $title,
      ':body' => $body,
      ':created_by' => $user['id']
    ]);

    // 🔔 Notify all users individually
    $users = $pdo->query("SELECT id, role_id FROM users")->fetchAll(PDO::FETCH_ASSOC);
    $notif = $pdo->prepare("
      INSERT INTO notifications (title, body, link, icon, user_id, role_id, created_at)
      VALUES (:title, :body, :link, :icon, :user_id, NULL, NOW())
    ");

    foreach ($users as $u) {
      $link = $roleLinkMap[$u['role_id']] ?? '/dashboard';
      $notif->execute([
        ':title' => 'New Announcement Posted',
        ':body' => mb_strimwidth(sentenceCase($body), 0, 140, '...'),
        ':link' => $link,
        ':icon' => '/assets/img/announcement-icon.png',
        ':user_id' => $u['id']
      ]);
    }

  } else {
    // 📣 Insert per-role announcements and notifications
    $announcementStmt = $pdo->prepare("
      INSERT INTO announcements (title, body, target_role_id, created_by, created_at)
      VALUES (:title, :body, :role_id, :created_by, NOW())
    ");

    $notifStmt = $pdo->prepare("
      INSERT INTO notifications (title, body, link, icon, user_id, role_id, created_at)
      VALUES (:title, :body, :link, :icon, NULL, :role_id, NOW())
    ");

    foreach ($filteredRoles as $roleId) {
      $announcementStmt->execute([
        ':title' => $title,
        ':body' => $body,
        ':role_id' => $roleId,
        ':created_by' => $user['id']
      ]);

      $link = $roleLinkMap[(int)$roleId] ?? '/dashboard';
      $notifStmt->execute([
        ':title' => 'New Announcement Posted',
        ':body' => mb_strimwidth($body, 0, 140, '...'),
        ':link' => $link,
        ':icon' => '/assets/img/announcement-icon.png',
        ':role_id' => $roleId
      ]);
    }
  }

  setFlash('success', 'Announcement posted successfully.');
  header('Location: /pages/main-admin.php');
  exit;

} catch (PDOException $e) {
  error_log('Announcement creation failed: ' . $e->getMessage());
  setFlash('error', 'Server error. Please try again.');
  header('Location: /pages/main-admin.php');
  exit;
}