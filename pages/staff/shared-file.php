<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/head.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/sharing-utils.php';

$activeUserId = $_SESSION['user_id'] ?? 0;
$view         = $_GET['view'] ?? 'with'; // 'by' or 'with'
$sortBy       = $_GET['sort'] ?? 'name';

$validSorts   = ['name', 'modified'];
$orderColumn  = $sortBy === 'modified' ? 'sf.shared_at' : 'f.name';

// 🧠 Fetch shared items (root-level only)
$sharedFolders = fetchSharedItems($pdo, 'folder', $view, $activeUserId, $orderColumn, true);
$sharedFiles   = fetchSharedItems($pdo, 'file', $view, $activeUserId, $orderColumn, true);
$sharedItems   = array_merge($sharedFolders, $sharedFiles);

// 🧹 Normalize paths and names
foreach ($sharedItems as $i => $item) {
  if (isset($item['path'])) {
    $sharedItems[$i]['path'] = preg_replace('#^.*uploads/staff/\d+/#', '', $item['path']);
    $sharedItems[$i]['name'] = basename($sharedItems[$i]['path']);
  } else {
    $sharedItems[$i]['name'] = 'Unnamed';
  }
}

// 🧩 Build tree for rendering (optional)
$sharedTree = [];
foreach ($sharedItems as $item) {
  $sharedTree[$item['path']] = ['__meta' => $item, '__children' => []];
}

renderHead('Staff');
?>

<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>
  <?php showFlash(); ?>

  <main class="grid grid-cols-1 md:grid-cols-[248px_1fr] min-h-screen">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/archive-user.png" class="w-5 h-5 sm:w-6 sm:h-6" alt="Archive icon">
        <h1 class="font-bold text-md sm:text-lg">Shared File</h1>
      </div>

      <!-- View Toggle -->
      <div class="relative inline-block text-center">
        <button id="sharedBtn"
          class="group flex flex-col items-center justify-center px-3 py-2 focus:outline-none cursor-pointer transition duration-300 ease-in-out hover:bg-emerald-100 hover:scale-105 rounded-full">
          <img src="/assets/img/shared-icon.png" alt="Shared Icon" class="w-6 h-6 mb-1" />
          <span class="text-sm font-medium text-gray-700">Shared</span>
        </button>

        <div id="sharedDropdown" class="absolute left-16 -translate-x-1/2 w-32 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
          <a href="?view=by" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-100 font-semibold">By Me</a>
          <a href="?view=with" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-100 font-semibold">With Me</a>
        </div>
      </div>

      <div class="bg-white shadow-2xl rounded-md p-4 sm:p-6 min-h-screen">
        <!-- Search -->
        <div class="flex flex-wrap items-center gap-2 mb-4">
          <input type="text" id="folderSearch" placeholder="Search"
            class="border px-3 py-2 rounded w-full max-w-md text-sm" />
          <button id="clearFolderSearch"
            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm cursor-pointer">Clear</button>
        </div>

        <!-- Sorting -->
        <div class="flex gap-4 mb-4 text-sm text-gray-700">
          <span>Sort by:</span>
          <a href="?view=<?= $view ?>&sort=name" class="<?= $sortBy === 'name' ? 'font-bold underline' : 'hover:underline' ?>">Name</a>
          <a href="?view=<?= $view ?>&sort=modified" class="<?= $sortBy === 'modified' ? 'font-bold underline' : 'hover:underline' ?>">Date Shared</a>
        </div>

        <!-- Header Row -->
        <div class="flex flex-wrap px-2 py-2 items-center w-full text-sm text-gray-600 border-b">
          <div class="flex flex-wrap items-center w-full justify-between gap-2 sm:gap-0">
            <div class="flex items-center gap-2 sm:gap-3 flex-grow">
              <span class="font-medium">Name</span>
            </div>
            <div class="hidden sm:flex items-center text-center font-medium gap-2 sm:gap-3">
              <span class="w-24 text-xs sm:text-sm"><?= $view === 'by' ? 'Shared to' : 'Shared by' ?></span>
              <span class="w-32 text-xs sm:text-sm">Date shared</span>
            </div>
            <div class="w-10"></div>
          </div>
        </div>

        <!-- Shared Items -->
        <div class="flex flex-col divide-y divide-gray-300">
          <?php foreach ($sharedItems as $item): ?>
            <?php
            $isFolder     = ($item['type'] ?? '') === 'folder';
            $name         = $item['name'] ?? '';
            $path         = $item['path'] ?? '';
            $accessLevel  = $item['access_level'] ?? 'none';
            $ext          = $name !== '' ? strtolower(pathinfo($name, PATHINFO_EXTENSION)) : '';
            $icon         = $isFolder ? 'folder-icon.png' : match ($ext) {
              'jpg', 'jpeg', 'png', 'gif' => 'image-icon.png',
              'pdf' => 'pdf-icon.png',
              'doc', 'docx' => 'doc-icon.png',
              'xls', 'xlsx' => 'excel-icon.png',
              'zip', 'rar' => 'archive-icon.png',
              default => 'file-icon.png',
            };

            $userId = $item['shared_by'] ?? $activeUserId;
            $link   = $isFolder
              ? "/pages/staff/file-manager.php?shared=1&user_id={$userId}&path=" . urlencode($path)
              : getUserUploadUrl('1', $userId, dirname($path), $name);

            $email  = $view === 'by' ? $item['recipient_email'] ?? 'Unknown' : $item['shared_by_email'] ?? 'Unknown';
            $avatar = $view === 'by' ? $item['recipient_avatar'] ?? '/assets/img/default-avatar.png' : $item['shared_by_avatar'] ?? '/assets/img/default-avatar.png';
            ?>
            <div class="flex flex-wrap px-2 py-2 items-center w-full text-sm text-gray-700">
              <div class="flex items-center gap-2 sm:gap-3 flex-grow">
                <img src="/assets/img/<?= $icon ?>" class="w-4 h-4" alt="<?= $isFolder ? 'Folder' : ($ext !== '' ? strtoupper($ext) . ' file' : 'Unknown file') ?> icon" />
                <a href="<?= $link ?>" class="text-sm text-emerald-700 hover:underline"><?= htmlspecialchars($name ?: 'Unnamed') ?></a>
              </div>
              <div class="hidden sm:flex items-center text-center gap-2 sm:gap-3">
                <img src="<?= htmlspecialchars($avatar) ?>" class="w-5 h-5 rounded-full" alt="Avatar" />
                <span class="w-24 text-xs sm:text-sm"><?= htmlspecialchars($email) ?></span>
                <span class="w-32 text-xs sm:text-sm"><?= htmlspecialchars(date('Y-m-d', strtotime($item['shared_at'] ?? ''))) ?></span>
              </div>
              <div class="w-10 flex justify-end">
                <?php if ($accessLevel === 'owner'): ?>
                  <form method="POST" action="/controllers/files/revoke-share.php" class="inline">
                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                    <input type="hidden" name="type" value="<?= $item['type'] ?>">
                    <input type="hidden" name="shared_with" value="<?= $item['shared_with'] ?>">
                    <button type="submit" class="text-red-600 text-xs hover:underline">Revoke</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
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
      const btn = document.getElementById('sharedBtn');
      const dropdown = document.getElementById('sharedDropdown');

      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
      });

      document.addEventListener('click', () => {
        dropdown.classList.add('hidden');
      });
    });
  </script>
</body>

</html>