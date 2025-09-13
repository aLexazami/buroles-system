export function setupSearchFilter({ inputId, clearId, selector, scope = 'textContent' }) {
  const searchInput = document.getElementById(inputId);
  const clearButton = document.getElementById(clearId);
  const elements = document.querySelectorAll(selector);

  if (!searchInput || !clearButton || elements.length === 0) return;

  let debounceTimer;

  function getSearchText(el) {
    return scope === 'dataset'
      ? el.dataset.name?.toLowerCase() || ''
      : el.textContent.toLowerCase();
  }

  function filterElements(query) {
    elements.forEach(el => {
      const text = getSearchText(el);
      el.style.display = text.includes(query) ? '' : 'none';
    });
  }

  searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      filterElements(searchInput.value.toLowerCase());
    }, 300);
  });

  clearButton.addEventListener('click', () => {
    searchInput.value = '';
    filterElements('');
  });
}