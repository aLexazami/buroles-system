<!-- 📖 Dynamic Announcement Viewer -->
<div id="announcementViewer" class="fixed inset-0 z-50 hidden overflow-y-auto items-start justify-center ">

  <!-- Fullscreen overlay -->
  <div class="fixed inset-0 bg-black opacity-50 z-0"></div>

  <!-- Content wrapper (scrollable) -->
  <div class="relative w-full flex justify-center z-10">

    <!-- Content box -->
    <div class="bg-white border border-emerald-600 rounded shadow-lg w-full max-w-4xl relative my-12">
      <div class="bg-emerald-600 py-3 text-2xl font-bold text-white text-center tracking-wide flex items-center justify-center gap-4">
        <img src="/assets/img/announcement.png" alt="Announcements" class="h-10 w-10">Announcements
      </div>

      <article class="p-6 space-y-6 text-sm text-gray-800">
        <!-- Meta Info -->
        <p id="viewerMeta" class="text-xs text-gray-500 italic"></p>
        <h2 id="viewerTitle" class="text-2xl text-center font-bold uppercase"></h2>

        <!-- Body -->
        <div class="rounded-md p-4 bg-gray-50">
          <p id="viewerBody" class="text-base text-gray-700 leading-relaxed whitespace-pre-line"></p>
        </div>
      </article>

      <div class="mt-5 flex justify-center mb-5">
        <button id="closeAnnouncementViewer" class="px-3 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition duration-150 cursor-pointer">Close</button>
      </div>
    </div>
  </div>
</div>