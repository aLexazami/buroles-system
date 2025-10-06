<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/sharing-utils.php';
require_once __DIR__ . '/../../helpers/file-utils.php';
require_once __DIR__ . '/../../helpers/head.php';

$activeUserId = $_SESSION['user_id'] ?? 0;
$view         = $_GET['view'] ?? 'with'; // 'by' or 'with'
$sortBy       = $_GET['sort'] ?? 'modified'; // 'name' or 'modified'
$sortOrder    = $_GET['sort_order'] ?? 'desc'; // 'asc' or 'desc'

// ✅ Fetch folders and files separately
$sharedFolders = fetchSharedItems($pdo, 'folder', $view, $activeUserId, 'sf.shared_at', true);
$sharedFiles   = fetchSharedItems($pdo, 'file',   $view, $activeUserId, 'sf.shared_at', true);

// ✅ Merge and normalize
$sharedItems = array_merge($sharedFolders, $sharedFiles);
$sharedItems = normalizeSharedItems($sharedItems);
$sharedItems = sortSharedItems($sharedItems, $sortBy, $sortOrder);

renderHead('Staff');
?>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>
  <?php showFlash(); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr] min-h-screen">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/archive-user.png" class="w-5 h-5 sm:w-6 sm:h-6" alt="Archive icon">
        <h1 class="font-bold text-md sm:text-lg">Shared File</h1>
      </div>

      <!-- View Toggle + Label -->
      <div class="flex items-center justify-start gap-4 mb-4">
        <div class="relative">
          <button id="sharedBtn"
            class="group flex flex-col items-center justify-center px-3 py-2 focus:outline-none cursor-pointer transition duration-300 ease-in-out hover:bg-emerald-100 hover:scale-105 rounded-full">
            <img src="/assets/img/shared-icon.png" alt="Shared Icon" class="w-6 h-6 mb-1" />
            <span class="text-sm font-medium text-gray-700">Shared</span>
          </button>

          <div id="sharedDropdown"
            class="absolute left-18 -translate-x-1/2 w-32 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
            <a href="?view=by" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-100 font-semibold">By Me</a>
            <a href="?view=with" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-100 font-semibold">With Me</a>
          </div>
        </div>

        <span class="inline-block bg-emerald-100 text-emerald-800 text-xs sm:text-sm font-semibold px-3 py-1 rounded-md shadow-sm">
          <?= $view === 'by' ? 'Shared by Me' : 'Shared with Me' ?>
        </span>
      </div>

      <div class="bg-white p-4 sm:p-6 rounded shadow min-h-screen">
        <!-- Search -->
        <div class="flex flex-wrap items-center gap-2 mb-4">
          <input type="text" id="folderSearch" placeholder="Search"
            class="border px-3 py-2 rounded w-full max-w-md text-sm" />
          <button id="clearFolderSearch"
            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm cursor-pointer">Clear</button>
        </div>
        <!-- Sorting -->
        <div class="flex gap-4 mb-4 text-sm text-gray-700">
          <?php
          $nextOrder = $sortOrder === 'asc' ? 'desc' : 'asc';
          ?>
          <span>Sort by:</span>
          <a href="?view=<?= $view ?>&sort=name&sort_order=<?= $nextOrder ?>">Name</a>
          <a href="?view=<?= $view ?>&sort=modified&sort_order=<?= $nextOrder ?>">Date Shared</a>
        </div>
        <!-- Header Row -->
        <div class="flex px-2 py-2 items-center w-full text-sm text-gray-600 border-y">
          <div class="flex items-center gap-2 sm:gap-3 flex-grow min-w-0">
            <span class="font-medium">Name</span>
          </div>
          <div class="hidden sm:flex items-center gap-2 sm:gap-3 w-[20rem] justify-between">
            <span class="w-[12rem] text-xs sm:text-sm"><?= $view === 'by' ? 'Shared to' : 'Shared by' ?></span>
            <span class="w-[8rem] text-xs sm:text-sm">Date shared</span>
          </div>
          <div class="w-10 text-xs sm:text-sm text-center font-medium"></div>
        </div>
        <!-- Shared Items -->
        <div class="flex flex-col divide-y divide-black-200">
          <?php foreach ($sharedItems as $item): ?>
            <?php
            $isFolder     = ($item['type'] ?? '') === 'folder';
            $name         = $item['name'] ?? '';
            $path         = $item['path'] ?? '';
            $accessLevel  = $item['access_level'] ?? 'none';
            $ext          = $name !== '' ? strtolower(pathinfo($name, PATHINFO_EXTENSION)) : '';
            $icon = $isFolder ? '/assets/img/folder.png' : getFileIcon($name);
            $userId = $item['shared_by'] ?? $activeUserId;
            $link   = $isFolder
              ? "/pages/staff/file-manager.php?shared=1&user_id={$userId}&path=" . urlencode($path)
              : getUserUploadUrl('1', $userId, dirname($path), $name);
            $email  = $view === 'by' ? $item['recipient_email'] ?? 'Unknown' : $item['shared_by_email'] ?? 'Unknown';
            $avatar = $view === 'by' ? $item['recipient_avatar'] ?? '/assets/img/default-avatar.png' : $item['shared_by_avatar'] ?? '/assets/img/default-avatar.png';
            ?>
            <div class="shared-row group flex px-2 py-2 items-center w-full text-sm text-gray-700 hover:bg-emerald-50 transition cursor-pointer"
              data-link="<?= $link ?>">
              <!-- Name + Icon -->
              <div class="flex items-center gap-2 sm:gap-3 flex-grow min-w-0">
                <img src="<?= htmlspecialchars($icon) ?>" class="w-4 h-4" alt="<?= $isFolder ? 'Folder' : strtoupper($ext) . ' file' ?>" />
                <span class="text-sm font-medium"><?= htmlspecialchars($name ?: 'Unnamed') ?></span>
              </div>
              <!-- Metadata -->
              <div class="hidden sm:flex items-center gap-2 sm:gap-3 w-[20rem] justify-between">
                <div class="flex items-center w-[12rem] min-w-0">
                  <img src="<?= htmlspecialchars($avatar) ?>" class="w-5 h-5 rounded-full shrink-0" alt="Avatar" />
                  <span
                    class="ml-2 text-xs sm:text-sm block max-w-full sm:max-w-[10rem] lg:max-w-none truncate overflow-hidden whitespace-nowrap"
                    title="<?= htmlspecialchars($email) ?>">
                    <?= htmlspecialchars($email) ?>
                  </span>
                </div>
                <span class="w-[8rem] text-xs sm:text-sm">
                  <?= htmlspecialchars(date('Y-m-d', strtotime($item['shared_at'] ?? ''))) ?>
                </span>
              </div>
              <!-- Action -->
              <div class="w-10 flex justify-end relative" onclick="event.stopPropagation();">
                <?php if ($view === 'by'): ?>
                  <div class="relative">
                    <button class="menu-toggle cursor-pointer hover:bg-emerald-300 rounded-full p-2" data-id="<?= htmlspecialchars($item['id']) ?>">
                      <img src="/assets/img/dots-icon.png" alt="Menu" class="w-5 h-5">
                    </button>
                    <div class="menu-dropdown absolute right-8 top-0  bg-white rounded shadow-lg hidden text-sm w-40 sm:w-44 transition ease-out duration-150 font-semibold z-10">
                      <button class="revoke-btn flex items-center gap-3 px-4 py-2 rounded hover:bg-emerald-100 text-sm text-left w-full cursor-pointer"
                        data-id="<?= htmlspecialchars($item['id']) ?>"
                        data-type="<?= htmlspecialchars($item['type']) ?>"
                        data-name="<?= htmlspecialchars($item['name']) ?>"
                        data-shared-with="<?= htmlspecialchars($item['shared_with']) ?>">
                        <img src="/assets/img/revoke-icon.png" alt="Revoke Icon" class="w-4 h-4" />
                        Revoke Access
                      </button>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Revoke Confirmation Modal -->
      <div id="revokeModal" role="dialog" aria-labelledby="revokeModalLabel" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0">
        <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
          <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
            <h2 class="text-xl sm:text-2xl mb-4">Confirm Revocation</h2>
            <p class="text-sm text-gray-700 mb-4">
              Are you sure you want to revoke access to <span id="revokeItemName" class="font-bold text-emerald-600"></span>?
            </p>
            <form method="POST" action="/controllers/files/revoke-share.php">
              <input type="hidden" name="item_id" id="revokeItemId">
              <input type="hidden" name="type" id="revokeItemType">
              <input type="hidden" name="shared_with" id="revokeSharedWith">
              <div class="flex justify-end gap-2  mt-5">
                <button type="button" id="cancelRevoke" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
                <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Confirm</button>
              </div>
            </form>
          </div>
      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const sharedBtn = document.getElementById('sharedBtn');
      const sharedDropdown = document.getElementById('sharedDropdown');

      // Toggle shared view dropdown
      sharedBtn.addEventListener('click', e => {
        e.stopPropagation();
        sharedDropdown.classList.toggle('hidden');
      });

      // Row click handler (excluding action buttons)
      document.querySelectorAll('.shared-row').forEach(row => {
        row.addEventListener('click', e => {
          const isActionClick = e.target.closest('.menu-toggle') || e.target.closest('.menu-dropdown');
          if (!isActionClick) {
            const link = row.dataset.link;
            if (link) window.location.href = link;
          }
        });
      });
    });
  </script>
</body>

</html>