import { initClassDeleteModal,initClassEditModal } from '../modal.js';
import { renderFlash } from '../flash.js';

export function refreshAdvisoryGrid(adviserId) {
  const schoolYearSelect = document.getElementById('schoolYearFilter');
  const schoolYearId = schoolYearSelect?.value || '';

  fetch(`/ajax/get-classes.php?adviser_id=${adviserId}&school_year_id=${schoolYearId}`)
    .then(res => res.json())
    .then(data => {
      const grid = document.getElementById('advisoryClassGrid');
      if (!grid || !data.classes) return;

      if (data.classes.length === 0) {
        grid.innerHTML = '<p class="text-gray-500 text-sm mt-4">No advisory classes created yet.</p>';
        return;
      }

      const rows = data.classes.map(cls => `
        <div class="advisory-row flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-l-10 border-emerald-700 hover:bg-emerald-100 bg-white rounded-lg shadow hover:shadow-md transition px-4 py-3 mb-3 group cursor-pointer"
             data-href="/pages/admin/student-list.php?class_id=${cls.id}">

          <!-- 📘 Class Info -->
          <div class="flex items-center gap-3 flex-1 min-w-0">
            <img src="/assets/img/class-advisory1.png" class="w-15 h-15 shrink-0" alt="Class Icon">
            <span title="${cls.name}" class="text-sm sm:text-xl group-hover:font-semibold text-gray-800 group-hover:text-emerald-600 whitespace-normal break-words">
              ${cls.name}
            </span>
          </div>

          <!-- 🛠️ Action Buttons -->
          <div class="flex gap-3 text-xs shrink-0 items-center">
            <!-- Status Toggle -->
            <button class="toggle-class-status px-2 py-1 rounded font-semibold ${cls.is_active ? 'bg-emerald-300 text-green-800' : 'bg-gray-200 text-gray-600'}"
                    data-id="${cls.id}" data-active="${cls.is_active}">
              ${cls.is_active ? 'Active' : 'Inactive'}
            </button>

            <!-- Edit Icon -->
            <div class="relative">
              <button type="button" data-action="edit-class" data-id="${cls.id}" class="peer rounded-full p-2 hover:bg-blue-100 hover:scale-110 transition-transform duration-200 cursor-pointer">
                <img src="/assets/img/edit-icon.png" alt="Edit" class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Edit
              </div>
            </div>

            <!-- Delete Icon -->
            <div class="relative">
              <button type="button" data-action="delete-class" data-id="${cls.id}" data-label="${cls.name}" class="peer delete-class rounded-full p-2 hover:bg-red-100 hover:scale-110 transition-transform duration-200 cursor-pointer">
                <img src="/assets/img/delete-icon.png" alt="Delete" class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Delete
              </div>
            </div>
          </div>
        </div>
      `).join('');

      grid.innerHTML = `<div class="w-full">${rows}</div>`;

      // 🧩 Attach row click listeners
      document.querySelectorAll('.advisory-row').forEach(row => {
        row.addEventListener('click', e => {
          if (e.target.closest('button')) return;
          const href = row.getAttribute('data-href');
          if (href) window.location.href = href;
        });
      });

      // 🧩 Bind modals
      initClassDeleteModal();
      initClassEditModal();

      // 🧩 Bind status toggles
      document.querySelectorAll('.toggle-class-status').forEach(btn => {
        if (btn.dataset.bound) return;

        btn.addEventListener('click', () => {
          const id = btn.dataset.id;
          const currentStatus = btn.dataset.active === '1';
          const newStatus = currentStatus ? 0 : 1;

          fetch('/controllers/admin/toggle-class-status.php', {
            method: 'POST',
            body: new URLSearchParams({ id, is_active: newStatus })
          })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                renderFlash('success', 'Class status updated.');
                refreshAdvisoryGrid(adviserId);
              } else {
                renderFlash('error', data.error || 'Failed to update status.');
              }
            });

          btn.dataset.bound = 'true';
        });
      });
    });
}

export function initAdvisoryGrid() {
  const adviserId = document.querySelector('input[name="user_id"]')?.value;
  const schoolYearSelect = document.getElementById('schoolYearFilter');

  if (adviserId) {
    refreshAdvisoryGrid(adviserId);

    schoolYearSelect?.addEventListener('change', () => {
      refreshAdvisoryGrid(adviserId);
    });
  }
}