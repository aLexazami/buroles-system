<?php
session_start();
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/folder-utils.php';

$userId = $_SESSION['user_id'] ?? '';
$currentPath = trim($_GET['path'] ?? '', '/');
$sortBy = $_GET['sort'] ?? 'name'; // 'name' or 'modified'

// ✅ Ensure user root folder exists
$userRoot = getUserUploadBase($userId);
if (!file_exists($userRoot)) {
  mkdir($userRoot, 0755, true);
}

// ✅ Resolve full folder path
$fullPath = resolveFolderPath($userId, $currentPath);

// ✅ Handle folder creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder_name'])) {
  createFolder($userRoot, $currentPath . '/' . $_POST['folder_name']);
  header("Location: file-manager.php?path=" . urlencode($currentPath));
  exit;
}

// ✅ Get folder contents
$contents = listFolderItems($fullPath);
$folders = $contents['folders'];
$files = $contents['files'];

// ✅ Sort folders
usort($folders, function ($a, $b) use ($sortBy) {
  return $sortBy === 'modified'
    ? strtotime($b['modified']) <=> strtotime($a['modified'])
    : strcasecmp($a['name'], $b['name']);
});

// ✅ Sort files
usort($files, function ($a, $b) use ($sortBy) {
  return $sortBy === 'modified'
    ? strtotime($b['modified']) <=> strtotime($a['modified'])
    : strcasecmp($a['name'], $b['name']);
});

/**
 * Get icon filename for a given file extension.
 */
