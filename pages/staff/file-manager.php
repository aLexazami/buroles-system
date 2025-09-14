<?php
session_start();

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/folder-utils.php';
require_once __DIR__ . '/../../helpers/file-utils.php';

$userId         = $_SESSION['user_id'] ?? '';
$activeRoleId   = $_SESSION['active_role_id'] ?? '';
$originalRoleId = $_SESSION['original_role_id'] ?? '';
$targetId       = $_GET['user_id'] ?? $userId;
$currentPath    = sanitizePath($_GET['path'] ?? '');
$sortBy         = $_GET['sort'] ?? 'name';

$isElevatedViewer   = in_array($originalRoleId, [2, 99]);
$isSwitchedToStaff  = $activeRoleId === 1;
$showMultiUserView  = $isElevatedViewer && $isSwitchedToStaff;

function canManageFolder(string $userId, string $targetId, int $activeRoleId, int $originalRoleId): bool {
  if (in_array($originalRoleId, [2, 99])) return true;
  return $activeRoleId === 1 && $userId === $targetId;
}

if (!canManageFolder($userId, $targetId, $activeRoleId, $originalRoleId)) {
  setFlash('error', 'Access denied. You do not have permission to manage this folder.');
  header("Location: file-manager.php?user_id=$userId");
  exit;
}

// ✅ Resolve upload base using role-first folder logic
$uploadBase = getUploadBaseByRoleUser($activeRoleId, $targetId);
$fullPath   = $uploadBase . '/' . $currentPath;

// ✅ Handle folder creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder_name'])) {
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

// ✅ Get folder contents
$contents = listFolderItems($fullPath);
$folders  = $contents['folders'];
$files    = $contents['files'];

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
      <?php
      $isElevatedViewer = in_array($_SESSION['original_role_id'], [2, 99]);
      $isSwitchedToStaff = $_SESSION['active_role_id'] == 1;
      $showMultiUserView = $isElevatedViewer && $isSwitchedToStaff;

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