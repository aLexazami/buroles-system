<form method="POST" action="<?= $formMode === 'edit' ? '/controllers/update-user.php' : '/controllers/create-user.php' ?>" class="flex flex-col space-y-5 w-full max-w-xl mx-auto">

  <?php if ($formMode === 'edit'): ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars($userData['id']) ?>" />
  <?php endif; ?>

  <!-- Role -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
    <img src="/assets/img/role.png" class="h-5 w-5">
    <label for="role_id" class="hidden">Role</label>
    <select name="role_id" id="role_id" class="flex-1 h-12 p-2 border-l-2 font-bold focus:outline-none sm:text-base md:text-lg cursor-pointer" required>
      <option value="1" <?= ($userData['role_id'] ?? '') === 'Staff' ? 'selected' : '' ?>>Staff</option>
      <option value="2" <?= ($userData['role_id'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
      <option value="99" <?= ($userData['role_id'] ?? '') == 'Super Admin' ? 'selected' : '' ?>>Super Admin</option>
    </select>
  </div>

  <!-- First Name -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
    <img src="/assets/img/name.png" class="h-5 w-5">
    <input type="text" name="first_name" value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>" placeholder="First Name" class="flex-1 h-12 p-2 border-l-2 focus:outline-none sm:text-base md:text-lg" required>
  </div>

  <!-- Middle Name -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
    <img src="/assets/img/name.png" class="h-5 w-5">
    <input type="text" name="middle_name" value="<?= htmlspecialchars($userData['middle_name'] ?? '') ?>" placeholder="Middle Name (optional)" class="flex-1 h-12 p-2 border-l-2 focus:outline-none sm:text-base md:text-lg">
  </div>

  <!-- Last Name -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
    <img src="/assets/img/name.png" class="h-5 w-5">
    <input type="text" name="last_name" value="<?= htmlspecialchars($userData['last_name'] ?? '') ?>" placeholder="Last Name" class="flex-1 h-12 p-2 border-l-2 focus:outline-none sm:text-base md:text-lg" required>
  </div>

  <!-- Username -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
    <img src="/assets/img/username.png" class="h-5 w-5">
    <input type="text" name="username" value="<?= htmlspecialchars($userData['username'] ?? '') ?>" placeholder="Username" class="flex-1 h-12 p-2 border-l-2 focus:outline-none sm:text-base md:text-lg" required>
  </div>

  <!-- Email -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
    <img src="/assets/img/email.png" class="h-5 w-5">
    <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" placeholder="Email Address" class="flex-1 h-12 p-2 border-l-2 focus:outline-none sm:text-base md:text-lg" required>
  </div>

  <!-- Password (only for create) -->
  <?php if ($formMode === 'create'): ?>
    <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
      <img src="/assets/img/password.png" class="h-5 w-5">
      <div class="flex items-center flex-1 border-l-2 pl-2">
        <input type="password" id="password" name="password" placeholder="Password" class="flex-1 h-12 p-2 focus:outline-none sm:text-base md:text-lg" required>
        <img src="/assets/img/eye-open.png" alt="Toggle visibility" class="w-5 h-5 ml-2 cursor-pointer opacity-70 hover:opacity-100" data-toggle-password="password">
      </div>
    </div>
  <?php endif; ?>

  <!-- Submit Button -->
  <div class="text-center">
    <button type="submit" class="w-full sm:w-48 bg-emerald-800 text-white p-2 rounded hover:bg-emerald-600 sm:text-base md:text-lg">
      <?= $formMode === 'edit' ? 'Save Changes' : 'Create Account' ?>
    </button>
  </div>
</form>