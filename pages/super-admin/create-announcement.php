<div>
  <form method="POST" action="/controllers/create-announcement.php" class="space-y-4 bg-white p-6 rounded shadow">
    <h2 class="text-lg font-bold text-gray-800">Create Announcement</h2>
    <input type="text" name="title" placeholder="Title" required class="w-full p-2 border rounded">
    <textarea name="body" placeholder="Body" required class="w-full p-2 border rounded"></textarea>
    <select name="role_id" class="w-full p-2 border rounded">
      <option value="">All Roles</option>
      <option value="1">Staff</option>
      <option value="2">Admin</option>
      <option value="99">Super Admin</option>
    </select>
    <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded">Post Announcement</button>
  </form>
</div>