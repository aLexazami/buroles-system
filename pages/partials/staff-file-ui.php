<?php
require_once __DIR__ . '/../../helpers/sharing-utils.php';
require_once __DIR__ . '/../../config/database.php';

$userId       = $_SESSION['user_id'] ?? 0;
$accessLevel  = $GLOBALS['accessLevel'] ?? 'owner';
$targetId     = $GLOBALS['targetId'] ?? 0;
$currentPath  = $GLOBALS['currentPath'] ?? '';

$isSharedView = isset($_GET['shared']) && $_GET['shared'] === '1';
$ownerId      = $targetId;
$linkUserId   = $isSharedView ? $ownerId : $userId;
$sharedParam  = $isSharedView ? '&shared=1' : '';

error_log("🧠 UI Render → user: $userId, target: $targetId, access: $accessLevel, shared: " . ($isSharedView ? 'yes' : 'no'));

$canEdit     = in_array($accessLevel, ['owner', 'editor'], true);
$canComment  = $accessLevel === 'comment';
$accessLabel = getAccessLabel($accessLevel);

$trueRoleId   = (int)($_SESSION['original_role_id'] ?? 0);
$activeRoleId = (int)($_SESSION['active_role_id'] ?? 1);
$roleId       = (string)$activeRoleId;

$safePath       = htmlspecialchars(trim($currentPath, '/'));
$segments       = explode('/', $currentPath);
$breadcrumbPath = '';


$ownerEmail = '';
if ($ownerId) {
  try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id AND is_archived = 0 LIMIT 1");
    $stmt->execute(['id' => $ownerId]);
    $ownerEmail = $stmt->fetchColumn() ?: '';
  } catch (Exception $e) {
    error_log("Owner email fetch error: " . $e->getMessage());
  }
}
?>
<div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
  <img src="/assets/img/archive-user.png" class="w-5 h-5 sm:w-6 sm:h-6" alt="Archive icon">
  <h1 class="font-bold text-md sm:text-lg">Manage File</h1>
</div>
<!-- Flash Messages -->
<?php showFlash(); ?>

