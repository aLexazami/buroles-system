<!-- 📖 Dynamic Announcement Viewer -->
<div id="announcement-viewer" class="fixed inset-0 z-50 hidden overflow-y-auto items-start justify-center px-4 sm:px-6">

  <!-- Fullscreen overlay -->
  <div class="fixed inset-0 bg-black opacity-50 z-0"></div>

  <!-- Content wrapper (scrollable) -->
  <div class="relative w-full flex justify-center z-10">

    <!-- Content box -->
    <div class="bg-white border border-emerald-600 rounded shadow-lg w-full max-w-2xl sm:max-w-3xl md:max-w-4xl relative my-8 sm:my-12">

      <!-- Header -->
      <div class="bg-emerald-600 py-3 text-lg sm:text-xl md:text-2xl font-bold text-white text-center tracking-wide flex items-center justify-center gap-2 sm:gap-4 px-4">
        <img src="/assets/img/announcement.png" alt="Announcements" class="h-8 w-8 sm:h-10 sm:w-10">
        Announcements
      </div>

      <!-- Content -->
      <article class="p-4 sm:p-6 space-y-6 text-sm sm:text-base text-gray-800">

        <!-- Meta Info -->
        <p id="viewerMeta" class="text-xs text-gray-500 italic text-center"></p>

        <!-- Title -->
        <h2 id="viewerTitle" class="text-lg sm:text-xl md:text-2xl text-center font-bold uppercase break-words overflow-hidden"></h2>

        <!-- Body -->
        <div class="rounded-md p-3 sm:p-4 bg-gray-50">
          <p id="viewerBody" class="text-sm sm:text-base text-gray-700 leading-relaxed whitespace-pre-line"></p>
        </div>
      </article>

      <!-- Close Button -->
      <div class="mt-4 sm:mt-5 flex justify-center mb-4 sm:mb-5 px-4">
        <button id="closeAnnouncementViewer"
          class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition duration-150 cursor-pointer">
          Close
        </button>
      </div>
    </div>
  </div>
</div>