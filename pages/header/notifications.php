<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/head.php';

$userId = $_SESSION['user_id'] ?? null;
$roleId = $_SESSION['original_role_id'] ?? null;

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
renderHead('Notifications');
?>
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
      <div class="bg-white p-2 rounded-b-lg shadow space-y-4 min-h-screen">
        <?php if (empty($notifications)): ?>
          <div class="flex flex-col items-center justify-center py-10 text-gray-500 space-y-4">
            <img src="/assets/img/empty-notify.png" alt="No notifications" class="w-16 h-16 opacity-50">
            <p class="text-sm italic">You're all caught up. No notifications at the moment.</p>
          </div>
        <?php else: ?>

          <form method="POST" action="/actions/notification/delete-selected.php">

            <!-- Action Nav -->
            <div class="flex flex-wrap items-center px-2 py-2 border-b gap-4">
              <!-- Selection Tools -->
              <div class="flex items-center bg-gray-300 p-2 rounded space-x-4">
                <!-- Select All Icon with Tooltip -->
                <div class="relative group">
                  <input type="checkbox" id="select-all" class="accent-emerald-600 w-4 h-4 cursor-pointer" />
                  <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                    Select All Notifications
                  </div>
                </div>
                <!-- Filter Dropdown Icon with Tooltip -->
                <div class="relative group">
                  <button type="button" id="filter-toggle" class="p-1 rounded hover:bg-emerald-100 transition cursor-pointer">
                    <img src="/assets/img/dropdown-icon.png" alt="Filter" class="w-3 h-3">
                  </button>
                  <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                    Filter Notifications
                  </div>
                  <!-- Dropdown Menu -->
                  <div id="filter-dropdown" class="absolute top-full left-0 mt-2 w-50 bg-white rounded shadow-lg z-10 hidden">
                    <button type="button" data-filter="all" class="block pl-15 w-full font-semibold cursor-pointer text-left px-3 py-2 text-sm hover:bg-emerald-100">Select All</button>
                    <button type="button" data-filter="unread" class="block pl-15 w-full font-semibold cursor-pointer text-left px-3 py-2 text-sm hover:bg-emerald-100">Select Unread</button>
                    <button type="button" data-filter="read" class="block pl-15 w-full font-semibold cursor-pointer text-left px-3 py-2 text-sm hover:bg-emerald-100">Select Read</button>
                  </div>
                </div>
              </div>
              <!-- Action Buttons -->
              <div class="flex items-center space-x-2">
                <!-- Delete Icon with Tooltip -->
                <div class="relative group">
                  <button type="submit" class="p-2 rounded hover:bg-emerald-100 transition cursor-pointer">
                    <img src="/assets/img/delete-icon.png" alt="Delete Selected" class="w-4 h-4" />
                  </button>
                  <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                    Delete Selected
                  </div>
                </div>
                <!-- Mark as Read Icon with Tooltip -->
                <div class="relative group">
                  <button type="submit" formaction="/actions/notification/mark-selected-read.php" class="p-2 rounded hover:bg-emerald-100 transition cursor-pointer">
                    <img src="/assets/img/read-icon.png" alt="Mark as Read" class="w-4 h-4" />
                  </button>
                  <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                    Mark as Read
                  </div>
                </div>
              </div>
            </div>

            <!-- Notification List -->
            <div class="space-y-4 py-4 px-2">
              <?php foreach ($notifications as $notif): ?>
                <?php
                $title = htmlspecialchars($notif['title']);
                $body = htmlspecialchars($notif['body']);
                $link = $notif['link'] ?? '#';
                $timestamp = date('M d, Y h:i A', strtotime($notif['created_at']));
                $icon = htmlspecialchars($notif['icon'] ?? '/assets/img/info.png');
                $isUnread = !$notif['is_read'];
                $hoverClass = $isUnread ? 'hover:bg-gray-300' : 'hover:bg-gray-100';
                $bgClass = $isUnread ? 'bg-gray-200' : 'bg-white';
                ?>
                <div class="notification-item flex items-start  px-2 py-2 space-x-4 <?= $bgClass ?> rounded shadow <?= $hoverClass ?> transition"
                  data-unread="<?= $isUnread ? 'true' : 'false' ?>">
                  <input type="checkbox" name="selected_ids[]" value="<?= $notif['id'] ?>" class=" accent-emerald-600 w-4 h-4 cursor-pointer mt-2">
                  <a href="/actions/notification/mark-notification-as-read.php?id=<?= $notif['id'] ?>&redirect=<?= urlencode($link) ?>" class="flex items-start gap-4 w-full">
                    <div class="relative">
                      <img src="<?= $icon ?>" alt="icon" class="w-6 h-6 mt-1">
                      <?php if ($isUnread): ?>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                      <?php endif; ?>
                    </div>
                    <div>
                      <p class="font-semibold text-emerald-800"><?= $title ?></p>
                      <p class="text-sm text-gray-700"><?= $body ?></p>
                      <p class="text-xs text-gray-500 mt-1"><?= $timestamp ?></p>
                    </div>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/../../includes/footer.php'; ?>

  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script src="/assets/js/date-time.js"></script>

</body>

</html>