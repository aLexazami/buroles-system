<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php'; // showFlash()
require_once __DIR__ . '/../../helpers/head.php'; //renderHead()
require_once __DIR__ . '/../../helpers/file-utils.php'; //getFilesForView()

$userId = $_SESSION['user_id'];
$view = $_GET['view'] ?? 'my-files'; // 'shared-with-me', 'shared-by-me', 'my-files', 'trash'
$folderId = $_GET['folder'] ?? null;


renderHead('Staff');
?>

<body data-folder-id="<?= htmlspecialchars($folderId ?? '') ?>" data-view="<?= htmlspecialchars($view) ?>" data-user-id="<?= htmlspecialchars($userId) ?>"
  class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>
  <!-- Flash Message -->
  <div id="flashContainer" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 space-y-2 w-full max-w-sm sm:max-w-md"></div>
  <?php showFlash(); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
        <img src="/assets/img/manage-file.png" class="w-5 h-5" alt="Manage icon">
        <h1 class="font-bold text-md sm:text-lg">Manage File</h1>
      </div>

      <div class="flex flex-col gap-2">
        <!-- Tabs -->
        <?php
        $folderParam = $view === 'my-files' && $folderId ? '&folder=' . urlencode($folderId) : '';
        ?>
        <div class="flex space-x-4 border-b pb-2 text-sm sm:text-md">
          <a href="/pages/staff/file-manager.php?view=my-files<?= $folderParam ?>" class="<?= $view === 'my-files' ? 'border-b-2 border-emerald-600 text-emerald-600 font-medium' : 'text-gray-600 hover:text-emerald-600' ?>">My Files</a>
          <a href="/pages/staff/file-manager.php?view=shared-with-me" class="<?= $view === 'shared-with-me' ? 'border-b-2 border-emerald-600 text-emerald-600 font-medium' : 'text-gray-600 hover:text-emerald-600' ?>">Shared with Me</a>
          <a href="/pages/staff/file-manager.php?view=shared-by-me" class="<?= $view === 'shared-by-me' ? 'border-b-2 border-emerald-600 text-emerald-600 font-medium' : 'text-gray-600 hover:text-emerald-600' ?>">Shared by Me</a>
          <a href="/pages/staff/file-manager.php?view=trash" class="<?= $view === 'trash' ? 'border-b-2 border-emerald-600 text-emerald-600 font-medium' : 'text-gray-600 hover:text-emerald-600' ?>">Trash</a>
        </div>

        <?php if ($view === 'trash'): ?>
          <!-- 🗑️ Trash Header -->
          <div id="trash-header" class="hidden items-center justify-between bg-emerald-50 border border-emerald-200 rounded-md px-4 py-3 text-sm sm:text-md text-gray-700">
            <span>Items in trash will be deleted forever after 30 days.</span>
            <button id="empty-trash-btn" class="px-3 py-1 font-semibold text-emerald-600 rounded hover:bg-emerald-100 transition text-sm sm:text-md cursor-pointer">
              Empty Trash
            </button>
          </div>
        <?php endif; ?>


        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4">
          <?php if ($view === 'my-files'): ?>
            <div class="relative inline-block text-left py-4">
              <!-- ➕ Trigger Button -->
              <button type="button" id="action-trigger" class="flex items-center justify-center bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 cursor-pointer text-sm sm:text-base" aria-label="Open new item menu" title="Create new folder or upload file">
                <img src="/assets/img/plus.png" alt="Plus" class="w-4 h-4 mr-2">
                <span>New</span>
              </button>

              <!-- 🔽 Dropdown Menu -->
              <ul id="action-menu" class="absolute left-0 mt-2 w-40 sm:w-48 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
                <li>
                  <button id="openUploadBtn" class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-emerald-100 text-sm sm:text-base cursor-pointer">
                    <img src="/assets/img/file-upload.png" alt="Upload" class="w-5 h-5">
                    <span>File Upload</span>
                  </button>
                </li>
                <li>
                  <button data-action="create-folder" class="flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-emerald-100 text-sm sm:text-base cursor-pointer">
                    <img src="/assets/img/new-folder.png" alt="New Folder" class="w-5 h-5">
                    <span>New Folder</span>
                  </button>
                </li>
              </ul>
            </div>
          <?php endif; ?>
        </div>


        <div class="bg-white shadow-2xl rounded-md p-4 sm:p-6  w-full transition-all duration-300">
          <!-- Breadcrumb -->
          <div id="breadcrumb" class="flex flex-wrap items-center text-sm text-emerald-600 hover:underline space-x-1 mb-3"></div>
          <!-- Search -->
          <div class="flex flex-wrap items-center gap-2 mb-4">
            <input type="text" id="folderSearch" placeholder="Search"
              class="border px-3 py-2 rounded w-full max-w-md text-sm" />
            <button id="clearFolderSearch"
              class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm cursor-pointer">
              Clear
            </button>
          </div>
          <!-- ✅ Dynamic File List Container -->
          <div id="file-list" class="divide-y divide-gray-400 mt-4 transition-all duration-300  min-h-[300px]"></div>
        </div>


      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>

  <!-- 📁 File Preview Modal -->
  <div id="preview-overlay" class="fixed inset-0 z-50 hidden w-full h-full bg-[rgba(0,0,0,0.6)] backdrop-blur-sm flex-col ">

    <!-- 🔝 Top Bar -->
    <div class="sticky top-0 z-50 w-full px-4 sm:px-10 py-2 flex justify-between items-center bg-[rgba(0,0,0,0.5)]">
      <!-- 🗙 Close Preview Button + File Title with Icon -->
      <div class="flex items-center space-x-3">
        <!-- Close Button -->
        <div class="relative group">
          <button id="closePreview" class="rounded-full hover:bg-[rgba(255,250,250,0.2)] p-2 duration-200 cursor-pointer hover:scale-110 transition-transform">
            <img src="/assets/img/close-icon-white.png" alt="Close Preview" class="w-4 h-4" />
          </button>
          <div class="absolute top-10 left-5 mb-1 -translate-x-1/2 px-3 py-1 bg-gray-100 text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
            Close Preview
          </div>
        </div>

        <!-- Title with Icon -->
        <div class="preview-title text-white text-lg font-semibold truncate flex items-center space-x-2">
          <img id="fileTypeIcon" src="/assets/img/file-icons/file-icon.png" alt="File Type" class="w-4 h-4 sm:w-5 sm:h-5" />
          <span id="fileTitle" class="text-sm sm:text-md">Preview Title</span>
        </div>
      </div>

      <!-- 💻 Desktop Actions with Icons -->
      <div class="hidden sm:flex space-x-2">
        <!-- 💬 Comment Icon -->
        <div class="relative group">
          <button id="commentPreview" class="rounded-full p-2 hover:bg-[rgba(255,250,250,0.2)] duration-200 cursor-pointer hover:scale-110 transition-transform">
            <img src="/assets/img/comment-white.png" alt="Comment" class="w-7 h-7" />
          </button>
          <div class="absolute top-12 mb-1 left-5 -translate-x-1/2 px-3 py-1 bg-gray-100 text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
            Comment
          </div>
        </div>

        <!-- 🔗 Share Icon -->
        <div class="relative group">
          <button id="sharePreview" class="rounded-full p-2 hover:bg-[rgba(255,250,250,0.2)] duration-200 cursor-pointer hover:scale-110 transition-transform">
            <img src="/assets/img/share-white.png" alt="Share" class="w-5 h-5" />
          </button>
          <div class="absolute top-12 mb-1 left-5 -translate-x-1/2 px-3 py-1 bg-gray-100 text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
            Share
          </div>
        </div>

        <!-- ⬇ Download Icon -->
        <div class="relative group">
          <button id="downloadPreview" class="rounded-full p-2 hover:bg-[rgba(255,250,250,0.2)] text-white duration-200 cursor-pointer hover:scale-110 transition-transform">
            <img src="/assets/img/downloads-white.png" alt="Download" class="w-5 h-5" />
          </button>
          <div class="absolute top-12 mb-1 left-5 -translate-x-1/2 px-3 py-1 bg-gray-100 text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
            Download
          </div>
        </div>
      </div>

      <!-- 📱 Mobile Actions Toggle -->
      <div class="sm:hidden">
        <button id="mobileActionToggle" class=" p-2 rounded-full hover:scale-105 transition-transform">
          <img src="/assets/img/dots-white.png" alt="Actions" class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- 📱 Bottom Sheet Menu -->
    <div id="mobileActionMenu"
      class="transition-transform duration-200 ease-out will-change-transform fixed bottom-0 left-0 w-full h-1/2 bg-white rounded-t-xl shadow-md z-50 hidden flex-col justify-start px-4 py-6 space-y-2">
      <!-- 💬 Comment -->
      <button id="commentPreviewMobile" class="flex items-center w-full px-4 py-2 hover:bg-gray-100 text-gray-700 space-x-3">
        <img src="/assets/img/comment.png" alt="Comment" class="w-5 h-5" />
        <span class="text-sm font-medium">Comment</span>
      </button>

      <!-- 🔗 Share -->
      <button id="sharePreviewMobile" class="flex items-center w-full px-4 py-2 hover:bg-gray-100 text-gray-700 space-x-3">
        <img src="/assets/img/share-icon.png" alt="Share" class="w-5 h-5" />
        <span class="text-sm font-medium">Share</span>
      </button>

      <!-- ⬇ Download -->
      <button id="downloadPreviewMobile" class="flex items-center w-full px-4 py-2 hover:bg-gray-100 text-gray-700 space-x-3">
        <img src="/assets/img/download-icon.png" alt="Download" class="w-5 h-5" />
        <span class="text-sm font-medium">Download</span>
      </button>
    </div>

    <!-- 🔄 Scrollable Preview Content -->
    <div class="flex-1 relative z-20 px-4 overflow-auto">
      <div class="preview-content w-full max-w-6xl mx-auto min-h-full flex justify-center items-center">
        <!-- Injected content goes here -->
      </div>
    </div>

    <!-- 📄 Navigation Container -->
    <div id="pdf-navigation" class="fixed bottom-0 left-0 w-full  px-4 py-2 z-40 hidden">
      <!-- Navigation buttons will be injected here -->
    </div>

    <!-- ◀ Fixed Navigation Buttons ▶ -->
    <!-- Prev Button -->
    <div class="fixed inset-y-0 left-5 w-12 z-40 flex items-center justify-center pointer-events-auto">
      <button id="prevPreview" class="p-4 bg-black rounded-full shadow hover:bg-[rgba(255,250,250,0.2)] transition duration-200 cursor-pointer">
        <img src="/assets/img/left-arrow-white.png" alt="Previous" class="w-3 h-3" />
      </button>
    </div>

    <!-- Next Button -->
    <div class="fixed inset-y-0 right-5 w-12 z-40 flex items-center justify-center pointer-events-auto">
      <button id="nextPreview" class="p-4 bg-black rounded-full shadow hover:bg-[rgba(255,250,250,0.2)] transition duration-200 cursor-pointer">
        <img src="/assets/img/right-arrow-white.png" alt="Next" class="w-3 h-3" />
      </button>
    </div>
  </div>

  <!-- ℹ️ File Info Modal -->
  <div id="file-info-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
      <h2 class="info-title text-md sm:text-lg font-semibold mb-4 text-emerald-700">File Info</h2>
      <div class="info-content mb-4"></div>
      <div class="flex justify-end">
        <button id="closeInfo" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Close</button>
      </div>
    </div>
  </div>

  <!-- 💬 Comment Modal -->
  <div id="commentModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
      <h2 class="text-md sm:text-lg font-semibold mb-4 text-emerald-700">Add Comment</h2>
      <form action="/controllers/file-manager/comment.php" method="POST">
        <input type="hidden" name="file_id" id="comment-file-id">
        <textarea name="comment" rows="8" required class="w-full border rounded px-3 py-2 mb-4 resize-none" placeholder="Write your comment..."></textarea>
        <div class="flex justify-end gap-2">
          <button type="button" id="cancelComment" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
          <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Post</button>
        </div>
      </form>
    </div>
  </div>

