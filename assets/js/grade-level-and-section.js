import { initGradeLevelEditHandler } from './modal.js';
import { initGradeLevelDeleteModal } from './modal.js';
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