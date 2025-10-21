<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';

// 🔐 Role check
if ($_SESSION['role_slug'] !== 'admin') {
  http_response_code(403);
  exit('Access denied');
}

// 🧑‍🏫 Fetch active staff users (role_id = 1)
$staffUsers = $pdo->query("
  SELECT users.id, users.first_name, users.middle_name, users.last_name, users.email, users.avatar_path
  FROM users
  WHERE users.role_id = 1
    AND users.is_archived = 0
    AND users.is_locked = 0
  ORDER BY users.last_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

renderHead('Admin');
?>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-admin.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/class-advisory.png" class="w-5 h-5 sm:w-6 sm:h-6">
        <h1 class="font-bold text-base sm:text-lg md:text-xl">Class Advisory Management</h1>
      </div>

      <!-- Staff Adviser Table -->
      <div class="overflow-auto">
        <table class="min-w-full table-auto bg-white rounded shadow overflow-hidden">
          <thead class="bg-emerald-600 text-white text-left text-xs sm:text-sm">
            <tr>
              <th class="py-2 px-4">Name</th>
              <th class="py-2 px-4">Email</th>
              <th class="py-2 px-4">Actions</th>
            </tr>
          </thead>
          <tbody class="text-sm text-gray-800 text-left">
            <?php if (count($staffUsers) > 0): ?>
              <?php foreach ($staffUsers as $staff): ?>
                <tr class="hover:bg-emerald-100">
                  <td class="py-2 px-4">
                    <div class="flex items-center gap-3">
                      <img src="<?= $staff['avatar_path'] ?? '/assets/img/default-avatar.png' ?>" class="w-8 h-8 rounded-full object-cover" alt="Avatar">
                      <span class="inline-block align-middle leading-tight">
                        <?= htmlspecialchars($staff['last_name'] . ', ' . $staff['first_name'] . ' ' . $staff['middle_name']) ?>
                      </span>
                    </div>
                  </td>
                  <td class="py-2 px-4"><?= htmlspecialchars($staff['email']) ?></td>
                  <td class="py-2 px-4">
                    <div class="relative">
                      <a href="/pages/admin/manage-advisory.php?user_id=<?= $staff['id'] ?>" class="peer rounded-full p-2 hover:bg-blue-100 hover:scale-110 transition-transform duration-200 cursor-pointer inline-block">
                        <img src="/assets/img/manage-advisory-icon.png" alt="Manage Advisory" class="w-8 h-8" />
                      </a>
                      <div class="absolute bottom-full mb-1 left-7 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                        Manage Advisory
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="3" class="py-6 px-4 text-center text-gray-500">No active staff advisers found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <?php
  include('../../includes/footer.php');
  include('../../includes/modals.php');
  ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>