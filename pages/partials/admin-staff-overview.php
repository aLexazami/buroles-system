<?php
require_once __DIR__ . '/../../helpers/folder-utils.php';

$stmt = $pdo->query("SELECT id, first_name, last_name FROM users WHERE role_id = 1 ORDER BY last_name");
$staffUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5">
  <img src="/assets/img/archive-user.png" class="w-5 h-5" alt="Archive icon">
  <h1 class="font-bold text-lg">Manage File</h1>
</div>

<div>
  <h2 class="text-xl font-semibold mb-4">Manage Staff Files</h2>
</div>

<div class="bg-white shadow-2xl rounded-md p-4">
  <!-- Search Staff -->
  <div class="flex items-center gap-2 mb-4">
    <input
      type="text"
      id="staffSearch"
      placeholder="Search"
      class="border px-3 py-2 rounded w-full max-w-md" />
    <button
      id="clearStaffSearch"
      class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm">
      Clear
    </button>
  </div>

  <div class="flex flex-col divide-y divide-black-200">
    <?php foreach ($staffUsers as $index => $user): ?>
      <?php
      $userPath = getUploadBasePathOnly('1', $user['id']);
      $fileCount = function_exists('countUserFiles') ? countUserFiles($userPath) : 0;
      ?>
      <a href="file-manager.php?user_id=<?= $user['id'] ?>"
        class="staff-item flex gap-4 items-center w-full p-2 hover:bg-emerald-50"
        data-name="<?= strtolower($user['first_name'] . ' ' . $user['last_name']) ?>">
        <span class="font-medium"><?= $index + 1 ?></span>
        <span><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
        <span class="ml-auto text-sm text-gray-500"><?= $fileCount ?> file<?= $fileCount !== 1 ? 's' : '' ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>