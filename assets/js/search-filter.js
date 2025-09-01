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