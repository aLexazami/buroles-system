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

  <?php $isSuperAdmin = ($_SESSION['role_id'] ?? 0) === 99; ?>

  <!-- User Table -->
  <table class="min-w-full table-auto border-transparent">
    <thead class="bg-emerald-600 text-white">
      <tr>
        <th scope="col" class="px-4 py-2 text-left">ID</th>
        <th scope="col" class="px-4 py-2 text-left">Full Name</th>
        <th scope="col" class="px-4 py-2 text-left">Username</th>
        <th scope="col" class="px-4 py-2 text-left">Email</th>
        <th scope="col" class="px-4 py-2 text-left">Role</th>
        <th scope="col" class="px-4 py-2 text-center">Password Status</th>
        <th scope="col" class="px-4 py-2 text-right"></th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($users as $user): ?>
        <tr class="  border-y border-gray-300 hover:bg-emerald-50">
          <!-- User ID -->
          <td class="px-4 py-2 text-red-500 text-left font-medium"><?= htmlspecialchars($user['id']) ?></td>

          <!-- Full Name -->
          <td class="px-4 py-2">
            <?= htmlspecialchars(trim($user['last_name'] . ', ' . $user['first_name'] . ' ' . ($user['middle_name'] ?? ''))) ?>
          </td>

          <!-- Username -->
          <td class="px-4 py-2"><?= htmlspecialchars($user['username']) ?></td>

          <!-- Email -->
          <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>

          <!-- Role -->
          <td class="px-4 py-2">
            <span class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded text-xs">
              <?= htmlspecialchars($user['role_name']) ?>
            </span>
          </td>

          <!-- Password Status -->
          <td class="px-4 py-2 text-center">
            <?php if ($user['must_change_password']): ?>
              <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Must Change</span>
            <?php else: ?>
              <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">OK</span>
            <?php endif; ?>
          </td>

          <!-- Unified Actions Column -->
          <?php if ($showActions ?? true): ?>
            <td class="px-4 py-2 text-right">
              <div class="relative inline-block">
                <button class="menu-toggle p-2 cursor-pointer hover:bg-emerald-300 rounded-full  focus:outline-none"
                  data-target="menu-<?= $user['id'] ?>" aria-haspopup="true" aria-expanded="false">
                  <img src="/assets/img/dots-icon.png" alt="Menu" class="w-5 h-5">
                </button>

                <div id="menu-<?= $user['id'] ?>" class="dropdown-menu hidden absolute top-[-50px] z-10 right-8 w-52 bg-white rounded shadow-lg transition ease-out duration-150">
                  <ul class=" text-sm text-gray-700 text-left font-semibold">
                    <?php if ($isSuperAdmin): ?>
                      <li>
                        <a href="/pages/super-admin/manage-password.php?id=<?= $user['id'] ?>"
                          class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100"
                          data-manage-password="<?= $user['id'] ?>">
                          <img src="/assets/img/manage-password-icon.png" alt="Key" class="w-4 h-4">
                          Manage Password
                        </a>
                      </li>
                    <?php endif; ?>
                    <?php if (!empty($user['is_archived'])): ?>
                      <li>
                        <a href="#" data-restore-user="<?= $user['id'] ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100">
                          <img src="/assets/img/restore-icon.png" alt="Key" class="w-4 h-4">
                          Restore
                        </a>
                      </li>
                      <li>
                        <span class="flex items-center gap-3 px-4 py-2 text-yellow-500">Archived</span>
                      </li>
                    <?php else: ?>
                      <li>
                        <a href="/pages/super-admin/edit-user.php?id=<?= $user['id'] ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100">
                          <img src="/assets/img/edit-icon.png" alt="Key" class="w-4 h-4">
                          Edit
                        </a>
                      </li>
                      <li>
                        <a href="#" data-archive-user="<?= $user['id'] ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100">
                          <img src="/assets/img/archive-icon.png" alt="Key" class="w-4 h-4">
                          Archive
                        </a>
                      </li>
                      <li>
                        <a href="#" data-delete-user="<?= $user['id'] ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100 text-red-600">
                          <img src="/assets/img/delete-icon.png" alt="Key" class="w-4 h-4">
                          Delete
                        </a>
                      </li>
                      <?php if ($user['is_locked'] == 1): ?>
                        <li>
                          <a href="#" data-unlock-user="<?= $user['id'] ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100 text-red-700">
                           Unlock
                          </a>
                        </li>
                      <?php endif; ?>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>