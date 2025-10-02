<?php
$user = $_SESSION['user'] ?? null;
$activeRoleId = $_SESSION['active_role_id'] ?? null;
$isSuperAdmin = $user && (int)$user['role_id'] === 99 && (int)$activeRoleId === 99;
?>

<div class="bg-gray-300 h-full relative">
  <!-- 🟩 Header -->
  <h2 class="bg-emerald-600 py-3 text-white text-center font-bold text-lg tracking-wide">
    Announcements
  </h2>

  <!-- 🟢 Create Announcement Button (Super Admin only) -->
  <?php if ($isSuperAdmin): ?>
    <div class="absolute top-0 left-0 flex justify-end items-center gap-x-2 z-10">
      <button id="openAnnouncementModal"
        class="relative group flex items-center justify-center p-2 transition cursor-pointer hover:scale-110"
        aria-label="Create Announcement">
        <img src="/assets/img/post-icon.png" alt="Create Announcement" class="h-8 w-8" />
        <span class="absolute bottom-full mb-2 left-16 transform -translate-x-1/2 px-3 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none">
          Create Announcement
        </span>
      </button>
    </div>
  <?php endif; ?>

  <!-- 📦 Announcement Container -->
  <div id="announcement-container" class="p-4 space-y-4">
    <!-- Cards will be injected via JS -->
  </div>

  <!-- 🔁 Pagination Controls -->
  <div id="pagination-container" class="mt-6 flex justify-center items-center gap-2 px-4">
    <!-- Buttons injected via JS -->
  </div>
</div>

<!-- 📖 Viewer Modal -->
<?php require __DIR__ . '/../components/announcement-viewer.php'; ?>

<!-- 📣 Announcement Modal -->
<div id="announcementModal" class="fixed inset-0 z-50 hidden overflow-y-auto items-start justify-center px-4 sm:px-8 lg:px-12">

  <!-- Fullscreen overlay -->
  <div class="fixed inset-0 bg-black opacity-50 z-0"></div>

  <!-- Content wrapper (scrollable) -->
  <div class="relative w-full flex justify-center z-10">

    <!-- Content box -->
    <div class="bg-white border border-emerald-600 rounded shadow-lg w-full max-w-screen-lg relative my-8 sm:my-12">
      <h2 class="bg-emerald-600 py-3 text-lg sm:text-xl md:text-2xl font-bold text-white text-center tracking-wide flex items-center justify-center gap-2 sm:gap-4 px-4">
        Create Announcements
      </h2>

      <div class="p-4 sm:p-6 space-y-6 text-sm sm:text-base text-gray-800">
        <form method="POST" action="/controllers/create-announcement.php" class="space-y-6">
          <!-- Title Field -->
          <div class="relative">
            <input
              type="text"
              name="title"
              id="announcementTitle"
              required
              class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" />
            <label for="announcementTitle"
              class="absolute left-4 top-2 text-xs text-gray-500 font-semibold transition-all peer-focus:top-2 peer-focus:text-xs peer-focus:text-emerald-600 peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400">
              Title
            </label>
          </div>

          <!-- Body Field -->
          <div class="relative">
            <textarea
              name="body"
              id="announcementBody"
              required
              rows="10"
              class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md shadow-sm resize-none focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"></textarea>
            <label for="announcementBody"
              class="absolute left-4 top-2 text-xs text-gray-500 font-semibold transition-all peer-focus:top-2 peer-focus:text-xs peer-focus:text-emerald-600 peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400">
              Body
            </label>
          </div>

          <!-- Role Selector with Checkboxes -->
          <div class="relative space-y-2">
            <label class="font-semibold text-gray-500 text-xs block">Audience</label>
            <div class="flex flex-wrap gap-2 sm:gap-4">
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="role_ids[]" value="100" class="role-checkbox accent-emerald-600">
                <span>All</span>
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="role_ids[]" value="1" class="role-checkbox accent-emerald-600">
                <span>Staff</span>
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="role_ids[]" value="2" class="role-checkbox accent-emerald-600">
                <span>Admin</span>
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="role_ids[]" value="99" class="role-checkbox accent-emerald-600">
                <span>Super Admin</span>
              </label>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-wrap justify-end gap-2 pt-4">
            <button type="button" id="cancelAnnouncementModal"
              class="px-4 py-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition shadow-sm cursor-pointer">
              Cancel
            </button>
            <button type="submit"
              class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm cursor-pointer">
              Post Announcement
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- 🔐 JS Access to Super Admin Flag -->
<script>
  window.isSuperAdmin = <?= json_encode($isSuperAdmin) ?>;
</script>