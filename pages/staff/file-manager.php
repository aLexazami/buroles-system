<?php
session_start();

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/folder-utils.php';
require_once __DIR__ . '/../../helpers/file-utils.php';
require_once __DIR__ . '/../../helpers/logging-utils.php';
require_once __DIR__ . '/../../helpers/head.php';
require_once __DIR__ . '/../../helpers/access-utils.php';

$userId         = (int)($_SESSION['user_id'] ?? 0);
$activeRoleId   = (int)($_SESSION['active_role_id'] ?? 0);
$originalRoleId = $_SESSION['original_role_id'] ?? '';
$targetId       = (int)($_GET['user_id'] ?? $userId);

// ✅ Normalize scoped path to relative
$rawPath = sanitizePath($_GET['path'] ?? '');
$expectedPrefix = "uploads/staff/$targetId/";
$currentPath = str_starts_with($rawPath, $expectedPrefix)
  ? substr($rawPath, strlen($expectedPrefix))
  : $rawPath;

$sortBy         = $_GET['sort'] ?? 'name';
$isSharedView   = isset($_GET['shared']) && $_GET['shared'] === '1';

$isElevatedViewer  = in_array($originalRoleId, [2, 99]);
$isSwitchedToStaff = $activeRoleId === 1;
$showMultiUserView = $isElevatedViewer && $isSwitchedToStaff;

function canManageFolder(int $userId, int $targetId, int $activeRoleId, int $originalRoleId): bool
{
  return in_array($originalRoleId, [2, 99]) || ($activeRoleId === 1 && $userId === $targetId);
}

// 🔐 Shared access view
if ($isSharedView) {
  $scopedPath = "uploads/staff/$targetId/" . ltrim($currentPath, '/');

  $resolved = resolveItemAccess($pdo, $userId, 'folder', $scopedPath, $targetId);
  if (!$resolved || !$resolved['accessLevel']) {
    setFlash('error', 'Access denied. You do not have access to this shared folder.');
    header("Location: shared-file.php");
    exit;
  }

  $itemId      = $resolved['itemId'];
  $hasAccess   = $resolved['accessLevel'];
  $accessLabel = $resolved['accessLabel'];
  $fullPath    = "uploads/staff/$targetId/$currentPath";
  $ownerId     = $targetId;

  $stmt = $pdo->prepare("
    SELECT CONCAT_WS(' ', first_name, middle_name, last_name) AS full_name
    FROM users
    WHERE id = ?
  ");
  $stmt->execute([$targetId]);
  $sharedByName = $stmt->fetchColumn() ?: 'Unknown';

  $uploadBase = getUploadBasePathOnly('1', $targetId);
  $activeRoleId = 1; // force staff role
} else {
  if (!canManageFolder($userId, $targetId, $activeRoleId, $originalRoleId)) {
    setFlash('error', 'Access denied. You do not have permission to manage this folder.');
    header("Location: file-manager.php?user_id=$userId");
    exit;
  }

  $uploadBase   = getUploadBasePathOnly($activeRoleId, $targetId);
  $accessLabel  = 'Owner';
  $ownerId      = $targetId;
  $hasAccess    = 'owner';
}

$fullPath = $uploadBase . '/' . $currentPath;

// 📂 Folder creation
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

usort($folders, fn($a, $b) =>
  $sortBy === 'modified'
    ? strtotime($b['modified']) <=> strtotime($a['modified'])
    : strcasecmp($a['name'], $b['name'])
);

usort($files, fn($a, $b) =>
  $sortBy === 'modified'
    ? strtotime($b['modified']) <=> strtotime($a['modified'])
    : strcasecmp($a['name'], $b['name'])
);

renderHead('Staff');
?>

<body data-current-path="<?= htmlspecialchars($currentPath) ?>" class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr] min-h-screen">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <?php if ($isSharedView): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded-md shadow-sm">
          <div class="flex items-center justify-between gap-5">
            <p class="text-sm">
              📁 You’re viewing a folder shared by <strong><?= htmlspecialchars($sharedByName) ?></strong>.
            </p>
            <a href="/pages/staff/shared-file.php"
              aria-label="Back to Shared Files"
              class="inline-flex items-center gap-1 text-sm font-medium text-yellow-700 hover:text-yellow-900 hover:underline transition">
              <img src="/assets/img/back-icon.png" alt="Back" class="w-4 h-4" />
              <span class="hidden sm:inline">Back to Shared Files</span>
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php
      if ($showMultiUserView && !isset($_GET['user_id'])) {
        include('../partials/admin-staff-overview.php');
      } else {
        // ✅ Pass full access context to staff-file-ui.php
        $GLOBALS['accessLevel']   = $hasAccess;
        $GLOBALS['isSharedView']  = $isSharedView;
        $GLOBALS['targetId']      = $targetId;
        $GLOBALS['currentPath']   = $currentPath;
        $GLOBALS['ownerId']       = $ownerId;

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