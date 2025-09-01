import { setupMenuToggle } from './menu-toggle.js';
import { setupRoleSwitcher } from './role-switcher.js';
import { setupUserActions } from './user-actions.js';
import { setupTableSearch } from './search-filter.js';

document.addEventListener('DOMContentLoaded', () => {
  setupMenuToggle();
  setupRoleSwitcher();
  setupUserActions();

  if (document.getElementById('userSearch') && document.getElementById('clearSearch')) {
    setupTableSearch('userSearch', 'clearSearch', 'table');
  }
});