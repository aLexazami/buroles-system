<?php
session_start();

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';
require_once __DIR__ . '/../../helpers/path.php';
require_once __DIR__ . '/../../helpers/folder-utils.php';
require_once __DIR__ . '/../../helpers/file-utils.php';

$viewerId    = $_SESSION['user_id'] ?? '';
$activeRole  = $_SESSION['active_role_id'] ?? '';
$targetId = $_GET['user_id'] ?? $_SESSION['user_id'];
$currentPath = sanitizePath($_GET['path'] ?? '');
$sortBy      = $_GET['sort'] ?? 'name'; // 'name' or 'modified'


$isElevatedViewer = in_array($_SESSION['original_role_id'], [2, 99]);
$isSwitchedToStaff = $_SESSION['active_role_id'] == 1;
$showMultiUserView = $isElevatedViewer && $isSwitchedToStaff;



function canManageFolder($viewerId, $targetId, $activeRole, $originalRoleId): bool
{
  if (in_array($originalRoleId, [2, 99])) return true; // Admin/Superadmin
  return $activeRole == 1 && $viewerId == $targetId;   // Staff can manage their own
}

if (!canManageFolder($viewerId, $targetId, $activeRole, $_SESSION['original_role_id'])) {
  setFlash('error', 'Access denied. You do not have permission to manage this folder.');
  header("Location: file-manager.php?user_id=$viewerId");
  exit;
}

// ✅ Resolve upload base using role-first folder logic
$uploadBase = getUploadBaseByRoleUser('1', $targetId);

// ✅ Resolve full folder path
$fullPath = $uploadBase . '/' . $currentPath;

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