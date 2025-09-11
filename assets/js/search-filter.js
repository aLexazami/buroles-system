export function setupTableSearch(inputId, clearId, tableSelector) {
  const searchInput = document.getElementById(inputId);
  const clearButton = document.getElementById(clearId);
  const rows = document.querySelectorAll(`${tableSelector} tbody tr`);

  if (searchInput && clearButton && rows.length > 0) {
    let debounceTimer;

    function filterRows(query) {
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
      });
    }

    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        filterRows(searchInput.value.toLowerCase());
      }, 300);
    });

    clearButton.addEventListener('click', () => {
      searchInput.value = '';
      filterRows('');
    });
  }
}

export function setupListSearch(inputId, clearId, itemSelector) {
  const searchInput = document.getElementById(inputId);
  const clearButton = document.getElementById(clearId);
  const items = document.querySelectorAll(itemSelector);

  if (searchInput && clearButton && items.length > 0) {
    let debounceTimer;

    function filterItems(query) {
      items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
      });
    }

    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        filterItems(searchInput.value.toLowerCase());
      }, 300);
    });

    clearButton.addEventListener('click', () => {
      searchInput.value = '';
      filterItems('');
    });
  }
}