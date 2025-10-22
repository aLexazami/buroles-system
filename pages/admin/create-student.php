<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/head.php';
require_once __DIR__ . '/../../helpers/flash.php';

$errors = getFlash('form_errors') ?? [];
$old = getFlash('form_data') ?? [];

// 🧠 Fetch dropdown data
$schoolYears = $pdo->query("SELECT id, label, is_active FROM school_years ORDER BY start_year DESC")->fetchAll(PDO::FETCH_ASSOC);
$gradeLevels = $pdo->query("SELECT id, label FROM grade_levels ORDER BY level ASC")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Find the active school year safely
$activeSchoolYear = null;
foreach ($schoolYears as $sy) {
  if (isset($sy['is_active']) && (int)$sy['is_active'] === 1) {
    $activeSchoolYear = $sy;
    break;
  }
}

renderHead('Admin');
?>

<body class="bg-gray-100 min-h-screen flex flex-col">
  <?php include('../../includes/header.php'); ?>
  <?php showFlash() ?>

  <main class="grid grid-cols-1 md:grid-cols-[auto_1fr]">
    <?php include('../../includes/side-nav-admin3.php'); ?>

    <section class="p-4 sm:p-6 md:p-8">
      <div class="bg-emerald-300 flex justify-center items-center gap-2 p-2 mb-5 rounded">
        <img src="/assets/img/student.png" class="w-6 h-6" alt="Student Icon">
        <h1 class="font-bold text-lg md:text-xl">Add New Student</h1>
      </div>

      <form action="/controllers/admin/create-student.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6 space-y-8">
        <!-- 🖼️ Profile Photo Upload -->
        <div class="flex justify-center">
          <div class="relative group">
            <img id="photoPreview" src="/assets/img/default-avatar.png" alt="Student Photo"
              class="w-28 h-28 rounded-full object-cover border-4 <?= isset($errors['photo']) ? 'border-red-500' : 'border-gray-300' ?> cursor-pointer hover:border-emerald-500 transition">
            <input type="file" name="photo" id="photoInput" accept="image/*" class="hidden">
            <div class="absolute bottom-0 right-0 bg-emerald-600 text-white text-xs px-2 py-1 rounded-full opacity-0 group-hover:opacity-100 transition">Change</div>
          </div>
        </div>
        <?php if (isset($errors['photo'])): ?>
          <p class="text-red-600 text-sm text-center mt-2"><?= $errors['photo'] ?></p>
        <?php endif; ?>

        <!-- 🧍 Personal Info -->
        <div>
          <h2 class="text-lg font-semibold mb-4 border-b pb-2">Personal Information</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <!-- LRN -->
            <div class="relative">
              <input
                type="text"
                name="lrn"
                id="lrn"
                maxlength="12"
                pattern="\d{12}"
                inputmode="numeric"
                value="<?= htmlspecialchars($old['lrn'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['lrn']) ? 'border-red-500 focus:border-red-500' : 'border-gray-300' ?> focus:border-3  focus:border-emerald-500 rounded-md  focus:outline-none placeholder-transparent"
                placeholder="LRN">
              <label for="lrn"
                class="absolute left-4 top-2 text-sm text-gray-500 transition-all
                peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">
                LRN
              </label>
              <?php if (isset($errors['lrn'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['lrn'] ?></p>
              <?php endif; ?>
            </div>

            <!-- First Name -->
            <div class="relative">
              <input type="text" name="first_name" id="first_name"
                value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['first_name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="First Name">
              <label for="first_name" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">First Name</label>
              <?php if (isset($errors['first_name'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['first_name'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Middle Name -->
            <div class="relative">
              <input type="text" name="middle_name" id="middle_name"
                value="<?= htmlspecialchars($old['middle_name'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Middle Name">
              <label for="middle_name" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Middle Name</label>
            </div>

            <!-- Last Name -->
            <div class="relative">
              <input type="text" name="last_name" id="last_name"
                value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['last_name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Last Name">
              <label for="last_name" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Last Name</label>
              <?php if (isset($errors['last_name'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['last_name'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Date of Birth -->
            <div class="relative">
              <input type="date" name="dob" id="dob"
                value="<?= htmlspecialchars($old['dob'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['dob']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent">
              <label for="dob" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Date of Birth</label>
              <?php if (isset($errors['dob'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['dob'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Gender -->
            <div class="relative">
              <select name="gender" id="gender"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['gender']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent">
                <option value="" disabled>Select Gender</option>
                <?php foreach (['male', 'female', 'other'] as $g): ?>
                  <option value="<?= $g ?>" <?= ($old['gender'] ?? '') === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                <?php endforeach; ?>
              </select>
              <label for="gender" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Gender</label>
              <?php if (isset($errors['gender'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['gender'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Nationality -->
            <div class="relative">
              <input type="text" name="nationality" id="nationality"
                value="<?= htmlspecialchars($old['nationality'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Nationality">
              <label for="nationality" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Nationality</label>
            </div>

            <!-- Barangay -->
            <div class="relative">
              <input type="text" name="barangay" id="barangay"
                value="<?= htmlspecialchars($old['barangay'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Barangay">
              <label for="barangay" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Barangay</label>
            </div>

            <!-- Contact Number -->
            <div class="relative">
              <input type="text" name="contact_number" id="contact_number"
                value="<?= htmlspecialchars($old['contact_number'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['contact_number']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Contact Number">
              <label for="contact_number"
                class="absolute left-4 top-2 text-sm text-gray-500 transition-all
                peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">
                Contact Number
              </label>
              <?php if (isset($errors['contact_number'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['contact_number'] ?></p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Address -->
          <div class="mt-4">
            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Full Address</label>
            <textarea name="address" id="address" rows="3"
              class="w-full resize-none px-4 py-2 border <?= isset($errors['address']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
            <?php if (isset($errors['address'])): ?>
              <p class="text-red-600 text-sm mt-1"><?= $errors['address'] ?></p>
            <?php endif; ?>
          </div>
        </div>

        <!-- 👨‍👩‍👧 Guardian Info -->
        <div>
          <h2 class="text-lg font-semibold mb-4 border-b pb-2">Guardian Information</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Guardian Name -->
            <div class="relative">
              <input type="text" name="guardian_name" id="guardian_name"
                value="<?= htmlspecialchars($old['guardian_name'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['guardian_name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Guardian Name">
              <label for="guardian_name" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Guardian Name</label>
              <?php if (isset($errors['guardian_name'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['guardian_name'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Relationship -->
            <div class="relative">
              <input type="text" name="relationship" id="relationship"
                value="<?= htmlspecialchars($old['relationship'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['relationship']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Relationship">
              <label for="relationship" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Relationship</label>
              <?php if (isset($errors['relationship'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['relationship'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Guardian Contact -->
            <div class="relative">
              <input type="text" name="guardian_contact" id="guardian_contact"
                value="<?= htmlspecialchars($old['guardian_contact'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['guardian_contact']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Contact Number">
              <label for="guardian_contact" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Contact Number</label>
              <?php if (isset($errors['guardian_contact'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['guardian_contact'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Guardian Email -->
            <div class="relative">
              <input type="email" name="guardian_email" id="guardian_email"
                value="<?= htmlspecialchars($old['guardian_email'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['guardian_email']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Email Address">
              <label for="guardian_email" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Email Address</label>
              <?php if (isset($errors['guardian_email'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['guardian_email'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Occupation -->
            <div class="relative">
              <input type="text" name="occupation" id="occupation"
                value="<?= htmlspecialchars($old['occupation'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Occupation">
              <label for="occupation" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Occupation</label>
            </div>

            <!-- Employer -->
            <div class="relative">
              <input type="text" name="employer" id="employer"
                value="<?= htmlspecialchars($old['employer'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent"
                placeholder="Employer">
              <label for="employer" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Employer</label>
            </div>

            <!-- Emergency Contact Checkbox -->
            <label class="flex items-center gap-2 mt-2">
              <input type="checkbox" name="is_emergency_contact" value="1" class="accent-emerald-600" <?= isset($old['is_emergency_contact']) ? 'checked' : '' ?>>
              <span class="text-sm text-gray-700">Emergency Contact</span>
            </label>
          </div>
        </div>

        <!-- 🏫 Enrollment Info -->
        <div>
          <h2 class="text-lg font-semibold mb-4 border-b pb-2">Enrollment Information</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- School Year (Fixed to Active) -->
            <div class="relative">
              <select name="school_year_id"
                class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed"
                readonly disabled>
                <?php if ($activeSchoolYear): ?>
                  <option value="<?= $activeSchoolYear['id'] ?>" selected>
                    <?= htmlspecialchars($activeSchoolYear['label']) ?>
                  </option>
                <?php else: ?>
                  <option value="" disabled selected>No active school year</option>
                <?php endif; ?>
              </select>
              <label class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">
                School Year
              </label>
            </div>

            <?php if ($activeSchoolYear): ?>
              <!-- Hidden input to preserve value for submission -->
              <input type="hidden" name="school_year_id" value="<?= $activeSchoolYear['id'] ?>">
            <?php endif; ?>


            <!-- Grade Level -->
            <div class="relative">
              <select name="grade_level_id" id="gradeLevelSelect" class="peer w-full px-4 pt-6 pb-2 border border-gray-300 rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent">
                <option value="" disabled>Select Grade Level</option>
                <?php foreach ($gradeLevels as $gl): ?>
                  <option value="<?= $gl['id'] ?>" <?= ($old['grade_level_id'] ?? '') == $gl['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($gl['label']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <label class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">Grade Level</label>
            </div>

            <!-- Section -->
            <div class="relative">
              <select
                name="grade_section_id"
                id="sectionSelect"
                data-old="<?= htmlspecialchars($old['grade_section_id'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['grade_section_id']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3 focus:border-emerald-500 focus:outline-none placeholder-transparent"
                <?= empty($old['grade_level_id']) ? 'disabled' : '' ?>>
                <option value="" disabled>Select Section</option>
                <?php if (!empty($sections)): ?>
                  <?php foreach ($sections as $section): ?>
                    <option value="<?= $section['id'] ?>" <?= ($old['grade_section_id'] ?? '') == $section['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($section['section_label']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
              <label for="grade_section_id"
                class="absolute left-4 top-2 text-sm text-gray-500 transition-all
    peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">
                Section
              </label>
              <?php if (isset($errors['grade_section_id'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['grade_section_id'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Previous School -->
            <div class="relative">
              <input type="text" name="previous_school" id="previous_school"
                value="<?= htmlspecialchars($old['previous_school'] ?? '') ?>"
                placeholder="Previous School (optional)"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['previous_school']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent">
              <label for="previous_school"
                class="absolute left-4 top-2 text-sm text-gray-500 transition-all
               peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400">
                Previous School (optional)
              </label>
              <?php if (isset($errors['previous_school'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['previous_school'] ?></p>
              <?php endif; ?>
            </div>

            <!-- Enrollment Date -->
            <div class="relative">
              <input type="date" name="enrollment_date" id="enrollment_date"
                value="<?= htmlspecialchars($old['enrollment_date'] ?? '') ?>"
                class="peer w-full px-4 pt-6 pb-2 border <?= isset($errors['enrollment_date']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md focus:border-3  focus:border-emerald-500 focus:outline-none placeholder-transparent">
              <label for="enrollment_date"
                class="absolute left-4 top-2 text-sm text-gray-500 peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 transition-all">
                Enrollment Date
              </label>
              <?php if (isset($errors['enrollment_date'])): ?>
                <p class="text-red-600 text-sm mt-1"><?= $errors['enrollment_date'] ?></p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- ✅ Submit -->
        <div class="pt-4 flex justify-end">
          <button type="submit"
            class="flex items-center gap-2 bg-emerald-600 text-white px-6 py-2 rounded-md hover:bg-emerald-700 transition cursor-pointer">
            <span>Save Student</span>
          </button>
        </div>
      </form>

      <!-- Optional: JS enforcement for LRN -->
      <script>
        document.getElementById('lrn')?.addEventListener('input', function() {
          this.value = this.value.replace(/\D/g, '').slice(0, 12);
        });
      </script>
    </section>
  </main>

  <?php include('../../includes/footer.php'); ?>

  <script src="/assets/js/auto-dismiss-alert.js"></script>
  <script type="module" src="/assets/js/app.js"></script>
  <script src="/assets/js/date-time.js"></script>
<script type="module">
  // 🧠 Dynamic section loader
  const gradeLevelSelect = document.getElementById('gradeLevelSelect');
  const sectionSelect = document.getElementById('sectionSelect');

  function loadSections(gradeLevelId, restoreSectionId = null) {
    sectionSelect.innerHTML = '<option value="" disabled>Select Section</option>';
    sectionSelect.disabled = true;

    if (!gradeLevelId) return;

    fetch(`/api/get-sections-dropdown.php?grade_level_id=${gradeLevelId}`)
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data.sections)) {
          data.sections.forEach(section => {
            const option = document.createElement('option');
            option.value = section.id;
            option.textContent = section.section_label;
            sectionSelect.appendChild(option);
          });
          sectionSelect.disabled = false;

          // ✅ Restore previously selected section if available
          if (restoreSectionId) {
            sectionSelect.value = restoreSectionId;
          }
        }
      });
  }

  // 🔄 Trigger on grade level change
  gradeLevelSelect?.addEventListener('change', () => {
    const gradeLevelId = gradeLevelSelect.value;
    loadSections(gradeLevelId);
  });

  // 🚀 Auto-load if grade level is preselected
  if (gradeLevelSelect?.value) {
    const oldSectionId = sectionSelect?.getAttribute('data-old');
    loadSections(gradeLevelSelect.value, oldSectionId);
  }

  // 🖼️ Photo preview logic
  const photoInput = document.getElementById('photoInput');
  const photoPreview = document.getElementById('photoPreview');

  photoPreview?.addEventListener('click', () => photoInput?.click());

  photoInput?.addEventListener('change', () => {
    const file = photoInput.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = e => {
        photoPreview.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }
  });

  // ✨ Auto-format name fields to Title Case
  function toTitleCase(str) {
    return str
      .toLowerCase()
      .replace(/\b\w/g, char => char.toUpperCase());
  }

  document.addEventListener('DOMContentLoaded', () => {
    const nameFields = [
      'first_name',
      'middle_name',
      'last_name',
      'guardian_name',
      'relationship',
      'nationality',
      'barangay',
      'previous_school'
    ];

    nameFields.forEach(id => {
      const input = document.getElementById(id);
      if (input) {
        input.addEventListener('blur', () => {
          input.value = toTitleCase(input.value);
        });
      }
    });

    // Optional: format before submission
    const form = document.querySelector('form');
    form?.addEventListener('submit', () => {
      nameFields.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
          input.value = toTitleCase(input.value);
        }
      });
    });
  });
</script>
</body>

</html>