<div class="flex flex-col">
  <!-- Folder Creation + Upload -->
  <?php if ($canEdit): ?>
    <div class="relative inline-block text-left py-4">
      <button type="button" id="newDropdownToggle"
        class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base"
        aria-label="Open new item menu" title="Create new folder or upload file">
        <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
        <span>New</span>
      </button>

      <div id="newDropdownMenu"
        class="absolute mt-2 w-40 sm:w-48 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
        <button type="button" id="openCreateFolderModal"
          class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-emerald-100 text-sm sm:text-base cursor-pointer">
          <img src="/assets/img/new-folder.png" alt="New Folder" class="w-5 h-5">
          New Folder
        </button>

        <?php if (!empty($currentPath)): ?>
          <form action="/controllers/upload-file.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
            <input type="hidden" name="user_id" value="<?= $targetId ?>">
            <input type="file" name="file" id="uploadInput" class="hidden" accept=".pdf,.doc,.docx,.jpg,.png" required>
            <button type="button" id="uploadTrigger"
              class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-emerald-100 text-sm sm:text-base cursor-pointer">
              <img src="/assets/img/file-upload.png" alt="Upload" class="w-5 h-5">
              File Upload
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Create Folder Modal -->
  <div id="createFolderModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
      <h2 class="text-xl sm:text-2xl mb-4">New Folder</h2>
      <form method="POST" action="/controllers/create-folder.php" class="flex flex-col gap-3" id="createFolderForm">
        <input type="text" name="folder_name" placeholder="Folder name" class="border px-3 py-3 rounded text-sm sm:text-base" required>
        <input type="hidden" name="path" id="createFolderPath">
        <div class="flex justify-end gap-2 mt-5">
          <button type="button" id="cancelCreateFolder" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
          <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Create</button>
        </div>
      </form>
    </div>
  </div>

  <div class="bg-white shadow-2xl rounded-md p-4 sm:p-6 min-h-screen ">
    <span class="text-xs text-emerald-800 font-bold bg-emerald-100 rounded-md p-2">Access: <?= $accessLabel ?></span>

    <!-- Breadcrumb -->
    <div class="text-sm text-gray-500 flex flex-wrap items-center pb-3 gap-2 overflow-x-auto whitespace-nowrap mt-4">
      <?php
      $breadcrumbPath = '';
      $startIndex = $isSharedView ? 0 : -1; // -1 means show "Home" for owner
      if (!$isSharedView): ?>
        <img src="/assets/img/folder.png" alt="Root" class="w-4 h-4">
        <a href="/pages/staff/file-manager.php?user_id=<?= $linkUserId ?>&path=" class="text-emerald-600 hover:underline">Home</a>
      <?php endif; ?>

      <?php foreach ($segments as $index => $segment):
        if ($segment === '') continue;
        if ($isSharedView && $index === 0) {
          // First segment is the shared root, skip label
          $breadcrumbPath = $segment;
        } else {
          $breadcrumbPath .= ($breadcrumbPath === '' ? '' : '/') . $segment;
        }
      ?>
        <span>/</span>
        <img src="/assets/img/folder.png" alt="Folder" class="w-4 h-4">
        <a href="/pages/staff/file-manager.php?user_id=<?= $linkUserId ?>&path=<?= urlencode($breadcrumbPath) ?><?= $sharedParam ?>" class="text-emerald-600 hover:underline">
          <?= htmlspecialchars($segment) ?>
        </a>
      <?php endforeach; ?>
    </div>


    <!-- Search -->
    <!-- Unified Search -->
    <div class="flex flex-wrap items-center gap-2 mb-4">
      <input type="text" id="unifiedSearch" placeholder="Search folders and files"
        class="border px-3 py-2 rounded w-full max-w-md text-sm" />
      <button id="clearUnifiedSearch"
        class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm cursor-pointer">
        Clear
      </button>
    </div>

    <!-- Unified Folder + File List -->
    <div class="flex flex-col divide-y divide-black-200" id="itemList">
      <!-- Sorting Controls -->
      <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-sm text-gray-700 pb-5">
        <span>Sort by:</span>
        <a href="?path=<?= urlencode($currentPath) ?>&sort=name" class="hover:underline <?= $sortBy === 'name' ? 'font-bold underline' : '' ?>">Name</a>
        <a href="?path=<?= urlencode($currentPath) ?>&sort=modified" class="hover:underline <?= $sortBy === 'modified' ? 'font-bold underline' : '' ?>">Modified</a>
      </div>

      <!-- Header Row -->
      <div class="flex flex-wrap px-2 py-2 items-center w-full text-sm text-gray-600 border-b">
        <div class="flex flex-wrap items-center w-full justify-between gap-2 sm:gap-0">
          <!-- Name Label -->
          <div class="flex items-center gap-2 sm:gap-3 flex-grow">
            <span class="font-medium">Name</span>
          </div>

          <!-- Metadata Labels (Desktop Only) -->
          <div class="hidden sm:flex items-center text-center font-medium gap-2 sm:gap-3">
            <span class="w-24 text-xs sm:text-sm">Files</span>
            <span class="w-32 text-xs sm:text-sm">Modified</span>
            <span class="w-24 text-xs sm:text-sm">Size</span>
          </div>

          <!-- Dot Menu Column -->
          <div class="w-10"></div>
        </div>
      </div>

      <?php foreach ($folders as $folder): ?>
        <?php
        $folderName = $folder['name'];
        $nextPath   = trim($currentPath . '/' . $folderName, '/');
        $menuId     = 'menu-' . md5($folderName);
        ?>
        <div class="flex items-center hover:bg-emerald-50 px-2 py-3 sm:py-2 gap-2 folder-item" data-name="<?= htmlspecialchars($folderName) ?>">
          <!-- Folder Name -->
          <a href="/pages/staff/file-manager.php?user_id=<?= $linkUserId ?>&path=<?= urlencode($nextPath) ?><?= $sharedParam ?>"
            class="flex items-center gap-2 sm:gap-3 flex-grow"
            title="Open folder <?= htmlspecialchars($folderName) ?>">
            <img src="/assets/img/folder.png" alt="Folder" class="w-5 h-5" title="<?= htmlspecialchars($folderName) ?>">
            <span class="text-sm font-medium"><?= htmlspecialchars($folderName) ?></span>
          </a>

          <!-- Metadata (Desktop Only) -->
          <div class="hidden sm:flex items-center text-xs text-gray-500">
            <span class="w-24 text-center px-2 bg-gray-200 py-1 rounded">
              <?= $folder['fileCount'] ?> file<?= $folder['fileCount'] !== 1 ? 's' : '' ?>
              <?php if ($folder['fileCount'] === 0): ?>
                <span class="text-gray-400 italic ml-1">(empty)</span>
              <?php endif; ?>
            </span>
            <span class="w-32 text-center"><?= $folder['modified'] ?></span>
            <span class="w-24 text-center"><?= formatSize($folder['size']) ?></span>
          </div>

          <?php if ($canComment): ?>
            <button class="flex items-center gap-2 px-3 py-2 text-sm text-emerald-700 hover:underline comment-btn"
              data-name="<?= htmlspecialchars($filename) ?>"
              data-path="<?= htmlspecialchars($currentPath) ?>"
              data-user-id="<?= $targetId ?>">
              💬 Comment
            </button>
          <?php endif; ?>

          <!-- Dot Menu -->
          <?php if ($canEdit): ?>
            <div class="w-10 flex justify-end">
              <button class="cursor-pointer menu-toggle hover:bg-emerald-300 rounded-full p-2"
                data-target="<?= $menuId ?>"
                aria-label="Open folder options for <?= htmlspecialchars($folderName) ?>">
                <img src="/assets/img/dots-icon.png" alt="Menu" class="w-5 h-5">
              </button>
            </div>

            <!-- Dropdown Menu -->
            <div id="<?= $menuId ?>" class="absolute right-4 sm:right-18 bg-white rounded shadow-lg hidden text-sm w-44 transition ease-out duration-150 font-semibold">
              <!-- Actions -->
              <button class="open-share-btn flex items-center gap-3 px-4 py-2 rounded hover:bg-emerald-100 text-sm text-left  w-full cursor-pointer"
                data-name="<?= htmlspecialchars($folderName) ?>"
                data-path="<?= htmlspecialchars($currentPath) ?>"
                data-type="folder"
                data-user-id="<?= $targetId ?>">
                <img src="/assets/img/share-icon.png" alt="Share Icon" class="w-4 h-4" />
                <span>Share</span>
              </button>
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


              <!-- Mobile Metadata (Only visible on mobile, below actions) -->
              <div class="block sm:hidden px-4 py-2 text-gray-600 border-t">
                <p class="text-xs"><strong>Files:</strong> <?= $folder['fileCount'] ?><?= $folder['fileCount'] === 0 ? ' (empty)' : '' ?></p>
                <p class="text-xs"><strong>Modified:</strong> <?= $folder['modified'] ?></p>
                <p class="text-xs"><strong>Size:</strong> <?= formatSize($folder['size']) ?></p>
              </div>
            </div>
          <?php endif; ?>
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
          <div class="flex items-center hover:bg-emerald-50 px-2 py-3 sm:py-2 gap-2 file-item" data-name="<?= htmlspecialchars($filename) ?>">
            <!-- File Name -->
            <a href="<?= $fileUrl ?>" target="_blank"
              class="flex items-center gap-2 sm:gap-3 flex-grow"
              title="Open <?= htmlspecialchars($filename) ?>">
              <img src="<?= getFileIcon($filename) ?>" alt="File icon" class="w-5 h-5" title="<?= htmlspecialchars($filename) ?>">
              <span class="text-sm font-medium"><?= htmlspecialchars($filename) ?></span>
            </a>

            <!-- Metadata (Desktop Only) -->
            <div class="hidden sm:flex items-center text-xs text-gray-500">
              <span class="w-32 text-center"><?= $file['modified'] ?></span>
              <span class="w-24 text-center"><?= formatSize($file['size']) ?></span>
            </div>

            <?php if ($canComment): ?>
              <button class="flex items-center gap-2 px-3 py-2 text-sm text-emerald-700 hover:underline comment-btn"
                data-name="<?= htmlspecialchars($filename) ?>"
                data-path="<?= htmlspecialchars($currentPath) ?>"
                data-user-id="<?= $targetId ?>">
                💬 Comment
              </button>
            <?php endif; ?>

            <!-- Dot Menu -->
            <?php if ($canEdit): ?>
              <div class="w-10 flex justify-end">
                <button class="cursor-pointer menu-toggle hover:bg-emerald-300 rounded-full p-2"
                  data-target="<?= $menuId ?>"
                  aria-label="Open file options for <?= htmlspecialchars($filename) ?>">
                  <img src="/assets/img/dots-icon.png" alt="Menu" class="w-5 h-5">
                </button>
              </div>

              <!-- Dropdown Menu -->
              <div id="<?= $menuId ?>" class="absolute right-4 sm:right-18 bg-white rounded shadow-lg hidden text-sm w-40 sm:w-44 transition ease-out duration-150 font-semibold">
                <button class="open-share-btn flex items-center gap-3 px-4 py-2 rounded hover:bg-emerald-100 text-sm text-left  w-full cursor-pointer"
                  data-name="<?= htmlspecialchars($filename) ?>"
                  data-path="<?= htmlspecialchars($currentPath) ?>"
                  data-type="file"
                  data-user-id="<?= $targetId ?>">
                  <img src="/assets/img/share-icon.png" alt="Share Icon" class="w-4 h-4" />
                  <span>Share</span>
                </button>
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

                <!-- Mobile Metadata (Only visible on mobile, below actions) -->
                <div class="block sm:hidden px-4 py-2 text-gray-600 border-t">
                  <p class="text-xs"><strong>Modified:</strong> <?= $file['modified'] ?></p>
                  <p class="text-xs"><strong>Size:</strong> <?= formatSize($file['size']) ?></p>
                </div>
              </div>
            <?php endif; ?>
            <!-- 💬 Comments (visible to all with access) -->
            <?php if (!empty($file['id'])): ?>
              <?php
              $comments = getFileComments($pdo, (int)$file['id']);
              foreach ($comments as $comment) {
                echo '<div class="ml-8 text-xs text-gray-600 italic">💬 '
                  . htmlspecialchars($comment['comment_text'])
                  . '<small class="text-gray-400 ml-2">('
                  . htmlspecialchars($comment['commenter_email'])
                  . ')</small></div>';
              }
              ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Empty State Messages -->
      <?php if (empty($files) && empty($folders)): ?>
        <p class="text-gray-500 text-sm py-5 px-4 text-center">This folder is empty.</p>
      <?php elseif (!empty($folders) && empty($files) && !empty($currentPath)): ?>
        <p class="text-gray-400 text-sm italic py-3 px-4 text-center">No files found, but subfolders are present.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Share Modal -->
