<div class="space-y-6">

  <!-- 👤 Profile Overview -->
  <section class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">My Profile</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

      <!-- Avatar Upload -->
      <form id="avatar-form" action="/controllers/upload-avatar.php" method="POST" enctype="multipart/form-data" class="flex flex-col items-center space-y-3">
        <!-- Avatar Preview -->
        <img id="avatar-preview"
          src="<?= htmlspecialchars($_SESSION['avatar_path'] ?? '/assets/img/user.png') . '?v=' . time() ?>"
          class="h-35 w-35 rounded-full border-2 border-emerald-400 object-cover"
          alt="Profile Avatar">
        <!-- Hidden File Input -->
        <input type="file" name="avatar" id="avatar-upload" accept="image/*" class="sr-only">
        <!-- Trigger Button -->
        <button type="button"
          onclick="document.getElementById('avatar-upload').click()"
          class="text-xs bg-gray-300 text-black px-3 py-1 rounded hover:bg-gray-400">
          Choose Image
        </button>
        <!-- Submit Button -->
        <button type="submit" class="text-xs bg-emerald-700 text-white px-3 py-1 rounded hover:bg-emerald-800">
          Update Avatar
        </button>
      </form>


      <!-- Profile Info -->
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Full Name</label>
          <p class="mt-1 text-gray-900"><?= htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']) ?></p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Role</label>
          <p class="mt-1 text-gray-900"><?= htmlspecialchars($_SESSION['role_name']) ?></p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <p class="mt-1 text-gray-900"><?= htmlspecialchars($_SESSION['email'] ?? 'Not set') ?></p>
        </div>
      </div>
    </div>
  </section>

  <!-- 🔐 Account Settings -->
  <section class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Account Settings</h2>
    <form action="/controllers/update-account.php" method="POST" class="space-y-4">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Update Email</label>
        <input type="email" name="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
      </div>
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
        <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
      </div>
      <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded hover:bg-emerald-800">Save Changes</button>
    </form>
  </section>

  <!-- 📊 Admin Insights -->
  <section class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Admin Activity Summary</h2>
    <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
      <li>Last login: <?= htmlspecialchars($_SESSION['last_login'] ?? 'Unknown') ?></li>
      <li>Feedback reports submitted: <?= htmlspecialchars($_SESSION['feedback_count'] ?? '0') ?></li>
      <li>System role switches: <?= htmlspecialchars($_SESSION['role_switch_count'] ?? '0') ?></li>
    </ul>
  </section>

</div>