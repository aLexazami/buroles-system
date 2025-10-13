export function initFileSearch() {
  const input = document.getElementById('folderSearch');
  const clear = document.getElementById('clearFolderSearch');
  const container = document.getElementById('file-list');
  const fallbackId = 'no-search-results';

  if (!input || !clear || !container) return;

  function getRows() {
    return container.querySelectorAll('[data-item-id]');
  }

  function filter(query) {
    const rows = getRows();
    let visibleCount = 0;

    rows.forEach(row => {
      const name = row.dataset.name?.toLowerCase() || '';
      const match = name.includes(query);
      row.style.display = match ? '' : 'none';
      if (match) visibleCount++;
    });

    const existing = document.getElementById(fallbackId);
    if (visibleCount === 0 && !existing) {
      const msg = document.createElement('div');
      msg.id = fallbackId;
      msg.className = 'text-center text-gray-500 py-12';
      msg.textContent = 'No matching files found.';
      container.appendChild(msg);
    } else if (visibleCount > 0 && existing) {
      existing.remove();
    }
  }

  input.addEventListener('input', () => {
    const query = input.value.trim().toLowerCase();
    filter(query);
  });

  clear.addEventListener('click', () => {
    input.value = '';
    filter('');
  });
}