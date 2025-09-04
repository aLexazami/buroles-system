<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/flash.php';

$userId = $_SESSION['user_id'];
$basePath = __DIR__ . "/../../uploads/staff/$userId/";

if (!file_exists($basePath)) {
    mkdir($basePath, 0755, true);
}
$folders = array_filter(glob($basePath . '*'), 'is_dir');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Feedback Files</title>
  <meta name="robots" content="noindex" />
  <link href="/src/styles.css" rel="stylesheet" />
</head>
<body class="bg-gray-200 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>

  <main class="grid grid-cols-[248px_1fr] min-h-screen">
    <?php include('../../includes/side-nav-staff.php'); ?>

    <section class="m-4">
      <h1 class="text-2xl font-bold mb-4"> Manage Feedback Files</h1>

      <?php showFlash(); ?>

      <!-- Create Folder -->
      <form action="/controllers/create-folder.php" method="POST" class="mb-6 space-y-2">
        <input type="text" name="folder_name" placeholder="New Folder Name" required
               class="border p-2 rounded w-full">
        <button type="submit" class="bg-emerald-500 text-white px-4 py-2 rounded">Create Folder</button>
      </form>

      <!-- Upload File -->
      <form action="/controllers/upload-file.php" method="POST" enctype="multipart/form-data" class="space-y-2">
        <select name="target_folder" required class="border p-2 rounded w-full">
          <option value="">Select Folder</option>
          <?php foreach ($folders as $folder): ?>
            <?php $folderName = basename($folder); ?>
            <option value="<?= htmlspecialchars($folderName) ?>"><?= htmlspecialchars($folderName) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="file" name="file" required class="border p-2 rounded w-full">
        <button type="submit" class="bg-emerald-500 text-white px-4 py-2 rounded">Upload File</button>
      </form>

      <!-- Folder List -->
      <div class="mt-8">
        <h2 class="text-xl font-semibold mb-2">Existing Folders</h2>
        <ul class="list-disc pl-5">
          <?php if (empty($folders)): ?>
            <li class="text-gray-500">No folders yet.</li>
          <?php else: ?>
            <?php foreach ($folders as $folder): ?>
              <li><?= htmlspecialchars(basename($folder)) ?></li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
</body>
</html>