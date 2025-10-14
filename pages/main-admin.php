<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/respondent-counts.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/text-format.php';
require_once __DIR__ . '/../helpers/head.php';

// ✅ Fetch today's feedback before rendering
$today = date('Y-m-d');
$stmt = $pdo->prepare("
  SELECT r.name, r.customer_type, s.name AS service, reg.name AS region, r.submitted_at, r.is_read
  FROM feedback_respondents r
  LEFT JOIN services s ON r.service_availed_id = s.id
  LEFT JOIN regions reg ON r.region_id = reg.id
  WHERE DATE(r.submitted_at) = ?
  ORDER BY r.submitted_at DESC
");
$stmt->execute([$today]);
$todayFeedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

renderHead('Admin');
?>

<body class="bg-gray-200 min-h-screen">

  <!-- Header -->
  <?php include '../includes/header.php' ?>

  <!-- Main Layout -->
  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr_300px] lg:grid-cols-[auto_1fr_300px]">

    <?php showFlash(); ?>

    <!-- Sidebar Left (flush to edge) -->
    <div class="order-1 lg:order-none h-full">
      <?php include '../includes/side-nav-admin.php' ?>
    </div>

    <!-- Center Content (padded and centered) -->
    <section class="order-2 lg:order-none lg:col-span-1 px-4 py-4 w-full">
      <div class="space-y-6 max-w-full bg-white h-full py-6 px-4 rounded shadow">
        <div class="space-y-4">
          <div class="bg-gray-300 flex justify-center items-center gap-2 py-2 rounded">
            <h1 class="font-bold text-lg">Respondents</h1>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Cards -->
            <div class="bg-white shadow-md rounded-lg p-4 text-center">
              <img src="/assets/img/new.png" class="mx-auto h-16 w-16 mb-2" alt="New Respondents">
              <p class="text-sm text-gray-500 uppercase">New</p>
              <p id="new-count" class="text-2xl font-bold"><?= $newCount ?></p>
            </div>
            <div class="bg-white shadow-md rounded-lg p-4 text-center">
              <img src="/assets/img/weekly.png" class="mx-auto h-16 w-16 mb-2" alt="Weekly Respondents">
              <p class="text-sm text-gray-500 uppercase">Weekly</p>
              <p id="weekly-count" class="text-2xl font-bold"><?= $weeklyCount ?></p>
            </div>
            <div class="bg-white shadow-md rounded-lg p-4 text-center">
              <img src="/assets/img/total.png" class="mx-auto h-16 w-16 mb-2" alt="Total Respondents">
              <p class="text-sm text-gray-500 uppercase">Total</p>
              <p id="annual-count" class="text-2xl font-bold"><?= $annualCount ?></p>
            </div>
          </div>
        </div>
        <!-- 🗂️ Daily Feedback Container -->
        <div class="space-y-4 mt-6">
          <!-- 🔖 Header -->
          <div class="bg-gray-300 flex justify-between items-center py-2 px-4 rounded">
            <h2 class="font-bold text-lg">Today's Feedback</h2>
            <div class="flex items-center gap-3">
              <!-- Count Badge -->
              <span class="bg-emerald-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                <?= count($todayFeedback) ?> entries
              </span>

              <!-- View All Button -->
              <a href="/pages/admin/feedback-respondents.php"
                class="bg-white border border-emerald-600 text-emerald-700 text-xs font-semibold px-3 py-1 rounded hover:bg-emerald-50 transition">
                View All
              </a>
            </div>
          </div>

          <!-- 📋 Feedback List -->
          <div class="bg-white rounded-lg min-h-[350px] shadow-md p-4 space-y-3 max-h-[400px] overflow-y-auto">
            <?php if (empty($todayFeedback)): ?>
              <!-- 🧩 Fallback Message -->
              <div class="flex flex-col items-center justify-center text-center text-gray-500 py-12">
                <img src="/assets/img/feedback-empty.png" alt="No feedback" class="w-24 h-24 mb-4 opacity-80" />
                <p class="text-lg font-semibold">No feedback submitted yet today</p>
                <p class="text-sm text-gray-400">Once responses come in, they’ll appear here automatically.</p>
              </div>
            <?php else: ?>
              <?php foreach ($todayFeedback as $entry):
                $name = htmlspecialchars($entry['name'] ?? 'Anonymous');
                $type = htmlspecialchars($entry['customer_type'] ?? 'Unknown');
                $service = htmlspecialchars($entry['service'] ?? 'Not Specified');
                $region = htmlspecialchars($entry['region'] ?? 'Unknown');
                $time = date('h:i A', strtotime($entry['submitted_at']));
                $isUnread = !($entry['is_read'] ?? true); // highlight if unread
              ?>
                <div class="border-b p-2 <?= $isUnread ? 'bg-emerald-50' : '' ?>">
                  <p class="font-semibold text-gray-700"><?= $name ?></p>
                  <p class="text-sm text-gray-500">Type: <?= $type ?> | Service: <?= $service ?> | Region: <?= $region ?></p>
                  <p class="text-xs text-gray-400">Submitted at <?= $time ?></p>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
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