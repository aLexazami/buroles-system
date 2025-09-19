 <!-- 👤 Profile Overview -->
 <div class="bg-emerald-700 text-white p-5">
   <h2 class="text-lg font-semibold">My Profile</h2>
 </div>
 <section class="bg-white p-6 rounded-b-lg shadow ">
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
       <button type="submit" class="text-xs bg-emerald-500 text-white px-3 py-1 rounded hover:bg-emerald-600">
         Update Avatar
       </button>
     </form>


     <!-- Profile Info -->
     <div class="space-y-4">
       <div>
         <label class="block text-sm font-bold text-gray-700">Full Name</label>
         <p class="mt-1 text-gray-900">
           <?= htmlspecialchars(trim($_SESSION['firstName'] . ' ' . ($_SESSION['middleName'] ?? '') . ' ' . $_SESSION['lastName'])) ?>
         </p>
       </div>
       <div>
         <label class="block text-sm font-bold text-gray-700">Role</label>
         <p class="mt-1 text-gray-900"><?= htmlspecialchars($_SESSION['role_name']) ?></p>
       </div>
       <div>
         <label class="block text-sm font-bold text-gray-700">Email</label>
         <p class="mt-1 text-gray-900"><?= htmlspecialchars($_SESSION['email'] ?? 'Not set') ?></p>
       </div>
     </div>
   </div>
 </section>


 <div class="bg-emerald-700 text-white p-5 mt-6">
   <h2 class="text-lg font-semibold">Account Settings</h2>
 </div>
 <!-- 🔐 Account Settings -->
 <section class="bg-white p-6 rounded-b-lg shadow grid grid-cols-2 gap-x-5 gap-y-8">

   <!-- ✉️ Update Email -->
   <div class="bg-white rounded-b-md shadow border border-emerald-600 ">
     <div class="bg-emerald-700 text-white p-2">
       <h2 class="text-sm font-semibold">Update Email Address</h2>
     </div>
     <form action="/controllers/account-settings/update-email.php" method="POST" class="space-y-4 p-2">
       <div class="pt-5">
         <label for="current_email" class="block text-sm font-bold text-gray-700">Current Email Address (For Recovery)</label>
         <input type="email" name="current_email" id="current_email"
           class="mt-1 text-gray-900 block w-full border border-gray-400 rounded-md shadow-sm p-2"
           value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
       </div>
       <div>
         <label for="new_email" class="block text-sm font-bold text-gray-700">New Email Address (For Recovery)</label>
         <input type="email" name="new_email" id="new_email"
           class="mt-1 text-gray-900 block w-full border border-gray-400 rounded-md shadow-sm p-2">
       </div>
       <div class="flex justify-end">
         <button type="submit" class="bg-emerald-500 text-white px-4 py-2 rounded hover:bg-emerald-600">
           Update Email
         </button>
       </div>
     </form>
   </div>

   <!-- 🔐 Update Password -->
   <div class="bg-white rounded-b-md shadow border border-emerald-600">
     <div class="bg-emerald-700 text-white p-2">
       <h2 class="text-sm font-semibold">Update Password</h2>
     </div>
     <form action="/controllers/account-settings/update-password.php" method="POST" class="space-y-4 p-2">

       <!-- 🔒 Current Password -->
       <div>
         <label for="current_password" class="block text-sm font-bold text-gray-700">Current Password</label>
         <div class="relative">
           <input type="password" name="current_password" id="current_password"
             class="mt-1 text-gray-900 block w-full border border-gray-400 rounded-md shadow-sm p-2 pr-10">
           <img
             src="/assets/img/eye-open.png"
             alt="Toggle visibility"
             data-toggle-password="current_password"
             class="absolute right-3 top-3 w-5 h-5 cursor-pointer opacity-70 hover:opacity-100" />
         </div>
       </div>

       <!-- 🔒 New Password -->
       <div>
         <label for="new_password" class="block text-sm font-bold text-gray-700">New Password</label>
         <div class="relative">
           <input type="password" name="new_password" id="new_password"
             class="mt-1 text-gray-900 block w-full border border-gray-400 rounded-md shadow-sm p-2 pr-10">
           <img
             src="/assets/img/eye-open.png"
             alt="Toggle visibility"
             data-toggle-password="new_password"
             class="absolute right-3 top-3 w-5 h-5 cursor-pointer opacity-70 hover:opacity-100" />
         </div>
       </div>

       <!-- 🔒 Confirm Password -->
       <div>
         <label for="confirm_password" class="block text-sm font-bold text-gray-700">Confirm Password</label>
         <div class="relative">
           <input type="password" name="confirm_password" id="confirm_password"
             class="mt-1 text-gray-900 block w-full border border-gray-400 rounded-md shadow-sm p-2 pr-10">
           <img
             src="/assets/img/eye-open.png"
             alt="Toggle visibility"
             data-toggle-password="confirm_password"
             class="absolute right-3 top-3 w-5 h-5 cursor-pointer opacity-70 hover:opacity-100" />
         </div>
       </div>

       <div class="flex justify-end">
         <button type="submit" class="bg-emerald-500 text-white px-4 py-2 rounded hover:bg-emerald-600">
           Update Password
         </button>
       </div>
     </form>
   </div>
 </section>