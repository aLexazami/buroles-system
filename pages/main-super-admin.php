<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/text-format.php';
require_once __DIR__ . '/../helpers/head.php';
renderHead('Super Admin');

// Example stats (replace with real queries if needed)
$totalUsers = 128;
$unreadMessages = 5;
$pendingRequests = 3;
?>

<?php //include __DIR__ . '/../includes/debug-panel.php' ?>

<body class="bg-gray-200 min-h-screen">

  <!-- Header -->
  <?php include '../includes/header.php' ?>

  <!-- Main Layout -->
  <main class="grid grid-cols-1 md:grid-cols-[64px_1fr_300px] lg:grid-cols-[248px_1fr_300px]">

    <?php showFlash(); ?>

    <!-- Sidebar Left -->
    <div class="order-1 lg:order-none h-full">
      <?php include '../includes/side-nav-super-admin.php' ?>
    </div>

    <!-- Center Content -->
    <section class="order-2 lg:order-none lg:col-span-1 px-4 py-4">
      <div class="space-y-6 max-w-full bg-white h-full py-6 px-4 rounded shadow">

        <!-- 1. Role Summary Card -->
        <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded shadow text-left">
          <h2 class="text-lg font-semibold text-emerald-700">Your Role: Super Admin</h2>
          <p class="text-sm text-gray-700 mt-1">
            You have full access to system-wide settings, user management, and announcement controls. Use this dashboard to monitor activity, resolve issues, and guide platform integrity.
          </p>
        </div>

        <!-- 2. Quick Stats Overview -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-xl font-bold text-emerald-700"><?= $totalUsers ?></p>
            <p class="text-xs text-gray-500">Total Users</p>
          </div>
          <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-xl font-bold text-emerald-700"><?= $unreadMessages ?></p>
            <p class="text-xs text-gray-500">Unread Messages</p>
          </div>
          <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-xl font-bold text-emerald-700"><?= $pendingRequests ?></p>
            <p class="text-xs text-gray-500">Pending Requests</p>
          </div>
        </div>

        <!-- 4. Welcome Message -->
        <div class="text-center mt-6">
          <h1 class="text-2xl sm:text-3xl font-bold text-emerald-700">Welcome, Super Admin!</h1>
          <p class="text-gray-700 text-sm sm:text-base leading-relaxed mt-2">
            You oversee platform-wide operations. Use the sidebar to navigate between administrative tools, or check the latest announcements on the right.
          </p>
        </div>

        <!-- 5. Motivational Quote or Tip -->
        <div class="mt-6 text-center text-sm italic text-gray-500">
          “Leadership is not about being in charge. It’s about taking care of those in your charge.” — Simon Sinek
        </div>

      </div>
    </section>

    <!-- Announcement Card Right -->
    <aside class="order-3 lg:order-none h-full">
      <?php include __DIR__ . '/dashboard/announcement-card.php'; ?>
    </aside>

  </main>

  <!-- Footer -->
  <?php include '../includes/footer.php' ?>

  <!-- Scripts -->
  <script src="../assets/js/update-dashboard.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="../assets/js/date-time.js"></script>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
</body>

</html>