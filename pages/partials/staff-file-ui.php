<div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
  <img src="/assets/img/archive-user.png" class="w-5 h-5" alt="Archive icon">
  <h1 class="font-bold text-lg">Manage File</h1>
</div>
<!-- Flash Messages -->
<?php showFlash(); ?>

<?php
$trueRoleId   = (int)($_SESSION['original_role_id'] ?? 0);
$activeRoleId = (int)($_SESSION['active_role_id'] ?? 1);
$roleId       = (string)$activeRoleId;
$userId       = (int)($_SESSION['user_id'] ?? 0);
$targetId     = (int)($_GET['user_id'] ?? $userId);
$currentPath  = sanitizePath($_GET['path'] ?? '');
$safePath    = htmlspecialchars(trim($currentPath, '/'));
$segments     = explode('/', $currentPath);
$breadcrumbPath = '';
?>


<div class="flex flex-col gap-4">
  <!-- Folder Creation + Upload -->
  <!-- New Button + Dropdown -->
  <?php if ((int)$activeRoleId === 1 && (int)$userId === (int)$targetId): ?>
    <div class="relative inline-block text-left py-4">
      <button type="button" id="newDropdownToggle"
        class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700"
        aria-label="Open new item menu" title="Create new folder or upload file">
        <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
        <span>New</span>
      </button>

      <div id="newDropdownMenu"
        class="absolute mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
        <!-- New Folder Button -->
        <button type="button" id="openCreateFolderModal"
          class="flex justify-center items-center gap-5 w-full text-left px-4 py-2 hover:bg-emerald-100 text-md"
          aria-label="Create new folder">
          <img src="/assets/img/new-folder.png" alt="New Folder" class="w-5 h-5">
          <span>New Folder</span>
        </button>

        <?php if (!empty($currentPath)): ?>
          <!-- Upload File -->
          <form action="/controllers/upload-file.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
            <input type="hidden" name="user_id" value="<?= $targetId ?>">
            <input type="file" name="file" id="uploadInput" class="hidden" accept=".pdf,.doc,.docx,.jpg,.png" required>
            <button type="button" id="uploadTrigger"
              class="flex justify-center items-center gap-5 w-full text-left px-4 py-2 hover:bg-emerald-100 text-md"
              aria-label="Upload file to <?= htmlspecialchars($currentPath) ?>" title="Upload file">
              <img src="/assets/img/file-upload.png" alt="Upload" class="w-5 h-5">
              <span>File Upload</span>
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>


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
      <a href="?user_id=<?= $targetId ?>&path=" class="text-emerald-600 hover:underline">Home</a>

      <?php foreach ($segments as $index => $segment): ?>
        <?php
        if ($segment === '') continue;
        $breadcrumbPath .= ($index > 0 ? '/' : '') . $segment;
        ?>
        <span>/</span>
        <a href="?user_id=<?= $targetId ?>&path=<?= urlencode($breadcrumbPath) ?>" class="text-emerald-600 hover:underline">
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

      <!-- Header Row -->
      <div class="flex px-2 py-2 items-center w-full text-sm text-gray-600 border-b ">
        <div class="flex items-center w-full justify-between">
          <div class="flex items-center gap-3 flex-grow">
            <span class="font-medium">Name</span>
          </div>
          <div class="flex items-center text-center font-medium gap-3">
            <span class="w-24">Files</span>
            <span class="w-32">Modified</span>
            <span class="w-24">Size</span>
          </div>
          <div class="w-10"></div>
        </div>
      </div>

      <?php foreach ($folders as $folder): ?>
        <?php
        $folderName = $folder['name'];
        $nextPath   = trim($currentPath . '/' . $folderName, '/');
        $menuId     = 'menu-' . md5($folderName);
        ?>
        <div class="flex item folder-item hover:bg-emerald-50 px-2 gap-2 py-2">
          <a href="?user_id=<?= $targetId ?>&path=<?= urlencode($nextPath) ?>"
            class="flex justify-between items-center w-full"
            title="Open folder <?= htmlspecialchars($folderName) ?>">
            <div class="flex items-center gap-3 flex-grow">
              <img src="/assets/img/folder.png" alt="Folder" class="w-5 h-5" title="<?= htmlspecialchars($folderName) ?>">
              <span class="text-sm font-medium"><?= htmlspecialchars($folderName) ?></span>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 gap-3">
              <span class="w-24 text-center px-2 bg-gray-200 py-1 rounded">
                <?= $folder['fileCount'] ?> file<?= $folder['fileCount'] !== 1 ? 's' : '' ?>
                <?php if ($folder['fileCount'] === 0): ?>
                  <span class="text-gray-400 italic ml-1">(empty)</span>
                <?php endif; ?>
              </span>
              <span class="w-32 text-center"><?= $folder['modified'] ?></span>
              <span class="w-24 text-center"><?= formatSize($folder['size']) ?></span>
            </div>
          </a>


          <!-- Folder Menu -->
          <div class="flex items-center gap-2 w-10 justify-end">
            <?php if ((int)$activeRoleId === 1 && (int)$userId === (int)$targetId): ?>
              <button class="cursor-pointer menu-toggle hover:bg-emerald-300 rounded-full p-2"
                data-target="<?= $menuId ?>"
                aria-label="Open folder options for <?= htmlspecialchars($folderName) ?>">
                <img src="/assets/img/dots-icon.png" alt="Menu" class="w-5 h-5">
              </button>
              <div id="<?= $menuId ?>" class="absolute right-18 bg-white rounded shadow-lg hidden text-sm w-44 transition ease-out duration-150  font-semibold">
                <button class="flex items-center gap-3 cursor-pointer px-4 py-2 hover:bg-emerald-100 w-full text-left rename-btn"
                  data-name="<?= htmlspecialchars($folderName) ?>"
                  data-type="folder"
                  data-path="<?= $safePath ?>"
                  data-user-id="<?= $targetId ?>">
                  <img src="/assets/img/edit-icon.png" alt="Key" class="w-4 h-4">
                  Rename
                </button>
                <button class="flex items-center gap-3 cursor-pointer px-4 py-2 hover:bg-emerald-100 w-full text-left delete-btn text-red-600"
                  data-name="<?= htmlspecialchars($folderName) ?>"
                  data-type="folder"
                  data-path="<?= $safePath ?>"
                  data-user-id="<?= $targetId ?>">
                  <img src="/assets/img/delete-icon.png" alt="Key" class="w-4 h-4">
                  Delete
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (!empty($currentPath)): ?>
        <?php foreach ($files as $file): ?>
          <?php
          $filename = $file['name'];
          $fileUrl  = getUserUploadUrl($roleId, (string)$targetId, $currentPath, $filename);
          $isImage  = preg_match('/\.(jpg|jpeg|png|gif)$/i', $filename);
          $menuId   = 'menu-' . md5($filename);
          ?>
          <div class="flex item file-item hover:bg-emerald-50 px-2 gap-2 py-2">
            <a href="<?= $fileUrl ?>" target="_blank"
              class="flex justify-between items-center w-full cursor-default"
              title="Open <?= htmlspecialchars($filename) ?>">
              <div class="flex items-center gap-3 flex-grow">
                <img src="<?= getFileIcon($filename) ?>" alt="File icon" class="w-5 h-5" title="<?= htmlspecialchars($filename) ?>">
                <span class="text-sm font-medium"><?= htmlspecialchars($filename) ?></span>
              </div>
              <div class="flex items-center text-xs text-gray-500 gap-3">
                <span class="w-24 text-center text-gray-400 italic"></span>
                <span class="w-32 text-center"><?= $file['modified'] ?></span>
                <span class="w-24 text-center"><?= formatSize($file['size']) ?></span>
              </div>
            </a>

            <!-- File Menu -->
            <div class="flex items-center gap-2 w-10 justify-end">
              <?php if ((int)$activeRoleId === 1 && (int)$userId === (int)$targetId): ?>
                <button class="cursor-pointer menu-toggle hover:bg-emerald-300 rounded-full p-2"
                  data-target="<?= $menuId ?>"
                  aria-label="Open file options for <?= htmlspecialchars($filename) ?>">
                  <img src="/assets/img/dots-icon.png" alt="Menu" class="w-5 h-5">
                </button>
                <div id="<?= $menuId ?>" class="absolute  right-18 bg-white rounded shadow-lg hidden text-sm w-44 transition ease-out duration-150  font-semibold ">
                  <?php if ($isImage): ?>
                    <a href="<?= $fileUrl ?>" target="_blank" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100 w-full text-left">
                      <img src="/assets/img/preview-icon.png" alt="Key" class="w-4 h-4">
                      Preview
                    </a>
                  <?php endif; ?>
                  <a href="<?= $fileUrl ?>" download class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100 w-full text-left">
                    <img src="/assets/img/download-icon.png" alt="Key" class="w-4 h-4">
                    Download
                  </a>
                  <button class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100 w-full text-left rename-btn cursor-pointer"
                    data-name="<?= htmlspecialchars($filename) ?>"
                    data-type="file"
                    data-path="<?= $safePath ?>"
                    data-user-id="<?= $targetId ?>">
                    <img src="/assets/img/edit-icon.png" alt="Key" class="w-4 h-4">
                    Rename
                  </button>
                  <button class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100 w-full text-left delete-btn text-red-600 cursor-pointer"
                    data-name="<?= htmlspecialchars($filename) ?>"
                    data-type="file"
                    data-path="<?= $safePath ?>"
                    data-user-id="<?= $targetId ?>">
                    <img src="/assets/img/delete-icon.png" alt="Key" class="w-4 h-4">
                    Delete
                  </button>
                </div>
              <?php endif; ?>
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
</div>

