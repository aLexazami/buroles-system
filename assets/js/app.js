import { setupMenuToggle } from './menu-toggle.js';
import { setupRoleSwitcher } from './role-switcher.js';
import { setupUserActions } from './user-actions.js';
import { setupTableSearch } from './search-filter.js';
import { initExportDropdown } from '/assets/js/export-button.js';
import { setupListSearch } from './search-filter.js';
import { initDropdownMenus } from './dropdown.js';
import { initRenameButtons } from './modal.js';
import { initDeleteButtons } from './modal.js';
import { initCreateFolderModal} from './folder-creation.js';
import { initUploadActions } from './upload.js';





document.addEventListener('DOMContentLoaded', () => {
  setupMenuToggle();
  setupRoleSwitcher();
  setupUserActions();
  initExportDropdown();
  initDropdownMenus();
  initRenameButtons();
  initCreateFolderModal();
  initDeleteButtons();
  initUploadActions();



  setupListSearch('folderSearch', 'clearFolderSearch', '#itemList .item');

  if (document.getElementById('userSearch') && document.getElementById('clearSearch')) {
    setupTableSearch('userSearch', 'clearSearch', 'table');
  }
});