<div class="bg-gray-300 h-full">
  <h2 class="bg-emerald-600 py-2 text-white text-center font-bold">Announcements</h2>
  <div id="announcement-container" class="p-2 space-y-2">
    <!-- Announcements will be loaded via AJAX -->
  </div>
  <div id="pagination-container" class="mt-4"></div>
</div>

<!-- 📖 Dynamic Announcement Viewer -->
<?php require __DIR__ . '/../components/announcement-viewer.php'; ?>