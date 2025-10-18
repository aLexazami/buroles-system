<!-- ✅ Attendance Modal -->
<div id="attendanceModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-green-500">
    <h2 class="text-xl sm:text-2xl mb-4">Mark Attendance</h2>
    <form id="attendanceForm">
      <input type="hidden" name="student_id" id="attendanceStudentId">
      <select name="status" required class="block w-full mb-4 border rounded px-3 py-2">
        <option value="">Select status</option>
        <option value="present">Present</option>
        <option value="absent">Absent</option>
        <option value="late">Late</option>
        <option value="excused">Excused</option>
      </select>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelAttendanceBtn" class="px-3 py-1 text-green-700 rounded hover:bg-green-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-green-700 rounded hover:bg-green-100 text-sm cursor-pointer">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- 👤 Add Student Modal -->
<div id="addStudentModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Add Student</h2>
    <form id="addStudentForm">
      <input type="text" name="first_name" placeholder="First name" required class="block w-full mb-3 border rounded px-3 py-2">
      <input type="text" name="last_name" placeholder="Last name" required class="block w-full mb-3 border rounded px-3 py-2">
      <input type="date" name="birthdate" required class="block w-full mb-4 border rounded px-3 py-2">
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelAddStudentBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Add</button>
      </div>
    </form>
  </div>
</div>

<!-- 🏫 Create Advisory Class Modal -->
<div id="createAdvisoryModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Create Advisory Class</h2>
    <form id="createAdvisoryForm">
      <input type="text" name="name" placeholder="Class name (e.g. Grade 4 - Section A)" required class="block w-full mb-3 border rounded px-3 py-2">
      <input type="number" name="grade_level" placeholder="Grade level (e.g. 4)" required class="block w-full mb-3 border rounded px-3 py-2">
      <input type="text" name="section" placeholder="Section (e.g. A)" required class="block w-full mb-4 border rounded px-3 py-2">
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelCreateAdvisoryBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Create</button>
      </div>
    </form>
  </div>
</div>

<!-- 🏫 Add Grade Level Modal -->
<div id="addGradeLevelModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Add Grade Level</h2>
    <form id="addGradeLevelForm">
      <input type="hidden" name="id" id="gradeLevelId">

      <!-- Grade Level -->
      <div class="relative mb-4">
        <input type="text" name="level" id="level" required
          class="peer block w-full border rounded px-3 pt-5 pb-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <label for="level" class="absolute left-3 top-2 text-sm text-gray-500 transition-all
      peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600
      peer-valid:top-2 peer-valid:text-sm peer-valid:text-emerald-600">
          Grade Level (numeric)
        </label>
      </div>

      <!-- Grade Label -->
      <div class="relative mb-6">
        <input type="text" name="label" id="label" required
          class="peer block w-full border rounded px-3 pt-5 pb-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <label for="label" class="absolute left-3 top-2 text-sm text-gray-500 transition-all
      peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600
      peer-valid:top-2 peer-valid:text-sm peer-valid:text-emerald-600">
          Grade Label (e.g. Grade 1)
        </label>
      </div>

      <!-- Buttons -->
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelAddGradeLevelBtn"
          class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">
          Cancel
        </button>
        <button type="submit"
          class="px-3 py-1 text-white bg-emerald-600 hover:bg-emerald-700 rounded text-sm cursor-pointer">
          Save
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 📝 Edit Grade Level Modal -->
<div id="editGradeLevelModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Edit Grade Level</h2>
    <form id="editGradeLevelForm">
      <input type="hidden" name="id" id="editGradeLevelId">

      <!-- Floating Label: Grade Level -->
      <div class="relative mb-4">
        <input type="text" name="level" id="editLevel" required
          class="peer block w-full border rounded px-3 pt-5 pb-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <label for="editLevel"
          class="absolute left-3 top-2 text-sm text-gray-500 transition-all
                 peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600
                 peer-valid:top-2 peer-valid:text-sm peer-valid:text-emerald-600">
          Grade Level (numeric)
        </label>
      </div>

      <!-- Floating Label: Grade Label -->
      <div class="relative mb-6">
        <input type="text" name="label" id="editLabel" required
          class="peer block w-full border rounded px-3 pt-5 pb-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <label for="editLabel"
          class="absolute left-3 top-2 text-sm text-gray-500 transition-all
                 peer-focus:top-2 peer-focus:text-sm peer-focus:text-emerald-600
                 peer-valid:top-2 peer-valid:text-sm peer-valid:text-emerald-600">
          Grade Label
        </label>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelEditGradeLevelBtn"
          class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">
          Cancel
        </button>
        <button type="submit"
          class="px-3 py-1 text-white bg-emerald-600 hover:bg-emerald-700 rounded text-sm cursor-pointer">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 🗑️ Confirm Delete Grade Level Modal -->
<div id="confirmDeleteGradeLevelModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-red-500">
    <h2 class="text-xl sm:text-2xl mb-4 text-red-700">Delete Grade Level</h2>
    <p class="mb-4 text-sm text-gray-700">
      Are you sure you want to delete <span id="deleteGradeLevelLabel" class="font-semibold text-red-600"></span>?
    </p>
    <form id="deleteGradeLevelForm">
      <input type="hidden" name="id" id="deleteGradeLevelId">
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelDeleteGradeLevelBtn" class="px-3 py-1 text-red-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-red-600 hover:bg-red-700 rounded text-sm cursor-pointer">Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- 📝 Edit Grade Section Modal -->
<div id="editGradeSectionModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Edit Grade Section</h2>
    <form id="editGradeSectionForm">
      <input type="hidden" name="id" id="editGradeSectionId">

      <!-- Grade Level Selector -->
      <div class="mb-4">
        <label for="editGradeSectionLevel" class="block text-sm text-gray-600 mb-1">Grade Level</label>
        <select name="grade_level_id" id="editGradeSectionLevel" required class="w-full border rounded px-3 py-2">
          <?php foreach ($gradeLevels as $g): ?>
            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Section Label -->
      <div class="mb-6">
        <label for="editGradeSectionLabel" class="block text-sm text-gray-600 mb-1">Section Label</label>
        <input type="text" name="section_label" id="editGradeSectionLabel" required class="w-full border rounded px-3 py-2">
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" id="cancelEditGradeSectionBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-emerald-600 hover:bg-emerald-700 rounded text-sm cursor-pointer">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- 🗑️ Confirm Delete Grade Section Modal -->
<div id="confirmDeleteGradeSectionModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-red-500">
    <h2 class="text-xl sm:text-2xl mb-4 text-red-700">Delete Section</h2>
    <p class="mb-4 text-sm text-gray-700">
      Are you sure you want to delete <span id="deleteGradeSectionLabel" class="font-semibold text-red-600"></span>?
    </p>
    <form id="deleteGradeSectionForm">
      <input type="hidden" name="id" id="deleteGradeSectionId">
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelDeleteGradeSectionBtn" class="px-3 py-1 text-red-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-red-600 hover:bg-red-700 rounded text-sm cursor-pointer">Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- ➕ Add Grade Section Modal -->
<div id="addGradeSectionModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Add Grade Section</h2>
    <form id="addGradeSectionForm">
      <!-- Grade Level Selector -->
      <div class="mb-4">
        <label for="addGradeSectionLevel" class="block text-sm text-gray-600 mb-1">Grade Level</label>
        <select name="grade_level_id" id="addGradeSectionLevel" required class="w-full border rounded px-3 py-2">
          <?php foreach ($gradeLevels as $g): ?>
            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Section Label -->
      <div class="mb-6">
        <label for="addGradeSectionLabel" class="block text-sm text-gray-600 mb-1">Section Label</label>
        <input type="text" name="section_label" id="addGradeSectionLabel" required class="w-full border rounded px-3 py-2">
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" id="cancelAddGradeSectionBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-emerald-600 hover:bg-emerald-700 rounded text-sm cursor-pointer">Add Section</button>
      </div>
    </form>
  </div>
</div>