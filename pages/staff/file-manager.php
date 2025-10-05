<?php
session_start();

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/folder-utils.php';
require_once __DIR__ . '/../../helpers/file-utils.php';
require_once __DIR__ . '/../../helpers/head.php';

$userId         = (int)($_SESSION['user_id'] ?? 0);
$activeRoleId   = (int)($_SESSION['active_role_id'] ?? 0);
$originalRoleId = $_SESSION['original_role_id'] ?? '';
$targetId       = (int)($_GET['user_id'] ?? $userId);
$currentPath    = sanitizePath($_GET['path'] ?? '');
$sortBy         = $_GET['sort'] ?? 'name';
$isSharedView   = isset($_GET['shared']) && $_GET['shared'] === '1';

$isElevatedViewer  = in_array($originalRoleId, [2, 99]);
$isSwitchedToStaff = $activeRoleId === 1;
$showMultiUserView = $isElevatedViewer && $isSwitchedToStaff;

function canManageFolder(int $userId, int $targetId, int $activeRoleId, int $originalRoleId): bool
{
  return in_array($originalRoleId, [2, 99]) || ($activeRoleId === 1 && $userId === $targetId);
}

// 🔐 Shared access validation
if ($isSharedView) {
  require_once __DIR__ . '/../../helpers/sharing-utils.php';

  $itemId = getItemIdByPath($pdo, $currentPath, $targetId, true);
  $hasAccess = $itemId ? getSharedAccess($pdo, $targetId, $userId, $itemId) : false;

  if (!$hasAccess) {
    setFlash('error', 'Access denied. You do not have access to this shared folder.');
    header("Location: shared-file.php");
    exit;
  }

  // 🧠 Get sharer name
  $stmt = $pdo->prepare("
    SELECT CONCAT_WS(' ', first_name, middle_name, last_name) AS full_name
    FROM users
    WHERE id = ?
  ");
  $stmt->execute([$targetId]);
  $sharedByName = $stmt->fetchColumn() ?: 'Unknown';
}

// 🔒 Role-based access check (skip if shared view)
if (!$isSharedView && !canManageFolder($userId, $targetId, $activeRoleId, $originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to manage this folder.');
  header("Location: file-manager.php?user_id=$userId");
  exit;
}

// 📁 Resolve upload path
$uploadBase = getUploadBasePathOnly($isSharedView ? 1 : $activeRoleId, $targetId);
$fullPath   = $uploadBase . '/' . $currentPath;

// 📂 Folder creation (POST only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder_name'])) {
  ensureUploadBaseExists($activeRoleId, $targetId);

  $newFolderName = sanitizeSegment($_POST['folder_name']);
  $newFolderPath = $currentPath !== '' ? $currentPath . '/' . $newFolderName : $newFolderName;

  if (createFolder($uploadBase, $newFolderPath)) {
    setFlash('success', "Folder '$newFolderName' created.");
  } else {
    setFlash('error', "Failed to create folder '$newFolderName'.");
  }

  header("Location: file-manager.php?user_id=$targetId&path=" . urlencode($currentPath));
  exit;
}

// 📦 Get folder contents
$contents = listFolderItems($fullPath);
$folders  = $contents['folders'];
$files    = $contents['files'];

// 🔃 Sort folders
usort($folders, fn($a, $b) =>
  $sortBy === 'modified'
    ? strtotime($b['modified']) <=> strtotime($a['modified'])
    : strcasecmp($a['name'], $b['name'])
);

// 🔃 Sort files
usort($files, fn($a, $b) =>
  $sortBy === 'modified'
    ? strtotime($b['modified']) <=> strtotime($a['modified'])
    : strcasecmp($a['name'], $b['name'])
);

renderHead('Staff');
?>

<body data-current-path="<?= htmlspecialchars($currentPath) ?>" class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[248px_1fr] min-h-screen">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <?php if ($isSharedView): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded-md shadow-sm">
          <div class="flex items-center justify-between">
            <p class="text-sm">
              📁 You’re viewing a folder shared by <strong><?= htmlspecialchars($sharedByName) ?></strong>.
            </p>
            <a href="/pages/staff/shared-file.php"
              class="inline-flex items-center gap-1 text-sm font-medium text-yellow-700 hover:text-yellow-900 hover:underline transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-yellow-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Back to Shared Files
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php
      if ($showMultiUserView && !isset($_GET['user_id'])) {
        include('../partials/admin-staff-overview.php');
      } else {
        include('../partials/staff-file-ui.php');
      }
      ?>

    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>

</html>