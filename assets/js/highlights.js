export function highlightSortedColumn(sortBy, shouldHighlight = false) {
  if (!sortBy || !shouldHighlight) return;

  document.querySelectorAll(`td[data-column="${sortBy}"]`).forEach(cell => {
    cell.classList.add('bg-emerald-100');
  });

  document.querySelectorAll(`th[data-column="${sortBy}"]`).forEach(header => {
    header.classList.add('bg-emerald-700');
  });
}