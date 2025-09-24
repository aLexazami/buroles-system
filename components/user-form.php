<form method="POST" action="<?= $formMode === 'edit' ? '/controllers/update-user.php' : '/controllers/create-user.php' ?>" class="flex flex-col">

  <?php if ($formMode === 'edit'): ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars($userData['id']) ?>" />
  <?php endif; ?>

  <!-- Role -->
  <div class="flex justify-center items-center mb-5 w-90 m-auto rounded-lg border-2">
    <img src="/assets/img/role.png" class="h-5 m-2">
    <select name="role_id" class="input h-12 w-80 p-2 border-l-2 font-bold focus:outline-none" required>
      <option value="1" <?= ($userData['role_id'] ?? '') === 'Staff' ? 'selected' : '' ?>>Staff</option>
      <option value="2" <?= ($userData['role_id'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
    </select>
  </div>

  <!-- First Name -->
  <div class="flex justify-center items-center mb-5 w-180 m-auto rounded-lg border-2">
    <img src="/assets/img/name.png" class="h-5 m-2">
    <input type="text" name="first_name" value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>" placeholder="First Name" class="input h-12 w-170 p-2 border-l-2 focus:outline-none" required>
  </div>

  <!-- Middle Name -->
  <div class="flex justify-center items-center mb-5 w-180 m-auto rounded-lg border-2">
    <img src="/assets/img/name.png" class="h-5 m-2">
    <input type="text" name="middle_name" value="<?= htmlspecialchars($userData['middle_name'] ?? '') ?>" placeholder="Middle Name (optional)" class="input h-12 w-170 p-2 border-l-2 focus:outline-none">
  </div>

  <!-- Last Name -->
  <div class="flex justify-center items-center mb-5 w-180 m-auto rounded-lg border-2">
    <img src="/assets/img/name.png" class="h-5 m-2">
    <input type="text" name="last_name" value="<?= htmlspecialchars($userData['last_name'] ?? '') ?>" placeholder="Last Name" class="input h-12 w-170 p-2 border-l-2 focus:outline-none" required>
  </div>

  <!-- Username -->
  <div class="flex justify-center items-center mb-5 w-180 m-auto rounded-lg border-2">
    <img src="/assets/img/username.png" class="h-5 m-2">
    <input type="text" name="username" value="<?= htmlspecialchars($userData['username'] ?? '') ?>" placeholder="Username" class="input h-12 w-170 p-2 border-l-2 focus:outline-none" required>
  </div>

  <!-- Email -->
  <div class="flex justify-center items-center mb-5 w-180 m-auto rounded-lg border-2">
    <img src="/assets/img/email.png" class="h-5 m-2">
    <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" placeholder="Email Address" class="input h-12 w-170 p-2 border-l-2 focus:outline-none" required>
  </div>

  <!-- Password (only for create) -->
  <?php if ($formMode === 'create'): ?>
      <div class="relative flex justify-center items-center mb-5 w-180 m-auto rounded-lg border-2">
        <img src="/assets/img/password.png" class="h-5 m-2">
        <input type="password" id="password" name="password" placeholder="Password" class="input h-12 w-170 p-2 border-l-2 focus:outline-none" required>
        <img
          src="/assets/img/eye-open.png"
          alt="Toggle visibility"
          class="absolute right-3  w-5 h-5 cursor-pointer opacity-70 hover:opacity-100"
          data-toggle-password="password"/>
    </div>
  <?php endif; ?>

  <div class="text-center">
    <button type="submit" class="btn-primary w-55 bg-emerald-800 text-white p-2 rounded hover:bg-emerald-600">
      <?= $formMode === 'edit' ? 'Save Changes' : 'Create Account' ?>
    </button>
  </div>
</form>
