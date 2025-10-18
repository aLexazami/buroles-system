import { initGradeLevelEditHandler, initGradeSectionEditHandler, initGradeSectionDeleteModal, initGradeLevelDeleteModal } from './modal.js';
import { renderFlash } from './flash.js';

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

      // Clear existing rows
      container.innerHTML = '';

      // Rebuild rows
      data.gradeLevels.forEach(g => {
        const row = document.createElement('tr');
        row.className = 'border-t hover:bg-gray-50';
        row.innerHTML = `
          <td class="px-4 py-2">${g.level}</td>
          <td class="px-4 py-2">${g.label}</td>
          <td class="px-4 py-2 space-x-2">
            <button class="edit-grade-level text-blue-600 hover:underline text-xs"
              data-id="${g.id}" data-level="${g.level}" data-label="${g.label}">
              Edit
            </button>
            <button class="delete-grade-level text-red-600 hover:underline text-xs"
              data-id="${g.id}" data-label="${g.label}">
              Delete
            </button>
          </td>
        `;
        container.appendChild(row);
      });

      // Re-bind modal triggers
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

      fallback.classList.remove('flex');   // ✅ remove layout
      fallback.classList.add('hidden');    // ✅ hide element

      data.gradeSections.forEach(s => {
        const row = document.createElement('tr');
        row.className = 'border-t hover:bg-gray-50';
        row.innerHTML = `
    <td class="px-4 py-2">${s.grade_label}</td>
    <td class="px-4 py-2">${s.section_label}</td>
    <td class="px-4 py-2 space-x-2">
      <button class="edit-grade-section text-blue-600 hover:underline text-xs"
        data-id="${s.id}" data-grade-level-id="${s.grade_level_id}" data-label="${s.section_label}">
        Edit
      </button>
      <button class="delete-grade-section text-red-600 hover:underline text-xs"
        data-id="${s.id}" data-label="${s.section_label}">
        Delete
      </button>
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