<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php'; // showFlash()
require_once __DIR__ . '/../../helpers/head.php'; //renderHead()
require_once __DIR__ . '/../../helpers/file-utils.php'; //getFilesForView()

$userId = $_SESSION['user_id'];
$view = $_GET['view'] ?? 'my-files'; // 'shared-with-me', 'shared-by-me', 'my-files'
$folderId = $_GET['folder'] ?? null;

$files = getFilesForView($userId, $view, $folderId);

renderHead('Staff');
?>

<body data-folder-id="<?= htmlspecialchars($folderId ?? '') ?>" class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr] min-h-screen">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-gray-800">📁 File Manager</h1>

        <!-- Tabs -->
        <div class="flex space-x-4 border-b pb-2">
          <a href="?view=my-files" class="<?= $view === 'my-files' ? 'border-b-2 border-blue-600 text-blue-600 font-medium' : 'text-gray-600 hover:text-blue-600' ?>">My Files</a>
          <a href="?view=shared-with-me" class="<?= $view === 'shared-with-me' ? 'border-b-2 border-blue-600 text-blue-600 font-medium' : 'text-gray-600 hover:text-blue-600' ?>">Shared with Me</a>
          <a href="?view=shared-by-me" class="<?= $view === 'shared-by-me' ? 'border-b-2 border-blue-600 text-blue-600 font-medium' : 'text-gray-600 hover:text-blue-600' ?>">Shared by Me</a>
        </div>

        <!-- Breadcrumb -->
        <div id="breadcrumb" class="flex flex-wrap items-center text-sm text-gray-600 space-x-1"></div>

        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4">
          <?php if ($view === 'my-files'): ?>
            <div class="flex gap-2">
              <button onclick="openUploadModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">📤 Upload</button>
              <button onclick="openCreateFolderModal()" class="bg-gray-100 text-gray-800 px-4 py-2 rounded hover:bg-gray-200">📂 New Folder</button>
            </div>
          <?php endif; ?>
          <input type="text" placeholder="Search files..." class="w-full sm:w-64 px-4 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
        </div>


        <!-- ✅ Dynamic File List Container -->
        <div id="file-list" class="divide-y divide-gray-200 mt-4"></div>

      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>

  <!-- 📁 File Preview Modal -->
  <div id="preview-overlay" class="fixed inset-0 z-50 hidden w-full h-full bg-[rgba(0,0,0,0.6)] backdrop-blur-sm flex flex-col ">

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
          <img id="fileTypeIcon" src="/assets/img/file-icon.png" alt="File Type" class="w-4 h-4 sm:w-5 sm:h-5" />
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
    <div id="mobileActionMenu" class="fixed bottom-0 left-0 w-full h-1/2 bg-white rounded-t-xl shadow-lg z-40 hidden flex flex-col justify-start px-4 py-6 space-y-4">
      <!-- 💬 Comment -->
      <button id="commentPreviewMobile" class="flex items-center w-full px-4 py-2 hover:bg-gray-100 text-gray-700 space-x-3">
        <img src="/assets/img/comment-icon.png" alt="Comment" class="w-5 h-5" />
        <span class="text-sm font-medium">Comment</span>
      </button>

      <!-- 🔗 Share -->
      <button id="sharePreviewMobile" class="flex items-center w-full px-4 py-2 hover:bg-gray-100 text-gray-700 space-x-3">
        <img src="/assets/img/share-icon.png" alt="Share" class="w-5 h-5" />
        <span class="text-sm font-medium">Share</span>
      </button>

      <!-- ⬇ Download -->
      <button id="downloadPreviewMobile" class="flex items-center w-full px-4 py-2 hover:bg-gray-100 text-green-600 space-x-3">
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
  <div id="file-info-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="info-title text-xl font-semibold mb-4">File Info</h2>
      <div class="info-content mb-4"></div>
      <div class="flex justify-end">
        <button id="closeInfo" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Close</button>
      </div>
    </div>
  </div>

  <!-- 💬 Comment Modal -->
  <div id="commentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="text-xl font-semibold mb-4">Add Comment</h2>
      <form action="/controllers/file-manager/comment.php" method="POST">
        <input type="hidden" name="file_id" id="comment-file-id">
        <textarea name="comment" rows="4" required class="w-full border rounded px-3 py-2 mb-4" placeholder="Write your comment..."></textarea>
        <div class="flex justify-end gap-2">
          <button type="button" id="cancelComment" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Post</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 🔗 Share Modal -->
  <div id="shareModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="text-xl font-semibold mb-4">Share File</h2>
      <form action="/controllers/file-manager/share.php" method="POST">
        <input type="hidden" name="file_id" id="share-file-id">
        <input type="email" name="recipient_email" required class="w-full border rounded px-3 py-2 mb-4" placeholder="Recipient's email">
        <select name="permission" required class="w-full border rounded px-3 py-2 mb-4">
          <option value="read">Read</option>
          <option value="write">Write</option>
          <option value="share">Share</option>
          <option value="delete">Delete</option>
        </select>
        <div class="flex justify-end gap-2">
          <button type="button" id="cancelShare" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Share</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 📤 Upload Modal -->
  <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="text-xl font-semibold mb-4">Upload File</h2>
      <form action="/controllers/file-manager/upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required class="block w-full mb-4 border rounded px-3 py-2">
        <input type="hidden" name="folder_id" value="<?= htmlspecialchars($folderId) ?>">
        <div class="flex justify-end gap-2">
          <button type="button" onclick="closeUploadModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 📂 Create Folder Modal -->
  <div id="createFolderModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="text-xl font-semibold mb-4">Create New Folder</h2>
      <form action="/controllers/file-manager/create-folder.php" method="POST">
        <input type="text" name="folder_name" placeholder="Folder name" required class="block w-full mb-4 border rounded px-3 py-2">
        <input type="hidden" name="parent_id" value="<?= htmlspecialchars($folderId) ?>">
        <div class="flex justify-end gap-2">
          <button type="button" onclick="closeCreateFolderModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create</button>
        </div>
      </form>
    </div>
  </div>
  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
  <script>
    function openUploadModal() {
      document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
      document.getElementById('uploadModal').classList.add('hidden');
    }

    function openCreateFolderModal() {
      document.getElementById('createFolderModal').classList.remove('hidden');
    }

    function closeCreateFolderModal() {
      document.getElementById('createFolderModal').classList.add('hidden');
    }
  </script>
</body>

</html>