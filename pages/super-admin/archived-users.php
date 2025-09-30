<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/get-archived-users.php';
require_once __DIR__ . '/../../helpers/head.php';

// Context for the table
$title = "Archived Users";
$showActions = true;
renderHead('Super Admin');
?>
<?php //include __DIR__ . '/../../includes/debug-panel.php'?>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[248px_1fr] min-h-screen">
    <?php include('../../includes/side-nav-super-admin.php'); ?>

    <section class="m-4">
      <!-- Page Header -->
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/archive-user.png" class="w-5 h-5 sm:w-6 sm:h-6" alt="Archive icon">
        <h1 class="font-bold text-lg">Archived</h1>
      </div>

      <!-- Confirmation Modal -->
      <div id="confirm-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
        <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
        <div class="relative bg-white p-6  rounded-4xl shadow-md w-full max-w-md z-10 border border-emerald-500">
          <p id="confirm-modal-message" class="text-lg mb-10 mt-5 font-semibold text-center">
            Are you sure you want to proceed?
          </p>
          <div class="flex justify-between gap-4 px-8 mb-5">
            <button id="confirm-modal-yes"
              class="px-3 py-1 bg-emerald-700 cursor-pointer rounded text-white w-full  hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-green-300 transition">
              Yes
            </button>
            <button id="confirm-modal-no"
              class="px-3 py-1  bg-emerald-700 cursor-pointer rounded text-white w-full hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-red-300 transition">
              No
            </button>
          </div>
        </div>
      </div>

      <!-- Flash Message -->
      <?php showFlash(); ?>

      <!-- User Table -->
      <div class="p-6 bg-white rounded-lg shadow-md ">
        <?php include(__DIR__ . '/../../components/user-table.php'); ?>
      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>