<div id="shareModal" role="dialog" aria-labelledby="shareModalLabel"
  class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Share <span id="shareModalLabel" class="text-emerald-700 font-semibold text-xl sm:text-2xl"></span></h2>
    <form id="shareForm" method="POST" action="/controllers/files/share-item.php" class="flex flex-col gap-3">
      <input type="hidden" name="item_path" id="shareItemPath">
      <input type="hidden" name="owner_id" id="shareOwnerId" value="<?= $targetId ?>">
      <input type="hidden" id="shareOwnerEmail" value="<?= htmlspecialchars($ownerEmail) ?>">
      <input type="hidden" name="type" id="shareItemType">

      <!-- Recipient Email -->
      <div class="relative mb-5">
        <div class="flex items-center gap-2 border-2 rounded-lg px-3 py-2" id="recipientInputWrapper">
          <img id="selectedAvatar" src="/assets/img/add-user.png" alt="User Avatar"
            class="w-5 h-5 sm:w-8 sm:h-8 rounded-full object-cover" />
          <input type="email" name="recipient_email" id="shareRecipientEmail"
            class="flex-1 h-10 sm:h-12 p-2 border-l-2 focus:outline-none text-sm sm:text-base"
            placeholder="Add people" autocomplete="off" required>
        </div>

        <!-- Suggestions -->
        <ul id="emailSuggestions"
          class="absolute left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto z-20 text-sm text-gray-700 hidden"></ul>
      </div>

      <!-- Access Level -->
      <select name="access_level" id="shareAccessLevel"
        class="border-2 rounded-lg px-3 py-3  text-sm sm:text-base cursor-pointer" required>
        <option value="view">Viewer</option>
        <option value="comment">Comment</option>
        <option value="edit">Editor</option>
      </select>
      <div class="flex items-center gap-2">
        <img src="/assets/img/info-icon.png" alt="Add User Icon" class="w-3 h-3 " />
        <small id="accessLevelDescription" class="text-xs text-gray-500"></small>
      </div>

      <div class="flex justify-end gap-2 mt-5">
        <button type="button" id="cancelShare" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Share</button>
      </div>
    </form>
  </div>
