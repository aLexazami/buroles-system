<?php
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/text-format.php';
require_once __DIR__ . '/../helpers/head.php';
renderHead('Staff');
?>
<?php //include __DIR__ . '/../includes/debug-panel.php' 
?>

<body class="bg-gray-200 min-h-screen">

  <!-- Header -->
  <?php include '../includes/header.php' ?>

  <!-- Main Layout -->
  <main class="grid grid-cols-1 md:grid-cols-[64px_1fr_300px] lg:grid-cols-[248px_1fr_300px]">

    <?php showFlash(); ?>

    <!-- Sidebar Left (flush to edge) -->
    <div class="order-1 lg:order-none h-full">
      <?php include '../includes/side-nav-staff.php' ?>
    </div>

    <!-- Center Content (padded and centered) -->
    <section class="order-2 lg:order-none lg:col-span-1 px-4 py-4 w-full">
      <div class="space-y-6 max-w-full bg-white h-full py-6 px-4 rounded shadow">

        <!-- Role Summary -->
        <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded shadow text-left">
          <h2 class="text-lg font-semibold text-emerald-700">Your Role: Staff</h2>
          <p class="text-sm text-gray-700 mt-1">
            As a Staff member, you help manage announcements, assist with classroom coordination, and support day-to-day operations. Your contributions ensure smooth communication and timely updates across the school.
          </p>
        </div>

        <!-- Welcome Message -->
        <div class="text-center">
          <h1 class="text-2xl sm:text-3xl font-bold text-emerald-700">Welcome, Staff!</h1>
          <p class="text-gray-700 text-sm sm:text-base leading-relaxed mt-2">
            Use the sidebar to access your tools, view announcements, and stay connected with your team.
          </p>
        </div>

        <!-- Optional Quick Stats (if available) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6">
          <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-xl font-bold text-emerald-700"><?= $assignedClasses ?? '—' ?></p>
            <p class="text-xs text-gray-500">Assigned Classes</p>
          </div>
          <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-xl font-bold text-emerald-700"><?= $unreadAnnouncements ?? '—' ?></p>
            <p class="text-xs text-gray-500">Unread Announcements</p>
          </div>
          <div class="bg-white p-4 rounded shadow text-center">
            <p class="text-xl font-bold text-emerald-700"><?= $upcomingEvents ?? '—' ?></p>
            <p class="text-xs text-gray-500">Upcoming Events</p>
          </div>
        </div>

        <!-- Motivational Quote or Tip -->
        <div class="mt-6 text-center text-sm italic text-gray-500">
          “Great teachers empathize with children, respect them, and believe that each one has something special that can be built upon.” — Ann Lieberman
        </div>

      </div>
    </section>

    <!-- Announcement Card Right (flush to edge) -->
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