function getFileIcon(string $filename): string
{
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  $iconMap = [
    'pdf'   => 'pdf.png',
    'doc'   => 'doc.png',
    'docx'  => 'doc.png',
    'jpg'   => 'image.png',
    'jpeg'  => 'image.png',
    'png'   => 'image.png',
    'gif'   => 'image.png',
    'zip'   => 'zip.png',
    'rar'   => 'zip.png',
  ];
  return "/assets/img/icons/" . ($iconMap[$ext] ?? 'file.png');
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
      <!-- Flash Messages -->
      <?php showFlash(); ?>

      <div class="flex flex-col gap-4">
        <!-- Folder Creation + Upload -->
        <!-- New Button + Dropdown -->
        <div class="relative inline-block text-left py-4">
          <button type="button" id="newDropdownToggle"
            class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">
            <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4  mr-2">
            <span>New</span>
          </button>

          <div id="newDropdownMenu"
            class="absolute mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
            <button type="button" id="openCreateFolderModal"
              class="flex justify-center items-center gap-5 w-full text-left px-4 py-2 hover:bg-emerald-100 text-md">
              <img src="/assets/img/new-folder.png" alt="New Folder" class="w-5 h-5">
              <span>New Folder</span>
            </button>

            <?php if (!empty($currentPath)): ?>
              <!-- Upload File -->
              <form action="/controllers/upload-file.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
                <input type="file" name="file" id="uploadInput" class="hidden" accept=".pdf,.doc,.docx,.jpg,.png" required>
                <button type="button" id="uploadTrigger"
                  class="flex justify-center items-center  gap-5 w-full text-left px-4 py-2 hover:bg-emerald-100 text-md">
                  <img src="/assets/img/file-upload.png" alt="Upload" class="w-5 h-5">
                  <span>File Upload</span>
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <!-- Create Folder Modal -->
        <div id="createFolderModal"
          class="fixed inset-0  z-50 hidden items-center justify-center">
          <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
          <div class="relative z-10 bg-white p-6 rounded-4xl shadow-md w-full max-w-md border border-emerald-500">
            <h2 class="text-2xl  mb-4">New Folder</h2>
            <form method="POST" action="/controllers/create-folder.php" class="flex flex-col gap-3" id="createFolderForm">
              <input type="text" name="folder_name" placeholder="Folder name" class="border px-3 py-4 rounded" required>
              <input type="hidden" name="path" id="createFolderPath"> <!-- ✅ Inject current path here -->
              <div class="flex justify-end gap-2 mt-5">
                <button type="button" id="cancelCreateFolder" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100">Cancel</button>
                <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100">Create</button>
              </div>
            </form>
          </div>
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
              $nextPath = trim($currentPath . '/' . $folder['name'], '/');
              $menuId = 'menu-' . md5($folder['name']);
              ?>
              <div class="flex item folder-item hover:bg-emerald-50 px-2 gap-2 py-2">
                <a href="?path=<?= urlencode($nextPath) ?>" class="flex justify-between font-medium cursor-default items-center w-full">
                  <div class="flex items-center mr-10 gap-3">
                    <img src="/assets/img/folder.png" alt="Folder" class="w-5 h-5">
                    <span class="text-sm font-medium"><?= htmlspecialchars($folder['name']) ?></span>
                  </div>
                  <div class="grid grid-cols-3 items-center text-center text-xs text-gray-500">
                    <span class="px-2 bg-gray-200 mr-2 py-1 rounded">
                      <?= $folder['fileCount'] ?> file<?= $folder['fileCount'] !== 1 ? 's' : '' ?>
                      <?php if ($folder['fileCount'] === 0): ?>
                        <span class="text-gray-400 italic ml-1">(empty)</span>
                      <?php endif; ?>
                    </span>
                    <span class="mr-2"><?= $folder['modified'] ?></span>
                    <span><?= formatSize($folder['size']) ?></span>
                  </div>
                </a>

                <!-- Menu dot and dropdown -->
                <div class="flex items-center gap-2">
                  <button class="cursor-pointer menu-toggle hover:bg-emerald-300 rounded-full p-2 transition duration-200 ease-in-out" data-target="<?= $menuId ?>">
                    <img src="/assets/img/dots-icon.png" alt="Menu" class="w-5 h-5">
                  </button>
                  <div id="<?= $menuId ?>" class="absolute right-18 bg-white  rounded shadow-lg hidden text-sm w-44">
                    <button class="block px-4 py-2 hover:bg-emerald-100 w-full text-left rename-btn"
                      data-name="<?= htmlspecialchars($folder['name']) ?>"
                      data-type="folder"
                      data-path="<?= htmlspecialchars($currentPath) ?>">
                      Rename
                    </button>
                    <button class="block px-4 py-2 hover:bg-emerald-100 w-full text-left delete-btn"
                      data-name="<?= htmlspecialchars($folder['name']) ?>"
                      data-type="folder"
                      data-path="<?= htmlspecialchars($currentPath) ?>">
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

            <!-- File Items -->
            <?php if (!empty($currentPath)): ?>
              <?php foreach ($files as $file): ?>
                <?php
                $filename = $file['name'];
                $filepath = $file['path'];
                $modified = $file['modified'];
                $icon = getFileIcon($filename);
                $fileUrl = "/uploads/staff/$userId/" . ($currentPath ? $currentPath . '/' : '') . rawurlencode($filename);
                $isImage = preg_match('/\.(jpg|jpeg|png|gif)$/i', $filename);
                $menuId = 'menu-' . md5($filename);
                ?>
                <div class="flex item file-item hover:bg-emerald-50 px-2 gap-2 py-2">
                  <!-- Clickable row -->
                  <a href="<?= $fileUrl ?>" target="_blank" class="font-medium flex justify-between items-center w-full cursor-default">
                    <div class="flex items-center gap-3">
                      <img src="<?= $icon ?>" alt="File icon" class="w-5 h-5">
                      <span class="text-sm"><?= htmlspecialchars($filename) ?></span>
                    </div>
                    <div class="grid grid-cols-2 items-center gap-3 text-xs text-gray-500">
                      <span><?= $modified ?></span>
                      <span><?= formatSize($file['size']) ?></span>
                    </div>
                  </a>

                  <!-- Menu dot and dropdown -->
                  <div class="flex items-center gap-2">
                    <button class="cursor-pointer menu-toggle hover:bg-emerald-300 rounded-full p-2 transition duration-200 ease-in-out" data-target="<?= $menuId ?>">
                      <img src="/assets/img/dots-icon.png" alt="Menu" class="w-5 h-5">
                    </button>
                    <div id="<?= $menuId ?>" class="absolute right-18 bg-white rounded shadow-lg hidden text-sm w-44">
                      <?php if ($isImage): ?>
                        <a href="<?= $fileUrl ?>" target="_blank" class="block px-4 py-2 hover:bg-emerald-100 w-full text-left">Preview</a>
                      <?php endif; ?>
                      <a href="<?= $fileUrl ?>" download class="block px-4 py-2 hover:bg-emerald-100 w-full text-left">Download</a>
                      <button class="block px-4 py-2 hover:bg-emerald-100 w-full text-left rename-btn" data-name="<?= htmlspecialchars($filename) ?>" data-type="file">Rename</button>
                      <button class="block px-4 py-2 hover:bg-emerald-100 w-full text-left delete-btn" data-name="<?= htmlspecialchars($filename) ?>" data-type="file">Delete</button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>

              <?php if (empty($files) && empty($folders)): ?>
                <p class="text-gray-500 text-sm py-5">This folder is empty.</p>
              <?php elseif (empty($files)): ?>
                <p class="text-gray-400 text-sm italic py-3">No files found, but subfolders are present.</p>
              <?php endif; ?>
            <?php endif; ?>

          </div>
        </div>
    </section>

    <!-- Rename Modal -->
    <div id="renameModal" role="dialog" aria-labelledby="renameTypeLabel"
      class="fixed inset-0 bg-opacity-50 z-50 hidden items-center justify-center">
      <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
      <div class="relative bg-white p-6 rounded-4xl shadow-md w-full max-w-md z-10 border border-emerald-500">
        <h2 class="text-2xl  mb-4">
          Rename <span id="renameTypeLabel"></span>
        </h2>
        <form id="renameForm" method="POST" action="/controllers/rename-item.php" class="flex flex-col gap-3 ">
          <input type="hidden" name="type" id="renameType">
          <input type="hidden" name="old_name" id="renameOldName">
          <input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
          <input type="text" name="new_name" id="renameNewName"
            class="border px-3 py-4 rounded" required>
          <small id="renameExtensionHint" class="text-xs text-gray-500 hidden"></small>
          <div class="flex justify-end gap-2 mt-5">
            <button type="button" id="cancelRename"
              class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100">Cancel</button>
            <button type="submit"
              class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100">Rename</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-opacity-50 z-50 hidden items-center justify-center">
      <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
      <div class="bg-white p-6 rounded-4xl shadow-md w-full max-w-md z-10 relative border border-emerald-500">
        <h2 class="text-2xl  mb-4">
          Delete <span id="deleteTypeLabel"></span>?
        </h2>
        <p class="text-md mb-4">
          Are you sure you want to delete <strong id="deleteItemName" class="text-red-700"></strong>? This action cannot be undone.
        </p>
        <form id="deleteForm" method="POST" action="/controllers/delete-item.php" class="flex flex-col gap-3">
          <input type="hidden" name="type" id="deleteType">
          <input type="hidden" name="name" id="deleteName">
          <input type="hidden" name="path" id="deletePath" value="<?= htmlspecialchars($currentPath) ?>">
          <div class="flex justify-end gap-2 mt-5">
            <button type="button" id="cancelDelete"
              class="px-3 py-1 text-emerald-700  rounded hover:bg-emerald-100">Cancel</button>
            <button type="submit"
              class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100">Delete</button>
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