<?php
include __DIR__ . '/dashboard-data.php';
?>
<!-- 📣 Announcement Carousel -->
<div class="rounded-b-2xl col-span-2 relative overflow-hidden min-h-[400px]">
  <h2 class="bg-emerald-600 py-3 text-2xl font-bold text-white text-center tracking-wide flex items-center justify-center gap-4">
    <img src="/assets/img/announcement.png" alt="Announcements" class="h-10 w-10">Announcements
  </h2>

  <div class=" relative p-8 rounded-b-2xl shadow-lg border border-emerald-600 min-h-[455px] bg-white">
    <?php if (!empty($_SESSION['user']) && (int) $_SESSION['user']['role_id'] === 99): ?>
      <!-- Create Announcement Icon -->
      <div class="absolute bottom-4 left-4 flex justify-end items-center gap-x-2 z-10">
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
    <?php if (!empty($announcements)): ?>
      <div id="announcement-carousel" class="relative">
        <?php foreach ($announcements as $index => $note): ?>
          <div class="announcement-slide <?= $index === 0 ? '' : 'hidden' ?>">
            <div class="p-8 rounded-2xl  min-h-[350px] flex flex-col justify-between bg-emerald-50">
              <div>
                <?php
                $isNew = strtotime($note['created_at']) >= strtotime('-1 days');
                ?>
                <?php if ($isNew): ?>
                  <span class="ml-2 px-2 py-1 text-xs w-fit font-medium bg-green-600 text-white rounded-full">New</span>
                <?php endif; ?>
                <div class="flex flex-col items-center justify-center gap-2 mb-6 text-center">
                  <h3 class="text-2xl font-bold text-gray-800">
                    <?= ucwords(htmlspecialchars($note['title'])) ?>
                  </h3>
                  <!-- 🏷️ Audience Type -->
                  <p class="text-xs font-semibold bg-emerald-700 p-1 text-white">
                    <?php
                    $roleMap = [
                      '1' => 'For Staff',
                      '2' => 'For Admin',
                      '99' => 'For Super Admin',
                      '' => 'For All',
                      null => 'For All'
                    ];
                    echo $roleMap[$note['target_role_id']] ?? 'For All';
                    ?>
                  </p>
                  <!-- 📅 Date -->
                  <p class="text-xs text-gray-500 italic">
                    Posted on <?= date('F j, Y', strtotime($note['created_at'])) ?>
                  </p>
                </div>
                <p
                  class="text-base text-center leading-relaxed whitespace-pre-line cursor-pointer text-emerald-900 hover:text-emerald-700 transition"
                  data-viewer-trigger
                  data-id="<?= $note['id'] ?>"
                  data-title="<?= htmlspecialchars($note['title']) ?>"
                  data-body="<?= htmlspecialchars(sentenceCase($note['body'])) ?>"
                  data-role="<?= $roleMap[$note['target_role_id']] ?? 'For All' ?>"
                  data-date="<?= date('F j, Y', strtotime($note['created_at'])) ?>">
                  <?= mb_strimwidth(sentenceCase($note['body']), 0, 180, '...') ?>
                </p>
              </div>
              <?php if (!empty($_SESSION['user']) && (int) $_SESSION['user']['role_id'] === 99): ?>
                <div class="mt-6 flex justify-end items-center gap-x-2">
                  <!-- Delete Announcement Icon -->
                  <div class="relative group">
                    <form method="POST" action="/actions/announcement/delete-announcement.php">
                      <input type="hidden" name="announcement_id" value="<?= $note['id'] ?>">
                      <button type="submit"
                        class="rounded-full p-2 hover:bg-red-100 hover:scale-110 transition-transform duration-200 cursor-pointer">
                        <img src="/assets/img/delete-icon.png" alt="Delete Announcement" class="w-5 h-5" />
                      </button>
                    </form>
                    <div
                      class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-red-700 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none">
                      Delete Announcement
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Navigation Arrows -->
      <div class="absolute top-1/2 left-1 transform -translate-y-1/2 z-10">
        <button id="prev-announcement" class=" hover:bg-green-400 px-3 py-3 rounded-full  transition cursor-pointer flex items-center justify-center" aria-label="Previous announcement">
          <img src="/assets/img/arrow-left.png" alt="Previous" class="h-6 w-6">
        </button>
      </div>
      <div class="absolute top-1/2 right-1 transform -translate-y-1/2 z-10">
        <button id="next-announcement" class=" hover:bg-green-400 px-3 py-3 rounded-full  transition cursor-pointer flex items-center justify-center" aria-label="Next announcement">
          <img src="/assets/img/arrow-right.png" alt="Next" class="h-6 w-6">
        </button>
      </div>

      <!-- Dot Indicators -->
      <div class="mt-6 overflow-hidden max-w-[300px] mx-auto flex justify-center">
        <div id="dot-track" class="flex space-x-2 transition-transform duration-300 ">
          <?php foreach ($announcements as $dotIndex => $_): ?>
            <button class="dot h-3 w-3 rounded-full bg-gray-300 opacity-50 transition cursor-pointer" data-index="<?= $dotIndex ?>" aria-label="Slide <?= $dotIndex + 1 ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>

    <?php else: ?>
      <div class="flex flex-col items-center justify-center py-20 text-gray-500 space-y-4">
        <img src="/assets/img/no-announcement-icon.png" alt="No announcements" class="h-20 w-20 opacity-50">
        <p class="text-lg italic">No announcements available.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- 📣 Announcement Modal -->
<div id="announcementModal" class="fixed inset-0 z-50 hidden overflow-y-auto items-start justify-center">

  <!-- Fullscreen overlay -->
  <div class="fixed inset-0 bg-black opacity-50 z-0"></div>

  <!-- Content wrapper (scrollable) -->
  <div class="relative w-full flex justify-center z-10">

    <!-- Content box -->
    <div class="bg-white border border-emerald-600 rounded shadow-lg w-full max-w-4xl relative my-12">
      <h2 class="bg-emerald-600 py-3 text-2xl font-bold text-white text-center tracking-wide flex items-center justify-center gap-4">
        Create Announcements
      </h2>

      <div class="p-6 space-y-6 text-sm text-gray-800">
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
              rows="4"
              placeholder="Body"
              class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md shadow-sm resize-none focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
  </textarea>
            <label for="announcementBody"
              class="absolute left-4 top-2 text-xs text-gray-500 font-semibold transition-all peer-focus:top-2 peer-focus:text-xs peer-focus:text-emerald-600 peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400">
              Body
            </label>
          </div>

          <!-- Role Selector -->
          <div class="relative">
            <select name="role_id" id="announcementRole"
              class="peer w-full px-4 pt-7 pb-2 border border-gray-300 rounded-md shadow-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
              <option value="100">All</option>
              <option value="1">Staff</option>
              <option value="2">Admin</option>
              <option value="99">Super Admin</option>
            </select>
            <label for="announcementRole"
              class="absolute font-semibold left-4 top-2 text-gray-500 text-xs transition-all peer-focus:text-emerald-600">
              Audience
            </label>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-2 pt-4">
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

<!-- 📖 Dynamic Announcement Viewer -->
<?php require __DIR__ . '/../components/announcement-viewer.php'; ?>