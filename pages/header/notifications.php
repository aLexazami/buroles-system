<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';

$userId = $_SESSION['user_id'] ?? null;
$roleId = $_SESSION['active_role_id'] ?? null;

// Fetch notifications including is_read
$stmt = $pdo->prepare("
  SELECT id, title, body, link, icon, created_at, is_read
  FROM notifications
  WHERE (user_id = :userId OR role_id = :roleId)
  ORDER BY created_at DESC
  LIMIT 20
");
$stmt->execute(['userId' => $userId, 'roleId' => $roleId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="/src/styles.css" rel="stylesheet" />
  <title>Notifications</title>
</head>

<body class="bg-gray-200 min-h-screen">
  <?php include __DIR__ . '/../../includes/header.php'; ?>

  <main class="grid grid-cols-[248px_1fr] min-h-screen">
    <?php
    switch ($_SESSION['active_role_id'] ?? null) {
      case 1:
        include __DIR__ . '/../../includes/side-nav-staff.php';
        break;
      case 2:
        include __DIR__ . '/../../includes/side-nav-admin.php';
        break;
      case 99:
        include __DIR__ . '/../../includes/side-nav-super-admin.php';
        break;
      default:
        echo "<p>Role not recognized.</p>";
    }
    ?>

    <section class="m-4">
      <?php showFlash(); ?>

      <div class="bg-emerald-700 text-white p-5">
        <h2 class="text-lg font-semibold">Notifications</h2>
      </div>
      <div class="bg-white p-6 rounded-b-lg shadow space-y-4">
        <?php
        if (empty($notifications)) {
          echo "<p class='text-sm text-gray-600'>No notifications available.</p>";
        } else {
          foreach ($notifications as $notif) {
            $title = htmlspecialchars($notif['title']);
            $body = htmlspecialchars($notif['body']);
            $link = $notif['link'] ?? '#';
            $timestamp = date('M d, Y h:i A', strtotime($notif['created_at']));
            $icon = htmlspecialchars($notif['icon'] ?? '/assets/img/info.png');
            $isUnread = !$notif['is_read'];
            $hoverClass = $isUnread ? 'hover:bg-gray-300' : 'hover:bg-gray-100';
            $bgClass = $isUnread ? 'bg-gray-200' : 'bg-white';

            echo "<a href='/actions/notification/mark-notification-as-read.php?id={$notif['id']}&redirect=" . urlencode($link) . "' class='flex items-start gap-4 p-4 {$bgClass} rounded shadow {$hoverClass} transition'>
              <div class='relative'>
                <img src='{$icon}' alt='icon' class='w-6 h-6 mt-1'>
                " . ($isUnread ? "<span class='absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full'></span>" : "") . "
              </div>
              <div>
                <p class='font-semibold text-emerald-800'>{$title}</p>
                <p class='text-sm text-gray-700'>{$body}</p>
                <p class='text-xs text-gray-500 mt-1'>{$timestamp}</p>
              </div>
            </a>";
          }
        }
        ?>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>