<!-- 🔐 Manage Access Modal -->
<div id="manageAccessModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200 will-change-opacity">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-2xl z-10 border border-emerald-500 max-h-[90vh] overflow-y-auto">
    <h2 class="text-md sm:text-lg font-semibold mb-4 text-emerald-700">Manage Access</h2>
    <form id="manageAccessForm" autocomplete="off">
      <input type="hidden" name="file_id" id="manage-access-file-id">

      <!-- 👥 Current Access List -->
      <div class="mb-2">
        <label class="block text-sm sm:text-lg font-medium text-gray-700 mb-4">People with access</label>
        <div id="accessList" class="space-y-4 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
          <!-- Access rows or fallback will be injected here -->
        </div>
      </div>

      <!-- 🧭 Action Buttons -->
      <div class="flex justify-end gap-2 mt-4">
        <button type="button" id="cancelManageAccess" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm">Cancel</button>
        <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm">Save Changes</button>
      </div>
    </form>
  </div>
</div>
  <!-- ✅ Floating Dropdown (outside all layout containers) -->
  <div id="permissionDropdown"
    class="fixed hidden z-[9999] bg-white shadow-2xl rounded-md text-sm w-40 transition font-semibold  transform opacity duration-200 scale-0 opacity-0 origin-center">
  </div>

  <!-- 🔗 Share Modal -->
  <div id="shareModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
      <h2 class="text-md sm:text-lg font-semibold mb-4 text-emerald-700">Share File</h2>
      <form id="shareForm" autocomplete="off">
        <input type="hidden" name="file_id" id="share-file-id">
        <input type="hidden" id="shareOwnerEmail" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">

        <!-- 📧 Email + Avatar + Dropdown -->
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

        <!-- 🔐 Permission Selector -->
        <select name="permission" required class="w-full border rounded px-3 py-2 mb-4">
          <option value="read">Read</option>
          <option value="write">Write</option>
          <option value="share">Share</option>
          <option value="delete">Delete</option>
        </select>

        <!-- 📘 Permission Description -->
        <div class="flex items-center gap-2 mb-4">
          <img src="/assets/img/info-icon.png" alt="Add User Icon" class="w-3 h-3" />
          <small id="accessLevelDescription" class="text-xs text-gray-500"></small>
        </div>


        <!-- 🧭 Action Buttons -->
        <div class="flex justify-end gap-2">
          <button type="button" id="cancelShare"
            class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
          <button type="submit"
            class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Share</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 📤 Upload Modal -->
  <div id="uploadModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
      <h2 class="text-xl sm:text-2xl mb-4">Choose a file to upload</h2>
      <form id="uploadForm" enctype="multipart/form-data">
        <div class="relative mb-4">
          <!-- Unified clickable area -->
          <label for="uploadInput" class="flex items-center border rounded bg-white cursor-pointer text-sm hover:bg-emerald-100 text-emerald-700">
            <span class="font-medium bg-emerald-800 py-2 px-2 text-white">Browse</span>
            <span id="fileName" class="text-gray-500 pl-2">No file chosen</span>
          </label>
          <input type="file" id="uploadInput" name="file" required class="hidden">
          <div id="previewContainer" class="mt-2"></div>
        </div>
        <input type="hidden" name="folder_id" value="<?= htmlspecialchars($folderId) ?>">
        <div class="flex justify-end gap-2">
          <button type="button" id="cancelUploadBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
          <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Upload</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 📂 Create Folder Modal -->
  <div id="createFolderModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
      <h2 class="text-xl sm:text-2xl mb-4">Create New Folder</h2>
      <form id="createFolderForm" action="/controllers/file-manager/create-folder.php" method="POST">
        <input type="text" name="folder_name" placeholder="Folder name" required class="block w-full mb-4 border rounded px-3 py-2">
        <input type="hidden" name="parent_id" value="<?= htmlspecialchars($folderId) ?>">
        <div class="flex justify-end gap-2">
          <button type="button" id="cancelCreateFolderBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
          <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Create</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ✏️ Rename Modal -->
  <div id="renameModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
      <h2 class="text-md sm:text-lg font-semibold mb-4 text-emerald-700">Rename Item</h2>
      <p class="text-sm text-gray-700 mb-4">
        Renaming <span class="font-semibold text-gray-800" id="rename-item-name">this item</span>.
      </p>
      <input type="hidden" id="rename-item-id">
      <input type="text" id="rename-input" class="w-full border border-gray-300 rounded px-3 py-2 text-sm mb-6" placeholder="Enter new name">
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelRename" class="px-3 py-1 text-emerald-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="button" id="confirmRenameBtn" class="px-3 py-1 text-white bg-emerald-600 rounded hover:bg-emerald-700 text-sm cursor-pointer">Rename</button>
      </div>
    </div>
  </div>

  <!-- 🗑️ Delete Confirmation Modal -->
  <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
      <h2 class="text-md sm:text-lg font-semibold mb-4 text-emerald-700">Confirm Move to Trash</h2>
      <p class="text-sm text-gray-700 mb-6">
        <span class="font-semibold text-gray-800" id="delete-item-name">This item</span>
        will be deleted forever and can't be restored after 30 days.
      </p>
      <input type="hidden" id="delete-item-id">
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelDelete" class="px-3 py-1 text-emerald-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="px-3 py-1 text-white bg-emerald-600 rounded hover:bg-emerald-700 text-sm cursor-pointer">Move to Trash</button>
      </div>
    </div>
  </div>

  <!-- 🧹 Empty Trash Confirmation Modal -->
  <div id="emptyTrashModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-red-500">
      <h2 class="text-md sm:text-lg font-semibold mb-4 text-red-700">Confirm Empty Trash</h2>
      <p class="text-sm text-gray-700 mb-6">
        All items in trash will be <span class="font-semibold text-gray-800">permanently deleted</span> and cannot be recovered.
      </p>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelEmptyTrash" class="px-3 py-1 text-red-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="button" id="confirmEmptyTrashBtn" class="px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700 text-sm cursor-pointer">Empty Trash</button>
      </div>
    </div>
  </div>

  <!-- ♻️ Restore Confirmation Modal -->
  <div id="restoreModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-emerald-500">
      <h2 class="text-md sm:text-lg font-semibold mb-4 text-emerald-700">Confirm Restore</h2>
      <p class="text-sm text-gray-700 mb-6">Are you sure you want to restore this item?</p>
      <input type="hidden" id="restore-item-id">
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelRestore" class="px-3 py-1 text-emerald-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="button" id="confirmRestoreBtn" class="px-3 py-1 text-white bg-emerald-600 rounded hover:bg-emerald-700 text-sm cursor-pointer">Restore</button>
      </div>
    </div>
  </div>

  <!-- 🗑️ Permanent Delete Confirmation Modal -->
  <div id="permanentDeleteModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    <div class="relative bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md z-10 border border-red-500">
      <h2 class="text-md sm:text-lg font-semibold mb-4 text-red-700">Confirm Permanent Delete</h2>
      <p class="text-sm text-gray-700 mb-6">This action cannot be undone. Are you sure?</p>
      <input type="hidden" id="permanent-delete-item-id">
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelPermanentDelete" class="px-3 py-1 text-red-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="button" id="confirmPermanentDeleteBtn" class="px-3 py-1 text-white bg-red-600 rounded hover:bg-red-700 text-sm cursor-pointer">Delete</button>
      </div>
    </div>
  </div>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>