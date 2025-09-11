<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/folder-utils.php';

define('UPLOAD_BASE', __DIR__ . '/../../uploads/staff/');

$userId = $_SESSION['user_id'];
$currentPath = isset($_GET['path']) ? trim($_GET['path'], '/') : '';
$sortBy = $_GET['sort'] ?? 'name'; // 'name' or 'modified'

$userRoot = UPLOAD_BASE . $userId . '/';
$fullPath = $userRoot . ($currentPath ? $currentPath . '/' : '');

if (!file_exists($userRoot)) {
  mkdir($userRoot, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder_name'])) {
  createFolder($fullPath, $_POST['folder_name']);
  header("Location: file-manager.php?path=" . urlencode($currentPath));
  exit;
}

$contents = listFolderItems($fullPath);
$folders = $contents['folders'];
$files = $contents['files'];

// Sort folders
usort($folders, function ($a, $b) use ($fullPath, $sortBy) {
  if ($sortBy === 'modified') {
    return filemtime($fullPath . $b) <=> filemtime($fullPath . $a);
  }
  return strcasecmp($a, $b);
});

// Sort files
usort($files, function ($a, $b) use ($fullPath, $sortBy) {
  if ($sortBy === 'modified') {
    return filemtime($fullPath . $b) <=> filemtime($fullPath . $a);
  }
  return strcasecmp($a, $b);
});

function getFileIcon($filename)
{
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  return match ($ext) {
    'pdf' => '/assets/img/icons/pdf.png',
    'doc', 'docx' => '/assets/img/icons/doc.png',
    'jpg', 'jpeg', 'png', 'gif' => '/assets/img/icons/image.png',
    'zip', 'rar' => '/assets/img/icons/zip.png',
    default => '/assets/img/icons/file.png',
  };
}

function countFilesInFolder($folderPath)
{
  if (!is_dir($folderPath)) return 0;
  $items = scandir($folderPath);
  $files = array_filter(
    $items,
    fn($item) =>
    is_file($folderPath . '/' . $item) && $item !== '.' && $item !== '..'
  );
  return count($files);
}

function getFolderModifiedTime($folderPath)
{
  if (!is_dir($folderPath)) return null;
  return date("M d, Y H:i", filemtime($folderPath));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Manage Files</title>
  <meta name="robots" content="noindex" />
  <link href="/src/styles.css" rel="stylesheet" />
</head>

<body data-current-path="<?= htmlspecialchars($currentPath) ?>" class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-[248px_1fr] min-h-screen">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <section class="m-4">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/archive-user.png" class="w-5 h-5" alt="Archive icon">
        <h1 class="font-bold text-lg">Manage File</h1>
      </div>

      <?php showFlash(); ?>

      <div class="flex flex-col gap-4">
        <!-- Folder Creation + Upload -->
        <div class="flex gap-2 py-4">
          <form method="POST" class="flex gap-2">
            <input type="text" name="folder_name" placeholder="New Folder" class="border px-2 py-1 rounded" required>
            <button type="submit" class="bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-700">+ Create Folder</button>
          </form>

          <?php if (!empty($currentPath)): ?>
            <form action="/controllers/upload-file.php" method="POST" enctype="multipart/form-data" class="flex gap-2">
              <input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
              <input type="file" name="file" required class="border px-2 py-1 rounded" accept=".pdf,.doc,.docx,.jpg,.png">
              <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Upload File</button>
            </form>
          <?php endif; ?>
        </div>


        <div class="bg-white shadow-2xl rounded-md p-4">
          <!-- Breadcrumb -->
          <div class="text-sm text-gray-500 flex items-center pb-3 gap-2">
            <a href="?path=" class="text-emerald-600 hover:underline">Home</a>
            <?php
            $segments = explode('/', $currentPath);
            $breadcrumbPath = '';
            foreach ($segments as $index => $segment):
              if ($segment === '') continue;
              $breadcrumbPath .= ($index > 0 ? '/' : '') . $segment;
            ?>
              <span>/</span>
              <a href="?path=<?= urlencode($breadcrumbPath) ?>" class="text-emerald-600 hover:underline">
                <?= htmlspecialchars($segment) ?>
              </a>
            <?php endforeach; ?>
          </div>

          <!-- Search -->
          <div class="flex items-center gap-2 mb-4">
            <input
              type="text"
              id="folderSearch"
              placeholder="Search"
              class="border px-3 py-2 rounded w-full max-w-md" />
            <button
              id="clearFolderSearch"
              class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm">
              Clear
            </button>
          </div>

          <!-- Unified Folder + File List -->
          <div class="flex flex-col divide-y divide-black-200" id="itemList">
            <!-- Sorting Controls -->
            <div class="flex items-center gap-4 text-sm text-gray-700 pb-5">
              <span>Sort by:</span>
              <a href="?path=<?= urlencode($currentPath) ?>&sort=name" class="hover:underline <?= $sortBy === 'name' ? 'font-bold' : '' ?>">Name</a>
              <a href="?path=<?= urlencode($currentPath) ?>&sort=modified" class="hover:underline <?= $sortBy === 'modified' ? 'font-bold' : '' ?>">Modified</a>
            </div>

            <!-- Folder Items -->
            <?php foreach ($folders as $folder): ?>
              <?php
              $nextPath = trim($currentPath . '/' . $folder, '/');
              $folderPath = $fullPath . $folder;
              $fileCount = countFilesInFolder($folderPath);
              $modified = getFolderModifiedTime($folderPath);
              $menuId = 'menu-' . md5($folder);
              ?>
              <div class="flex gap-2 item folder-item hover:bg-emerald-50 px-2  py-2 ">
                <a href="?path=<?= urlencode($nextPath) ?>" class="cursor-default flex justify-between items-center w-full">
                  <div class="flex items-center gap-3">
                    <img src="/assets/img/folder.png" alt="Folder" class="w-5 h-5">
                    <span class="text-sm font-medium"><?= htmlspecialchars($folder) ?></span>
                  </div>
                  <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span class="bg-gray-200 px-2 py-1 rounded"><?= $fileCount ?> file<?= $fileCount !== 1 ? 's' : '' ?></span>
                    <span><?= $modified ?></span>
                  </div>
                </a>
                <div class="flex items-center gap-2">
                  <button class="cursor-pointer menu-toggle hover:bg-emerald-300 rounded-full p-2 transition duration-200 ease-in-out" data-target="<?= $menuId ?>">⋯</button>
                  <div id="<?= $menuId ?>" class="absolute right-17 bg-white  rounded shadow-lg hidden text-sm w-44">
                    <button class="block px-4 py-2 hover:bg-emerald-100 w-full text-left rename-btn" data-name="<?= htmlspecialchars($folder) ?>" data-type="folder">Rename</button>
                    <button class="block px-4 py-2 hover:bg-emerald-100 w-full text-left delete-btn" data-name="<?= htmlspecialchars($folder) ?>" data-type="folder">Delete</button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

            <!-- File Items -->
            <?php if (!empty($currentPath)): ?>
              <?php foreach ($files as $file): ?>
                <?php
                $icon = getFileIcon($file);
                $fileUrl = "/uploads/staff/$userId/" . ($currentPath ? $currentPath . '/' : '') . $file;
                $isImage = preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
                $modified = date("M d, Y H:i", filemtime($fullPath . $file));
                $menuId = 'menu-' . md5($file);
                ?>
                <div class="flex item file-item hover:bg-emerald-50 px-2 gap-2 py-2 ">
                  <!-- Clickable row -->
                  <a href="<?= $fileUrl ?>" target="_blank" class="font-medium flex justify-between items-center w-full cursor-default">
                    <div class="flex items-center gap-3">
                      <img src="<?= $icon ?>" alt="File icon" class="w-5 h-5">
                      <span class="text-sm"><?= htmlspecialchars($file) ?></span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-500">
                      <span><?= $modified ?></span>
                    </div>
                  </a>

                  <!-- Menu dot and dropdown -->
                  <div class="flex items-center">
                    <button class="cursor-pointer menu-toggle hover:bg-emerald-300 rounded-full p-2 transition duration-200 ease-in-out" data-target="<?= $menuId ?>">⋯</button>
                    <div id="<?= $menuId ?>" class="absolute right-17 bg-white  rounded shadow-lg hidden text-sm w-44">
                      <?php if ($isImage): ?>
                        <a href="<?= $fileUrl ?>" target="_blank" class="block px-4 py-2 hover:bg-emerald-100 w-full text-left">Preview</a>
                      <?php endif; ?>
                      <a href="<?= $fileUrl ?>" download class="block px-4 py-2 hover:bg-emerald-100 w-full text-left">Download</a>
                      <button class="block px-4 py-2 hover:bg-emerald-100 w-full text-left rename-btn" data-name="<?= htmlspecialchars($file) ?>" data-type="file">Rename</button>
                      <button class="block px-4 py-2 hover:bg-emerald-100 w-full text-left delete-btn" data-name="<?= htmlspecialchars($file) ?>" data-type="file">Delete</button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if (empty($files)): ?>
                <p class="text-gray-500 text-sm py-5">No files found.</p>
              <?php endif; ?>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </section>
    
    <!-- Rename Modal -->
    <div id="renameModal" role="dialog" aria-labelledby="renameTypeLabel"
      class="fixed inset-0  bg-opacity-50 z-50  items-center justify-center rename-modal hidden ">
      <div class="bg-white p-6 rounded shadow-md w-full max-w-sm">
        <h2 class="text-lg font-semibold mb-4">
          Rename <span id="renameTypeLabel"></span>
        </h2>
        <form id="renameForm" method="POST" action="/controllers/rename-item.php" class="flex flex-col gap-3">
          <input type="hidden" name="type" id="renameType">
          <input type="hidden" name="old_name" id="renameOldName">
          <input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
          <input type="text" name="new_name" id="renameNewName" class="border px-3 py-2 rounded" required>
          <small id="renameExtensionHint" class="text-xs text-gray-500 hidden"></small>
          <div class="flex justify-end gap-2">
            <button type="button" onclick="closeRenameModal()" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
            <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Rename</button>
          </div>
        </form>
      </div>
    </div>
    
  </main>

  <?php include('../../includes/footer.php'); ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>