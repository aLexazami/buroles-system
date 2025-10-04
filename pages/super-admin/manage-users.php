<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/get-users.php';
require_once __DIR__ . '/../../helpers/head.php';

// Context for the table
$title = "User Management";
$showActions = true;
renderHead('Super Admin');
?>
<?php //include __DIR__ . '/../../includes/debug-panel.php'?>
<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[248px_1fr] min-h-screen">
    <?php include('../../includes/side-nav-super-admin.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/manage-user.png " class="w-5 h-5 sm:w-6 sm:h-6">
        <h1 class="font-bold text-lg ">User Management</h1>
      </div>

      <!-- Confirmation Modal -->
      <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0">
        <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
         <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
          <p id="confirm-modal-message" class="text-lg sm:text-2xl mb-10 mt-5 font-semibold text-center">
            Are you sure you want to proceed?
          </p>
          <div class="flex justify-between gap-4 px-8 mb-5">
            <button id="confirm-modal-yes"
              class="text-sm px-3 py-1 bg-emerald-700 cursor-pointer rounded text-white w-full  hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-green-300 transition">
              Yes
            </button>
            <button id="confirm-modal-no"
              class="text-sm px-3 py-1  bg-emerald-700 cursor-pointer rounded text-white w-full hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-red-300 transition">
              No
            </button>
          </div>
        </div>
      </div>

      <!-- Flash Messages -->
      <?php showFlash(); ?>

      <div class="px-6 py-10 bg-white rounded-lg shadow-md">
        <?php include(__DIR__ . '/../../components/user-table.php'); ?>
      </div>

      <!-- Super Admin Password Modal -->
      <div id="passwordModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0">
        <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
        <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
          <h2 class="text-xl sm:text-2xl font-semibold mb-8">Super Admin Verification</h2>
          <div class="relative">
            <input type="password" id="superAdminPasswordInput" class="w-full border px-3 py-2 rounded mb-4" placeholder="Enter your password">
            <img
            src="/assets/img/eye-open.png"
            alt="Toggle visibility"
            class="absolute right-3 top-3 w-5 h-5 cursor-pointer opacity-70 hover:opacity-100"
            data-toggle-password="superAdminPasswordInput"/>
          </div>
          <input type="hidden" id="targetUserId">
          <div class="flex justify-end gap-2">
            <button id="cancelSuperAdminPassword" class="text-sm px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 cursor-pointer">Cancel</button>
            <button id="submitSuperAdminPassword" class="text-sm px-3 py-1 bg-emerald-700 text-white rounded hover:bg-emerald-500 cursor-pointer">Verify</button>
          </div>
        </div>
      </div>

      <!-- Unlock User Modal -->
      <div id="unlockModal" class="hidden fixed inset-0 z-50  items-center justify-center">
        <div class="absolute inset-0 bg-black opacity-50"></div>
        <div class="bg-white p-6 rounded-4xl shadow-md w-full max-w-md z-10 relative border border-emerald-500 transition-opacity duration-200">
          <h2 class="text-2xl font-semibold mb-8">Unlock User Account</h2>
          <p class="mb-10 text-md text-gray-600 text-center">Do you also want to reset the password?</p>
          <input type="hidden" id="unlockUserId">
          <input type="hidden" id="managePasswordUrl">
          <div>
            <div class="flex justify-between  gap-4">
              <button id="unlockAndResetBtn" class="px-4 py-2 bg-emerald-700 text-white  rounded hover:bg-emerald-500 w-full cursor-pointer">Yes</button>
              <button id="justUnlockBtn" class="px-4 py-2 bg-emerald-700 text-white  rounded hover:bg-emerald-500 w-full cursor-pointer">No</button>
            </div>
            <div class="mt-5 flex justify-end">
              <button id="cancelUnlockModal" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 transition duration-150 cursor-pointer">Cancel</button>
            </div>
          </div>
        </div>
      </div>
      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>