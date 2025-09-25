export function highlightSortedColumn() {
  const urlParams = new URLSearchParams(window.location.search);
  const sortBy = urlParams.get('sort_by');

  if (!sortBy) return;

  document.querySelectorAll(`td[data-column="${sortBy}"]`).forEach(cell => {
    cell.classList.add('bg-emerald-100');
  });

  document.querySelectorAll(`th[data-column="${sortBy}"]`).forEach(header => {
    header.classList.add('bg-emerald-700');
  });
}