<!-- Rename Modal -->
<div id="renameModal" role="dialog" aria-labelledby="renameTypeLabel"
  class="fixed inset-0 bg-opacity-50 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative bg-white p-6 rounded-4xl shadow-md w-full max-w-md z-10 border border-emerald-500">
    <h2 class="text-2xl  mb-4">
      Rename <span id="renameTypeLabel"></span>
    </h2>
    <form id="renameForm" method="POST" action="/controllers/rename-item.php" class="flex flex-col gap-3">
      <input type="hidden" name="type" id="renameType">
      <input type="hidden" name="old_name" id="renameOldName">
      <input type="hidden" name="user_id" id="renameUserId" value="<?= $targetId ?>">
      <input type="hidden" name="path" id="renamePath" value="<?= htmlspecialchars($currentPath) ?>">
      <input type="text" name="new_name" id="renameNewName" class="border px-3 py-4 rounded" required>
      <small id="renameExtensionHint" class="text-xs text-gray-500 hidden"></small>
      <div class="flex justify-end gap-2 mt-5">
        <button type="button" id="cancelRename" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100">Cancel</button>
        <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100">Rename</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" role="dialog" aria-labelledby="deleteTypeLabel" aria-modal="true"
  class="fixed inset-0 bg-opacity-50 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="bg-white p-6 rounded-4xl shadow-md w-full max-w-md z-10 relative border border-emerald-500 transition-opacity duration-200">
    <h2 class="text-2xl mb-4">
      Delete <span id="deleteTypeLabel"></span>?
    </h2>
    <p class="text-md mb-4">
      Are you sure you want to delete <strong id="deleteItemName" class="text-red-700"></strong>? This action cannot be undone.
    </p>
    <form id="deleteForm" method="POST" action="/controllers/delete-item.php" class="flex flex-col gap-3">
      <input type="hidden" name="type" id="deleteType">
      <input type="hidden" name="name" id="deleteName">
      <input type="hidden" name="path" id="deletePath">
      <input type="hidden" name="user_id" id="deleteUserId" value="<?= $targetId ?>">
      <div class="flex justify-end gap-2 mt-5">
        <button type="button" id="cancelDelete"
          class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 transition duration-150">
          Cancel
        </button>
        <button type="submit"
          class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 transition duration-150">
          Delete
        </button>
      </div>
    </form>
  </div>
</div>