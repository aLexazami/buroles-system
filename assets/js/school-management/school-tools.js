import { initGradeLevelEditHandler, initGradeSectionEditHandler, initGradeSectionDeleteModal, initGradeLevelDeleteModal, initSchoolYearEditHandler, initSchoolYearDeleteModal } from '../modal.js';
import { renderFlash } from '../flash.js';

export function refreshGradeLevels() {
  const container = document.getElementById('gradeLevelTableBody');
  if (!container) return;

  fetch('/api/get-grade-levels.php')
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data.gradeLevels)) {
        renderFlash('error', 'Failed to load grade levels.');
        return;
      }

      container.innerHTML = '';

      data.gradeLevels.forEach(g => {
        const row = document.createElement('tr');
        row.className = 'border-t hover:bg-emerald-100 transition-all duration-300';

        row.innerHTML = `
          <td class="px-4 py-2">${g.level}</td>
          <td class="px-4 py-2">${g.label}</td>
          <td class="px-4 py-2 flex gap-3 flex-wrap justify-start sm:justify-center">
            <!-- Edit Icon -->
            <div class="relative">
              <button type="button" class="peer edit-grade-level rounded-full p-2 hover:bg-blue-100 hover:scale-110 transition-transform duration-200 cursor-pointer"
                data-id="${g.id}" data-level="${g.level}" data-label="${g.label}">
                <img src="/assets/img/edit-icon.png" alt="Edit" class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Edit
              </div>
            </div>

            <!-- Delete Icon -->
            <div class="relative">
              <button type="button" class="peer delete-grade-level rounded-full p-2 hover:bg-red-100 hover:scale-110 transition-transform duration-200 cursor-pointer"
                data-id="${g.id}" data-label="${g.label}">
                <img src="/assets/img/delete-icon.png" alt="Delete" class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Delete
              </div>
            </div>
          </td>
        `;
        container.appendChild(row);
      });

      initGradeLevelEditHandler();
      initGradeLevelDeleteModal();
    })
    .catch(() => {
      renderFlash('error', 'Error loading grade levels.');
    });
}

export function refreshGradeSections() {
  const container = document.getElementById('gradeSectionTableBody');
  const fallback = document.getElementById('sectionFallback');
  if (!container || !fallback) return;

  fetch('/api/get-grade-sections.php')
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data.gradeSections)) {
        renderFlash('error', 'Failed to load grade sections.');
        return;
      }

      container.innerHTML = '';

      if (data.gradeSections.length === 0) {
        fallback.classList.remove('hidden');
        fallback.classList.add('flex');
        return;
      }

      fallback.classList.remove('flex');
      fallback.classList.add('hidden');

      data.gradeSections.forEach(s => {
        const row = document.createElement('tr');
        row.className = 'border-t hover:bg-emerald-100 transition-all duration-300';

        row.innerHTML = `
          <td class="px-4 py-2">${s.grade_label}</td>
          <td class="px-4 py-2">${s.section_label}</td>
          <td class="px-4 py-2 flex gap-3 flex-wrap justify-start sm:justify-center">
            <!-- Edit Icon -->
            <div class="relative">
              <button type="button" class="peer edit-grade-section rounded-full p-2 hover:bg-blue-100 hover:scale-110 transition-transform duration-200 cursor-pointer"
                data-id="${s.id}" data-grade-level-id="${s.grade_level_id}" data-label="${s.section_label}">
                <img src="/assets/img/edit-icon.png" alt="Edit" class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Edit
              </div>
            </div>

            <!-- Delete Icon -->
            <div class="relative">
              <button type="button" class="peer delete-grade-section rounded-full p-2 hover:bg-red-100 hover:scale-110 transition-transform duration-200 cursor-pointer"
                data-id="${s.id}" data-label="${s.section_label}">
                <img src="/assets/img/delete-icon.png" alt="Delete" class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Delete
              </div>
            </div>
          </td>
        `;
        container.appendChild(row);
      });

      initGradeSectionEditHandler();
      initGradeSectionDeleteModal();
    })
    .catch(() => {
      renderFlash('error', 'Error loading grade sections.');
    });
}

