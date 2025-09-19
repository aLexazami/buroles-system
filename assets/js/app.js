// UI & Role Toggles
import { setupMenuToggle } from './menu-toggle.js';
import { setupRoleSwitcher } from './role-switcher.js';
import { setupUserActions } from './user-actions.js';

// File Manager Actions
import { initRenameButtons, initDeleteButtons, initPasswordButtons, initUnlockButtons } from './modal.js';
import { initCreateFolderModal } from './folder-creation.js';
import { initUploadActions } from './upload.js';
import { initExportDropdown } from '/assets/js/export-button.js';
import { initDropdownMenus } from './dropdown.js';

// Search Filters (Unified)
import { setupSearchFilter } from './search-filter.js';

// Preview
import { setupAvatarPreview } from './avatar-preview.js';

document.addEventListener('DOMContentLoaded', () => {
  // 🧭 UI & Role Toggles
  setupMenuToggle();
  setupRoleSwitcher();
  setupUserActions();
  initDropdownMenus();

  // 📁 File Manager Actions
  initRenameButtons();
  initDeleteButtons();
  initCreateFolderModal();
  initUploadActions();
  initExportDropdown();
  initPasswordButtons();
  initUnlockButtons();

  setupAvatarPreview();


  // 🔍 Search Filters
  setupSearchFilter({
    inputId: 'folderSearch',
    clearId: 'clearFolderSearch',
    selector: '#itemList .item'
  });

  setupSearchFilter({
    inputId: 'staffSearch',
    clearId: 'clearStaffSearch',
    selector: '.staff-item',
    scope: 'dataset' // uses data-name attribute
  });

  if (document.getElementById('userSearch') && document.getElementById('clearSearch')) {
    setupSearchFilter({
      inputId: 'userSearch',
      clearId: 'clearSearch',
      selector: 'table tbody tr'
    });
  }


});