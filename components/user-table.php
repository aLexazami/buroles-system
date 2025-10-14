<?php
require_once __DIR__ . '/../helpers/table-utils.php';
?>
<?php if (!isset($users) || !is_array($users) || empty($users)): ?>
  <div class="flex flex-col items-center justify-center text-center text-gray-600 py-12">
    <img src="/assets/img/no-user.png" alt="No user data" class="w-24 h-24 mb-4 opacity-50" />
    <p class="text-md sm:text-lg font-semibold">No user data available</p>
    <p class="text-sm sm:text-md text-gray-400">Once users are added, they’ll appear here automatically.</p>
  </div>
<?php else: ?>
  <!-- Search Bar -->
  <?php include('../../components/search-bar.php'); ?>

  <?php $isSuperAdmin = ($_SESSION['role_id'] ?? 0) === 2; ?> 

  <!-- User Table -->
  <div class="overflow-auto rounded-lg shadow-sm min-h-[500px]">
    <table class="min-w-full table-auto border-transparent">
      <thead class="bg-emerald-600 text-white">
        <tr>
          <th class="px-4 py-2 text-left text-xs sm:text-sm"><?= sortLink('ID', 'id') ?></th>
          <th class="px-4 py-2 text-left text-xs sm:text-sm"><?= sortLink('Full Name', 'last_name') ?></th>
          <th class="px-4 py-2 text-left text-xs sm:text-sm"><?= sortLink('Username', 'username') ?></th>
          <th class="px-4 py-2 text-left text-xs sm:text-sm"><?= sortLink('Email', 'email') ?></th>
          <th class="px-4 py-2 text-left text-xs sm:text-sm"><?= sortLink('Role', 'role_name') ?></th>
          <th class="px-4 py-2 text-left text-xs sm:text-sm">Status</th>
          <th class="px-4 py-2 text-left text-xs sm:text-sm">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr class="border-y border-gray-300 hover:bg-emerald-50">
            <td class="px-4 py-2 text-red-500 font-medium text-xs sm:text-sm"><?= htmlspecialchars($user['id']) ?></td>
            <td class="px-4 py-2 text-xs sm:text-sm whitespace-nowrap">
              <?= htmlspecialchars(trim($user['last_name'] . ', ' . $user['first_name'] . ' ' . ($user['middle_name'] ?? ''))) ?>
            </td>
            <td class="px-4 py-2 text-xs sm:text-sm"><?= htmlspecialchars($user['username']) ?></td>
            <td class="px-4 py-2 text-xs sm:text-sm"><?= htmlspecialchars($user['email']) ?></td>
            <td class="px-4 py-2 text-xs sm:text-sm whitespace-nowrap">
              <span class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded text-xs sm:text-sm">
                <?= htmlspecialchars($user['role_name']) ?>
              </span>
            </td>
            <td class="px-4 py-2 text-xs sm:text-sm space-x-1 whitespace-nowrap">
              <?php if (!empty($user['is_archived'])): ?>
                <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs sm:text-sm">Archived</span>
              <?php endif; ?>
              <?php if ($user['must_change_password']): ?>
                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs sm:text-sm">Must Change Password</span>
              <?php endif; ?>
              <?php if (!empty($user['is_locked'])): ?>
                <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-xs sm:text-sm">Locked</span>
              <?php endif; ?>
              <?php if (empty($user['is_archived']) && !$user['must_change_password'] && empty($user['is_locked'])): ?>
                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs sm:text-sm">Active</span>
              <?php endif; ?>
            </td>

            <!-- Unified Actions Column -->
            <?php if ($showActions ?? true): ?>
              <td class="px-4 py-2 text-xs sm:text-sm whitespace-nowrap">
                <div class="inline-block w-full relative">
                  <button class="menu-toggle p-2 cursor-pointer hover:bg-emerald-300 rounded-full focus:outline-none"
                    data-target="menu-<?= $user['id'] ?>" aria-haspopup="true" aria-expanded="false">
                    <img src="/assets/img/dots-icon.png" alt="Menu" class="w-6 h-6 sm:w-5 sm:h-5">
                  </button>

                  <div id="menu-<?= $user['id'] ?>" class="dropdown-menu hidden absolute top-full right-12 w-45 sm:w-52 z-10 bg-white rounded shadow-md">
                    <ul class=" text-gray-700 font-semibold divide-y divide-gray-100 text-xs sm:text-sm">
                      <?php if (!empty($user['is_archived'])): ?>
                        <li>
                          <a href="#" data-restore-user="<?= $user['id'] ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100">
                            <img src="/assets/img/restore-icon.png" alt="Restore" class="w-4 h-4"> Restore
                          </a>
                        </li>
                      <?php else: ?>
                        <?php if ($isSuperAdmin): ?>
                          <li>
                            <a href="/pages/admin/manage-password.php?id=<?= $user['id'] ?>"
                              data-manage-password="<?= $user['id'] ?>"
                              class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100">
                              <img src="/assets/img/manage-password-icon.png" alt="Manage" class="w-4 h-4"> Manage Password
                            </a>
                          </li>
                        <?php endif; ?>
                        <li>
                          <a href="/pages/admin/edit-user.php?id=<?= $user['id'] ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100">
                            <img src="/assets/img/edit-icon.png" alt="Edit" class="w-4 h-4"> Edit
                          </a>
                        </li>
                        <li>
                          <a href="#" data-archive-user="<?= $user['id'] ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100">
                            <img src="/assets/img/archive-icon.png" alt="Archive" class="w-4 h-4"> Archive
                          </a>
                        </li>
                        <li>
                          <a href="#" data-delete-user="<?= $user['id'] ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100 text-red-600">
                            <img src="/assets/img/delete-icon.png" alt="Delete" class="w-4 h-4"> Delete
                          </a>
                        </li>
                        <?php if ($user['is_locked'] == 1): ?>
                          <li>
                            <a href="#" class="flex items-center gap-3 px-4 py-2 hover:bg-emerald-100 text-yellow-700 open-unlock-modal"
                              data-user-id="<?= $user['id'] ?>"
                              data-manage-url="/pages/admin/manage-password.php?id=<?= $user['id'] ?>">
                              <img src="/assets/img/unlock-icon.png" alt="Unlock" class="w-4 h-4"> Unlock
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
  </div>
<?php endif; ?>