export function refreshSchoolYears() {
  const container = document.getElementById('schoolYearTableBody');
  const fallback = document.getElementById('schoolYearFallback');
  if (!container || !fallback) return;

  fetch('/api/get-school-years.php')
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data.schoolYears)) {
        renderFlash('error', 'Failed to load school years.');
        return;
      }

      container.innerHTML = '';

      if (data.schoolYears.length === 0) {
        fallback.classList.remove('hidden');
        fallback.classList.add('flex');
        return;
      }

      fallback.classList.remove('flex');
      fallback.classList.add('hidden');

      const sortedYears = [...data.schoolYears].sort((a, b) => {
        if (a.is_active !== b.is_active) {
          return b.is_active - a.is_active;
        }
        return new Date(b.start_date) - new Date(a.start_date);
      });

      sortedYears.forEach(sy => {
        const row = document.createElement('tr');
        row.classList.add('border-t', 'hover:bg-emerald-100' ,'transition-all', 'duration-300');
        if (sy.is_active) {
          row.classList.add('bg-emerald-100', 'ring-1', 'ring-green-300', 'font-semibold');
        }

        row.innerHTML = `
          <td class="px-4 py-2">${sy.label}</td>
          <td class="px-4 py-2">${formatDate(sy.start_date)}</td>
          <td class="px-4 py-2">${formatDate(sy.end_date)}</td>
          <td class="px-4 py-2">
            <button class="toggle-status cursor-pointer text-xs px-2 py-1 rounded ${
              sy.is_active ? 'bg-emerald-300' : 'bg-gray-100 text-gray-600'
            }" data-id="${sy.id}" data-active="${sy.is_active}">
              ${sy.is_active ? 'Active' : 'Inactive'}
            </button>
          </td>
          <td class="px-4 py-2 flex flex-wrap gap-2 justify-start sm:justify-center">
            <!-- Edit Icon -->
            <div class="relative">
              <button type="button" class="peer edit-school-year rounded-full p-2 hover:bg-blue-100 hover:scale-110 transition-transform duration-200 cursor-pointer"
                data-id="${sy.id}" data-label="${sy.label}" data-start="${sy.start_date}" data-end="${sy.end_date}" data-active="${sy.is_active}">
                <img src="/assets/img/edit-icon.png" alt="Edit" class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Edit
              </div>
            </div>

            <!-- Delete Icon -->
            <div class="relative">
              <button type="button" class="peer delete-school-year rounded-full p-2 hover:bg-red-100 hover:scale-110 transition-transform duration-200 cursor-pointer"
                data-id="${sy.id}" data-label="${sy.label}">
                <img src="/assets/img/delete-icon.png" alt="Delete" class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 text-white text-xs rounded whitespace-nowrap opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Delete
              </div>
            </div>
          </td>
        `;
        container.appendChild(row);
      });

      initSchoolYearEditHandler();
      initSchoolYearDeleteModal();
      initSchoolYearStatusToggle(sortedYears);
    })
    .catch(() => {
      renderFlash('error', 'Error loading school years.');
    });
}

export function initSchoolYearStatusToggle(schoolYears) {
  const toggleButtons = document.querySelectorAll('.toggle-status');

  toggleButtons.forEach(btn => {
    if (btn.dataset.bound) return;

    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const currentStatus = btn.dataset.active === '1';
      const newStatus = currentStatus ? 0 : 1;

      // ✅ Prevent deactivation if it's the only active year
      const activeCount = schoolYears.filter(sy => sy.is_active === 1).length;
      if (currentStatus && activeCount === 1) {
        renderFlash('error', 'At least one school year must remain active.');
        return;
      }

      fetch('/controllers/admin/toggle-school-year-status.php', {
        method: 'POST',
        body: new URLSearchParams({ id, is_active: newStatus })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            renderFlash('success', 'School year status updated.');
            refreshSchoolYears();
          } else {
            renderFlash('error', data.error || 'Failed to update status.');
          }
        })
        .catch(() => {
          renderFlash('error', 'Error updating school year status.');
        });
    });

    btn.dataset.bound = 'true';
  });
}

// 📅 Format date helper
function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}