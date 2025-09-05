import { setupMenuToggle } from './menu-toggle.js';
import { setupRoleSwitcher } from './role-switcher.js';
import { setupUserActions } from './user-actions.js';
import { setupTableSearch } from './search-filter.js';
import { initExportDropdown } from '/assets/js/export-button.js';


document.addEventListener('DOMContentLoaded', () => {
  setupMenuToggle();
  setupRoleSwitcher();
  setupUserActions();
  initExportDropdown();

  if (document.getElementById('userSearch') && document.getElementById('clearSearch')) {
    setupTableSearch('userSearch', 'clearSearch', 'table');
  }
});