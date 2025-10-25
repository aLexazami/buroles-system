<?php
require_once __DIR__ . '/../helpers/flash.php';
$formData = getFlash('form_data') ?? $userData ?? [];
$formErrors = getFlash('form_errors') ?? [];

function fieldError($name, $formErrors)
{
  return $formErrors[$name] ?? null;
}

function fieldClass($name, $formErrors)
{
  return isset($formErrors[$name]) ? 'border-b-2 border-red-500' : '';
}

function fieldAutofocus($name, $formErrors)
{
  return isset($formErrors[$name]) ? 'autofocus' : '';
}
?>
<form method="POST" action="<?= $formMode === 'edit' ? '/controllers/update-user.php' : '/controllers/create-user.php' ?>" class="flex flex-col space-y-5 w-full max-w-xl mx-auto">

  <?php if ($formMode === 'edit'): ?>
    <input type="hidden" name="id" value="<?= htmlspecialchars($userData['id']) ?>" />
  <?php endif; ?>

  <!-- Role -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2 <?= fieldClass('role_id', $formErrors) ?>">
    <img src="/assets/img/role.png" class="h-5 w-5">
    <select name="role_id" id="role_id" class="flex-1 h-12 p-2 border-l-2 font-bold focus:outline-none sm:text-base md:text-lg cursor-pointer" required <?= fieldAutofocus('role_id', $formErrors) ?>>
      <option value="" disabled hidden>Select Role</option>
      <option value="1" <?= ($formData['role_id'] ?? '') == '1' ? 'selected' : '' ?>>Teacher</option>
      <option value="2" <?= ($formData['role_id'] ?? '') == '2' ? 'selected' : '' ?>>Admin</option>
    </select>
  </div>
  <?php if ($error = fieldError('role_id', $formErrors)): ?>
    <p class="text-red-500 text-sm mt-1"><?= $error ?></p>
  <?php endif; ?>

  <!-- First Name -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2 <?= fieldClass('first_name', $formErrors) ?>">
    <img src="/assets/img/name.png" class="h-5 w-5">
    <div class="relative w-full border-l-2">
      <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" class="peer w-full pt-6 pb-2 px-3 border-none focus:outline-none sm:text-base md:text-lg" placeholder=" " required <?= fieldAutofocus('first_name', $formErrors) ?>>
      <label for="first_name" class="absolute left-3 top-2 text-gray-500 text-sm transition-all peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600 peer-placeholder-shown:top-5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">First Name</label>
    </div>
  </div>
  <?php if ($error = fieldError('first_name', $formErrors)): ?>
    <p class="text-red-500 text-sm mt-1"><?= $error ?></p>
  <?php endif; ?>

  <!-- Middle Name -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2">
    <img src="/assets/img/name.png" class="h-5 w-5">
    <div class="relative w-full border-l-2">
      <input type="text" name="middle_name" id="middle_name" value="<?= htmlspecialchars($formData['middle_name'] ?? '') ?>" class="peer w-full pt-6 pb-2 px-3 border-none focus:outline-none sm:text-base md:text-lg" placeholder=" ">
      <label for="middle_name" class="absolute left-3 top-2 text-gray-500 text-sm transition-all peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600 peer-placeholder-shown:top-5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Middle Name (optional)</label>
    </div>
  </div>

  <!-- Last Name -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2 <?= fieldClass('last_name', $formErrors) ?>">
    <img src="/assets/img/name.png" class="h-5 w-5">
    <div class="relative w-full border-l-2">
      <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" class="peer w-full pt-6 pb-2 px-3 border-none focus:outline-none sm:text-base md:text-lg" placeholder=" " required <?= fieldAutofocus('last_name', $formErrors) ?>>
      <label for="last_name" class="absolute left-3 top-2 text-gray-500 text-sm transition-all peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600 peer-placeholder-shown:top-5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Last Name</label>
    </div>
  </div>
  <?php if ($error = fieldError('last_name', $formErrors)): ?>
    <p class="text-red-500 text-sm mt-1"><?= $error ?></p>
  <?php endif; ?>

  <!-- Username -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2 <?= fieldClass('username', $formErrors) ?>">
    <img src="/assets/img/username.png" class="h-5 w-5">
    <div class="relative w-full border-l-2">
      <input type="text" name="username" id="username" value="<?= htmlspecialchars($formData['username'] ?? '') ?>" class="peer w-full pt-6 pb-2 px-3 border-none focus:outline-none sm:text-base md:text-lg" placeholder=" " required <?= fieldAutofocus('username', $formErrors) ?>>
      <label for="username" class="absolute left-3 top-2 text-gray-500 text-sm transition-all peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600 peer-placeholder-shown:top-5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Username</label>
    </div>
  </div>
  <?php if ($error = fieldError('username', $formErrors)): ?>
    <p class="text-red-500 text-sm mt-1"><?= $error ?></p>
  <?php endif; ?>

  <!-- Email -->
  <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2 <?= fieldClass('email', $formErrors) ?>">
    <img src="/assets/img/email.png" class="h-5 w-5">
    <div class="relative w-full border-l-2">
      <input type="email" name="email" id="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" class="peer w-full pt-6 pb-2 px-3 border-none focus:outline-none sm:text-base md:text-lg" placeholder=" " title="Must be a valid @gmail.com address" required <?= fieldAutofocus('email', $formErrors) ?>>
      <label for="email" class="absolute left-3 top-2 text-gray-500 text-sm transition-all peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600 peer-placeholder-shown:top-5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Email Address</label>
    </div>
  </div>
  <?php if ($error = fieldError('email', $formErrors)): ?>
    <p class="text-red-500 text-sm mt-1"><?= $error ?></p>
  <?php endif; ?>

  <!-- Password (only for create) -->
  <?php if ($formMode === 'create'): ?>
    <div class="flex items-center gap-3 border-2 rounded-lg px-3 py-2 <?= fieldClass('password', $formErrors) ?>">
      <img src="/assets/img/password.png" class="h-5 w-5">
      <div class="relative w-full flex items-center border-l-2 pl-2">
        <input type="password" id="password" name="password" class="peer w-full pt-6 pb-2 px-3 focus:outline-none sm:text-base md:text-lg" placeholder=" " required <?= fieldAutofocus('password', $formErrors) ?>>
        <label for="password" class="absolute left-3 top-2 text-gray-500 text-sm transition-all peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600 peer-placeholder-shown:top-5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Password</label>
        <img src="/assets/img/eye-open.png" alt="Toggle visibility" class="w-5 h-5 ml-2 cursor-pointer opacity-70 hover:opacity-100" data-toggle-password="password">
      </div>
    </div>
    <?php if ($error = fieldError('password', $formErrors)): ?>
      <p class="text-red-500 text-sm mt-1"><?= $error ?></p>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Submit Button -->
  <div class="text-center">
    <button type="submit" class="w-full sm:w-48 bg-emerald-800 text-white p-2 rounded hover:bg-emerald-600 sm:text-base md:text-lg">
      <?= $formMode === 'edit' ? 'Save Changes' : 'Create Account' ?>
    </button>
  </div>
</form>