</div>

<!-- Rename Modal -->
<div id="renameModal" role="dialog" aria-labelledby="renameTypeLabel"
  class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">
      Rename <span id="renameTypeLabel"></span>
    </h2>
    <form id="renameForm" method="POST" action="/controllers/rename-item.php" class="flex flex-col gap-3">
      <input type="hidden" name="type" id="renameType">
      <input type="hidden" name="old_name" id="renameOldName">
      <input type="hidden" name="user_id" id="renameUserId" value="<?= $targetId ?>">
      <input type="hidden" name="path" id="renamePath" value="<?= htmlspecialchars($currentPath) ?>">
      <input type="text" name="new_name" id="renameNewName" class="border px-3 py-3 rounded text-sm sm:text-base" required>
      <small id="renameExtensionHint" class="text-xs text-gray-500 hidden"></small>
      <div class="flex justify-end gap-2 mt-5">
        <button type="button" id="cancelRename" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Rename</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" role="dialog" aria-labelledby="deleteTypeLabel" aria-modal="true"
  class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 relative border border-emerald-500 transition-opacity duration-200">
    <h2 class="text-xl sm:text-2xl mb-4">
      Delete <span id="deleteTypeLabel"></span>?
    </h2>
    <p class="text-sm sm:text-base mb-4">
      Are you sure you want to delete <strong id="deleteItemName" class="text-red-700"></strong>? This action cannot be undone.
    </p>
    <form id="deleteForm" method="POST" action="/controllers/delete-item.php" class="flex flex-col gap-3">
      <input type="hidden" name="type" id="deleteType">
      <input type="hidden" name="name" id="deleteName">
      <input type="hidden" name="path" id="deletePath">
      <input type="hidden" name="user_id" id="deleteUserId" value="<?= $targetId ?>">
      <div class="flex justify-end gap-2 mt-5">
        <button type="button" id="cancelDelete"
          class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm transition duration-150 cursor-pointer">
          Cancel
        </button>
        <button type="submit"
          class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm transition duration-150 cursor-pointer">
          Delete
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Comment Modal -->
<div id="commentModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Comment on <span id="commentFileLabel" class="text-emerald-700 font-semibold"></span></h2>
    <form id="commentForm" method="POST" action="/controllers/files/comment-item.php" class="flex flex-col gap-3">
      <input type="hidden" name="file_name" id="commentFileName">
      <input type="hidden" name="path" id="commentPath">
      <input type="hidden" name="user_id" value="<?= $targetId ?>">
      <textarea name="comment" rows="4" class="border px-3 py-2 rounded text-sm sm:text-base" placeholder="Write your comment..." required></textarea>
      <div class="flex justify-end gap-2 mt-5">
        <button type="button" id="cancelComment" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Post</button>
      </div>
    </form>
  </div>
</div>
<script>
  document.querySelectorAll('.comment-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('commentFileLabel').textContent = btn.dataset.name;
      document.getElementById('commentFileName').value = btn.dataset.name;
      document.getElementById('commentPath').value = btn.dataset.path;
      document.getElementById('commentModal').classList.remove('hidden');
    });
  });
  document.getElementById('cancelComment').addEventListener('click', () => {
    document.getElementById('commentModal').classList.add('hidden');
  });
</script>