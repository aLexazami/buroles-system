<!-- 🏫 Create Advisory Class Modal -->
<div id="createAdvisoryModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Create Advisory Class</h2>
    <form id="createAdvisoryForm">
      <input type="hidden" name="adviser_id" value="<?= isset($adviser['id']) ? htmlspecialchars($adviser['id']) : '' ?>">

      <!-- 📅 School Year (Fixed to Active) -->
      <?php
      $activeSchoolYear = null;
      if (!empty($schoolYears) && is_array($schoolYears)) {
        foreach ($schoolYears as $sy) {
          if (!empty($sy['is_active'])) {
            $activeSchoolYear = $sy;
            break;
          }
        }
      }
      ?>
      <?php if ($activeSchoolYear): ?>
        <label class="block mb-2 text-sm font-medium">School Year</label>
        <div class="mb-3 px-3 py-2 border rounded bg-gray-100 text-gray-700">
          <?= htmlspecialchars($activeSchoolYear['label']) ?>
        </div>
        <input type="hidden" name="school_year_id" value="<?= htmlspecialchars($activeSchoolYear['id']) ?>">
      <?php else: ?>
        <div class="mb-3 text-red-600 text-sm">No active school year found. Please activate one first.</div>
      <?php endif; ?>

      <!-- 🏫 Grade Level -->
      <label class="block mb-2 text-sm font-medium">Grade Level</label>
      <select name="grade_level" id="gradeLevelSelect" required class="block w-full mb-3 border rounded px-3 py-2">
        <option value="">Select grade level</option>
        <?php if (!empty($gradeLevels) && is_array($gradeLevels)): ?>
          <?php foreach ($gradeLevels as $level): ?>
            <option value="<?= htmlspecialchars($level['id']) ?>"><?= htmlspecialchars($level['label']) ?></option>
          <?php endforeach; ?>
        <?php else: ?>
          <option disabled>No grade levels available</option>
        <?php endif; ?>
      </select>

      <!-- 🧩 Section -->
      <label class="block mb-2 text-sm font-medium">Section</label>
      <select name="section_id" id="sectionSelect" required class="block w-full mb-4 border rounded px-3 py-2" disabled>
        <option value="">Select section</option>
      </select>

      <!-- 🎯 Actions -->
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
          <?php if (!empty($gradeLevels) && is_array($gradeLevels)): ?>
            <?php foreach ($gradeLevels as $g): ?>
              <option value="<?= htmlspecialchars($g['id']) ?>"><?= htmlspecialchars($g['label']) ?></option>
            <?php endforeach; ?>
          <?php else: ?>
            <option disabled>No grade levels available</option>
          <?php endif; ?>
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
          <?php if (!empty($gradeLevels) && is_array($gradeLevels)): ?>
            <?php foreach ($gradeLevels as $g): ?>
              <option value="<?= htmlspecialchars($g['id']) ?>"><?= htmlspecialchars($g['label']) ?></option>
            <?php endforeach; ?>
          <?php else: ?>
            <option disabled>No grade levels available</option>
          <?php endif; ?>
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

<!-- ➕ Add School Year Modal -->
<div id="addSchoolYearModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Add School Year</h2>
    <form id="addSchoolYearForm">
      <!-- Start Date -->
      <div class="mb-4">
        <label for="addSchoolYearStart" class="block text-sm text-gray-600 mb-1">Start Date</label>
        <input type="date" name="start_date" id="addSchoolYearStart" required class="w-full border rounded px-3 py-2">
      </div>

      <!-- End Date -->
      <div class="mb-4">
        <label for="addSchoolYearEnd" class="block text-sm text-gray-600 mb-1">End Date</label>
        <input type="date" name="end_date" id="addSchoolYearEnd" required class="w-full border rounded px-3 py-2">
      </div>

      <!-- Auto-generated Label -->
      <div class="mb-6">
        <label for="addSchoolYearLabel" class="block text-sm text-gray-600 mb-1">Label</label>
        <input type="text" name="label" id="addSchoolYearLabel" readonly class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500">
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" id="cancelAddSchoolYearBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-emerald-600 hover:bg-emerald-700 rounded text-sm cursor-pointer">Add School Year</button>
      </div>
    </form>
  </div>
</div>

<!-- ✏️ Edit School Year Modal -->
<div id="editSchoolYearModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Edit School Year</h2>
    <form id="editSchoolYearForm">
      <!-- Hidden ID -->
      <input type="hidden" name="id" id="editSchoolYearId">

      <!-- Start Date -->
      <div class="mb-4">
        <label for="editSchoolYearStart" class="block text-sm text-gray-600 mb-1">Start Date</label>
        <input type="date" name="start_date" id="editSchoolYearStart" required class="w-full border rounded px-3 py-2">
      </div>

      <!-- End Date -->
      <div class="mb-4">
        <label for="editSchoolYearEnd" class="block text-sm text-gray-600 mb-1">End Date</label>
        <input type="date" name="end_date" id="editSchoolYearEnd" required class="w-full border rounded px-3 py-2">
      </div>

      <!-- Auto-generated Label -->
      <div class="mb-4">
        <label for="editSchoolYearLabel" class="block text-sm text-gray-600 mb-1">Label</label>
        <input type="text" name="label" id="editSchoolYearLabel" readonly class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500">
      </div>

      <!-- Status Toggle -->
      <div class="mb-6 flex items-center gap-2">
        <input type="checkbox" name="is_active" id="editSchoolYearStatus" class="h-4 w-4">
        <label for="editSchoolYearStatus" class="text-sm text-gray-600">Mark as Active</label>
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" id="cancelEditSchoolYearBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-emerald-600 hover:bg-emerald-700 rounded text-sm cursor-pointer">Update School Year</button>
      </div>
    </form>
  </div>
