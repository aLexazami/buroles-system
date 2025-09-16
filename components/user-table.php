<?php if (!isset($users) || !is_array($users) || empty($users)): ?>
  <p class="text-red-600">No user data available.</p>
<?php else: ?>
  <!-- Search Bar -->
  <div class="flex items-center gap-2 mb-4">
    <input
      type="text"
      id="userSearch"
      placeholder=" Search"
      class="px-4 py-2 border rounded w-full max-w-md" />
    <button
      id="clearSearch"
      class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm">
      Clear
    </button>
  </div>

  <!-- User Table -->
  <table class="min-w-full table-auto border border-gray-200">
    <thead class="bg-emerald-600 text-white">
      <tr>
        <th scope="col" class="px-4 py-2 text-center">ID</th>
        <th scope="col" class="px-4 py-2 text-left">Full Name</th>
        <th scope="col" class="px-4 py-2 text-left">Username</th>
        <th scope="col" class="px-4 py-2 text-left">Email</th>
        <th scope="col" class="px-4 py-2 text-left">Role</th>
        <th scope="col" class="px-4 py-2 text-left">Password Status</th>
        <?php if ($showActions ?? true): ?>
          <th scope="col" class="px-4 py-2 text-left">Actions</th>
        <?php endif; ?>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($users as $user): ?>
        <tr class="border-b hover:bg-emerald-50">
          <!-- User ID -->
          <td class="px-4 py-2 text-red-500 text-center font-medium"><?= htmlspecialchars($user['id']) ?></td>

          <!--  Full Name -->
          <td class="px-4 py-2">
            <?= htmlspecialchars(trim($user['last_name'] . ', ' . $user['first_name'] . ' ' . ($user['middle_name'] ?? ''))) ?>
          </td>

          <!--  Username -->
          <td class="px-4 py-2"><?= htmlspecialchars($user['username']) ?></td>

          <!--  Email -->
          <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>

          <!-- Role -->
          <td class="px-4 py-2">
            <span class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded text-xs">
              <?= htmlspecialchars($user['role_name']) ?>
            </span>
          </td>

          <!-- Password Status -->
          <td class="px-4 py-2">
            <?php if ($user['must_change_password']): ?>
              <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Must Change</span>
            <?php else: ?>
              <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">OK</span>
            <?php endif; ?>
          </td>

          <!-- Actions -->
          <?php if ($showActions ?? true): ?>
            <td class="px-4 py-2">
              <div class="flex gap-2 flex-wrap items-center">
                <?php if (!empty($user['is_archived'])): ?>
                  <a href="#" data-restore-user="<?= $user['id'] ?>" class="text-green-600 hover:underline text-sm">Restore</a>
                  <span class="text-yellow-400 text-sm">📦 Archived</span>
                <?php else: ?>
                  <a href="/pages/super-admin/edit-user.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:underline text-sm">Edit</a>
                  <a href="#" data-archive-user="<?= $user['id'] ?>" class="text-yellow-600 hover:underline text-sm">Archive</a>
                  <a href="#" data-delete-user="<?= $user['id'] ?>" class="text-red-600 hover:underline text-sm">Delete</a>
                  <?php if ($user['is_locked'] == 1): ?>
                  <a href="#" data-unlock-user="<?= $user['id'] ?>" class="text-red-700 hover:underline text-sm">🔓 Unlock</a>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>