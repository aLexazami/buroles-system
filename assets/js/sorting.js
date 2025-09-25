import { highlightSortedColumn } from './highlights.js';

let userTriggeredSort = false;

export function loadFeedbackTable(sortBy = 'id', sortOrder = 'desc') {
  const container = document.getElementById('feedbackTableContainer');
  const url = `/ajax/fetch-feedback-table.php?sort_by=${sortBy}&sort_order=${sortOrder}`;

  fetch(url)
    .then(res => res.text())
    .then(html => {
      container.innerHTML = html;
      attachSortListeners();
      highlightSortedColumn(sortBy, userTriggeredSort); // ✅ only highlight if user clicked
      userTriggeredSort = false; // reset after render
    });
}

export function attachSortListeners() {
  document.querySelectorAll('.sort-button').forEach(button => {
    button.addEventListener('click', () => {
      const column = button.dataset.column;
      const order = button.dataset.order;
      userTriggeredSort = true; // ✅ mark that user clicked
      loadFeedbackTable(column, order);
    });
  });
}