</div>

<!-- 🗑️ Delete School Year Modal -->
<div id="deleteSchoolYearModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-red-500">
    <h2 class="text-xl sm:text-2xl mb-4 text-red-600">Delete School Year</h2>
    <form id="deleteSchoolYearForm">
      <input type="hidden" name="id" id="deleteSchoolYearId">
      <p class="text-sm text-gray-700 mb-6">
        Are you sure you want to delete <strong id="deleteSchoolYearLabel" class="text-red-600"></strong>?
        This action cannot be undone.
      </p>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelDeleteSchoolYearBtn" class="px-3 py-1 text-red-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-red-600 hover:bg-red-700 rounded text-sm cursor-pointer">Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- 🗑️ Delete Class Advisory Modal -->
<div id="deleteClassModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-red-500">
    <h2 class="text-xl sm:text-2xl mb-4 text-red-600">Delete Advisory Class</h2>
    <form id="deleteClassForm">
      <input type="hidden" name="id" id="deleteClassId">
      <p class="text-sm text-gray-700 mb-6">
        Are you sure you want to delete <strong id="deleteClassLabel" class="text-red-600"></strong>?
        This action cannot be undone.
      </p>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelDeleteClassBtn" class="px-3 py-1 text-red-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-red-600 hover:bg-red-700 rounded text-sm cursor-pointer">Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- ✏️ Edit Advisory Class Modal -->
<div id="editClassModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm sm:max-w-md border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4 text-emerald-600">Edit Advisory Class</h2>
    <form id="editClassForm">
      <!-- 🔒 Hidden Inputs -->
      <input type="hidden" name="id" id="editClassId">
      <input type="hidden" name="school_year_id" id="editClassSchoolYearId">

      <!-- 📅 School Year (Fixed Display) -->
      <label class="block mb-2 text-sm font-medium">School Year</label>
      <div id="editClassSchoolYearLabel" class="mb-3 px-3 py-2 border rounded bg-gray-100 text-gray-700">
        <!-- Injected via JS -->
      </div>

      <!-- 🏫 Grade Level -->
      <label class="block mb-2 text-sm font-medium">Grade Level</label>
      <select name="grade_level" id="editGradeLevelSelect" required class="block w-full mb-3 border rounded px-3 py-2">
        <option value="">Select grade level</option>
        <!-- Options injected via JS -->
      </select>

      <!-- 🧩 Section -->
      <label class="block mb-2 text-sm font-medium">Section</label>
      <select name="section_id" id="editSectionSelect" required class="block w-full mb-4 border rounded px-3 py-2" disabled>
        <option value="">Select section</option>
        <!-- Options injected via JS -->
      </select>

      <!-- 🎯 Actions -->
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelEditClassBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-emerald-600 hover:bg-emerald-700 rounded text-sm cursor-pointer">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- 🗑️ Delete Student Modal -->
<div id="deleteStudentModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-red-500">
    <h2 class="text-xl sm:text-2xl mb-4 text-red-600">Delete Student</h2>
    <form id="deleteStudentForm">
      <input type="hidden" name="id" id="deleteStudentId">
      <p class="text-sm text-gray-700 mb-6">
        Are you sure you want to delete <strong id="deleteStudentLabel" class="text-red-600"></strong>?
        This action cannot be undone.
      </p>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelDeleteStudentBtn" class="px-3 py-1 text-red-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-red-600 hover:bg-red-700 rounded text-sm cursor-pointer">Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- ➕ Add Existing Student Modal -->
<div id="addStudentModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-xl border border-emerald-500">
    <h2 class="text-xl sm:text-2xl mb-4">Add Student to Advisory</h2>
    <div id="availableStudentList" class="space-y-3 max-h-[400px] overflow-y-auto">
      <!-- Student rows will be injected by JS -->
    </div>

    <!-- 🔻 Fallback Message -->
    <div id="noAvailableStudents" class="hidden text-sm text-gray-500 text-center mt-4">
      No available students found for this advisory class.
    </div>

    <div class="flex justify-end gap-2 mt-4">
      <button type="button" id="cancelAddStudentBtn" class="px-3 py-1 text-emerald-700 rounded hover:bg-emerald-100 text-sm cursor-pointer">Cancel</button>
    </div>
  </div>
</div>

<!-- 🗑️ Delete Student Advisory Modal -->
<div id="deleteStudentClassModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 sm:px-0 opacity-0 transition-opacity duration-200">
  <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
  <div class="relative z-10 bg-white p-4 sm:p-6 rounded-2xl shadow-md w-full max-w-sm border border-red-500">
    <h2 class="text-xl sm:text-2xl mb-4 text-red-700">Remove Student from Advisory</h2>
    <form id="deleteStudentClassForm">
      <input type="hidden" name="student_id" id="deleteStudentClassStudentId">
      <input type="hidden" name="class_id" id="deleteStudentClassId" value="<?= isset($class['id']) ? htmlspecialchars($class['id']) : '' ?>">

      <p class="text-sm text-gray-700 mb-6">
        Are you sure you want to remove <span id="deleteStudentName" class="font-semibold text-red-600"></span> from this advisory class?
      </p>

      <div class="flex justify-end gap-2">
        <button type="button" id="cancelDeleteStudentClassBtn" class="px-3 py-1 text-red-700 rounded hover:bg-red-100 text-sm cursor-pointer">Cancel</button>
        <button type="submit" class="px-3 py-1 text-white bg-red-600 hover:bg-red-700 rounded text-sm cursor-pointer">Remove</button>
      </div>
    </form>
  </div>
</div>