<header class=" shadow-md sticky-top-0 z-10 bg-emerald-950 text-white p-1">
  <section class=" max-w-7xl m-auto flex justify-between px-10 items-center">
    <div class="flex items-center py-2 ">
      <img src="/assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-12 border rounded-full bg-white ">
      <p class="text-xl font-medium  ml-5">
        Burol Elementary School
      </p>
    </div>

    <!-- Date and Time Section -->
    <div class="flex text-sm  font-bold ">
      <span id="date-time"></span>
    </div>
    <div class="flex justify-end items-center">

      <!-- Nav Menu for Desktop -->
      <div class="max-md:hidden flex space-x-2 ">

        <div class=" flex space-x-2 relative">
          <?php include 'role-badge.php'; ?>
          <!-- Profile Button -->
          <button id="menu-btn-desktop" class="flex flex-row items-center space-x-3 cursor-pointer mr-2">
            <img src="/assets/img/user.png" alt="Profile" class="h-10 w-10 rounded-full border-2 border-emerald-400">
            <div>
              <p class="font-medium">
                <?= htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']) ?>
              </p>
              <p class="uppercase text-sm">
                <?= htmlspecialchars($_SESSION['role_name']) ?>
              </p>
            </div>
          </button>

          <!-- Role Switcher Dropdown -->
          <?php if (count($_SESSION['available_roles']) >= 1): ?>
            <div id="role-switcher-desktop" class="absolute top-full right-0 mt-2 w-48 bg-white border rounded shadow-lg z-50 hidden px-4 py-2 space-y-1">
              <?php foreach ($_SESSION['available_roles'] as $role): ?>
                <a href="#"
                  class="block px-3 py-2 text-sm rounded hover:bg-emerald-100 text-emerald-800 <?= $role == $_SESSION['active_role_id'] ? 'font-bold bg-emerald-50' : '' ?>"
                  data-role="<?= $role ?>">
                  <?= $role == 1 ? 'Staff' : ($role == 2 ? 'Admin' : 'Super Admin') ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class=" flex items-center">
          <a href="" class="group relative flex items-center p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600">
            <img src="/assets/img/profile.png" alt="Profile" class="h-5 w-5 invert">
            <span class="absolute top-10 w-23 opacity-0 translate-y-1 transition-all duration-300 text-sm bg-white text-emerald-800 px-2 py-1 rounded group-hover:opacity-100 group-hover:translate-y-0">
              My Account
            </span>
          </a>
        </div>
        <div class=" flex items-center">
          <a href="" class="group relative flex items-center p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600">
            <img src="/assets/img/message.png" alt="Profile" class="h-5 w-5 invert">
            <span class="absolute top-10 opacity-0 translate-y-1 transition-all duration-300 text-sm bg-white text-emerald-800 px-2 py-1 rounded group-hover:opacity-100 group-hover:translate-y-0">
              Message
            </span>
          </a>
        </div>
        <div class=" flex items-center">
          <a href="" class="group relative flex items-center p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600">
            <img src="/assets/img/notif.png" alt="Profile" class="h-5 w-5 invert">
            <span class="absolute top-10 opacity-0 translate-y-1 transition-all duration-300 text-sm bg-white text-emerald-800 px-2 py-1 rounded group-hover:opacity-100 group-hover:translate-y-0">
              Notification
            </span>
          </a>
        </div>
        <div class=" flex items-center">
          <a href="/controllers/log-out.php" class="group relative flex items-center p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600">
            <img src="/assets/img/logout.png" alt="Profile" class="h-5 w-5 ">
            <span class="absolute top-10 opacity-0 translate-y-1 transition-all duration-300 text-sm bg-white text-emerald-800 px-2 py-1 rounded group-hover:opacity-100 group-hover:translate-y-0">
              Logout
            </span>
          </a>
        </div>
      </div>

      <!-- Nav Menu for Mobile -->
      <div class=" flex flex-row">
        <button id="menu-btn-mobile" class=" flex flex-row items-center space-x-3 cursor-pointer md:hidden mr-2">
          <img src="/assets/img/user.png" alt="Profile" class="h-10 w-10 rounded-full border-2 border-emerald-400">
          <div>
            <p class="font-medium"> <?php echo htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']); ?></p>
            <p class="uppercase text-sm"><?php echo htmlspecialchars($_SESSION['role_name']); ?></p>
          </div>
        </button>
        <div id="menu-links" class="hidden md:hidden absolute top-17 max-md:top-20   p-3 bg-white shadow-lg rounded-sm ">
          <a href="" class="menu-link flex items-center  p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600 ">
            <img src="/assets/img/profile.png" alt="Profile" class="h-5 w-5 rounded-full mr-3">My Account
          </a>
          <a href="" class="menu-link flex items-center p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600 ">
            <img src="/assets/img/message.png" alt="Profile" class="h-5 w-5 rounded-full mr-3">Message
          </a>
          <a href="" class="menu-link flex items-center p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600 ">
            <img src="/assets/img/notif.png" alt="Profile" class="h-5 w-5 rounded-full mr-3">Notification
          </a>
          <a href="/controllers/log-out.php" class="menu-link flex items-center  p-2 text-sm rounded-sm text-emerald-800 hover:bg-emerald-600">
            <img src="/assets/img/logout.png" alt="Profile" class="h-5 w-5 rounded-full mr-3">Logout
          </a>
        </div>
      </div>
    </div>
  </section>
</header>
<div>
  <!-- Role Welcome-->
  <?php include __DIR__ .'/../includes/role-welcome.php'?>
</div>