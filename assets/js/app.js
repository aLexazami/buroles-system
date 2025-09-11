import { setupMenuToggle } from './menu-toggle.js';
import { setupRoleSwitcher } from './role-switcher.js';
import { setupUserActions } from './user-actions.js';
import { setupTableSearch } from './search-filter.js';
import { initExportDropdown } from '/assets/js/export-button.js';
import { setupListSearch } from './search-filter.js';
import { initDropdownMenus } from './dropdown.js';
import { initRenameButtons } from './modal.js';
import { initDeleteButtons } from './delete.js';



document.addEventListener('DOMContentLoaded', () => {
  setupMenuToggle();
  setupRoleSwitcher();
  setupUserActions();
  initExportDropdown();
  initDropdownMenus();
  initRenameButtons();

  setupListSearch('folderSearch', 'clearFolderSearch', '#itemList .item');

  const currentPath = document.body.dataset.currentPath;
  initDeleteButtons(currentPath);

  if (document.getElementById('userSearch') && document.getElementById('clearSearch')) {
    setupTableSearch('userSearch', 'clearSearch', 'table');
  }
});