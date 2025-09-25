import { highlightSortedColumn } from './highlights.js';

let userTriggeredSort = false;

export function setupTableSorter({ containerId, endpoint }) {
  const container = document.getElementById(containerId);
  if (!container) return;

  function loadTable(sortBy = 'id', sortOrder = 'desc') {
    const url = `${endpoint}?sort_by=${sortBy}&sort_order=${sortOrder}`;

    fetch(url)
      .then(res => res.text())
      .then(html => {
        container.innerHTML = html;
        attachSortListeners();
        highlightSortedColumn(sortBy, userTriggeredSort);
        userTriggeredSort = false;
      })
      .catch(err => console.error(`Failed to load table for ${containerId}:`, err));
  }

  function attachSortListeners() {
    container.querySelectorAll('.sort-button').forEach(button => {
      button.addEventListener('click', () => {
        const column = button.dataset.column;
        const order = button.dataset.order;
        userTriggeredSort = true;
        loadTable(column, order);
      });
    });
  }

  loadTable(); // Initial load
}