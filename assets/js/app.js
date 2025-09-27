// UI & Role Toggles
import { setupMenuToggle } from './menu-toggle.js';
import { setupRoleSwitcher } from './role-switcher.js';
import { setupUserActions } from './user-actions.js';

// File Manager Actions
import { initRenameButtons, initDeleteButtons, initPasswordButtons, initUnlockButtons, initAnnouncementModal, initAnnouncementTriggers } from './modal.js';
import { initCreateFolderModal } from './folder-creation.js';
import { initUploadActions } from './upload.js';
import { initExportDropdown } from '/assets/js/export-button.js';
import { initDropdownMenus, setupRecipientDropdown, initNotificationActions } from './dropdown.js';

// Search Filters (Unified)
import { setupSearchFilter } from './search-filter.js';

// Preview
import { setupAvatarPreview } from './avatar-preview.js';
import { initPasswordStrength, toggleVisibility } from './password-utils.js';
import { startBadgePolling } from './badge-updater.js';
import { setupTableSorter } from '/assets/js/table-sorter.js';
import { initAnnouncementCarousel } from './announcementCarousel.js';





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
  initPasswordStrength();
  setupPasswordVisibilityToggles();
  setupRecipientDropdown();
  initNotificationActions();
  initAnnouncementModal();
  initAnnouncementCarousel();
  initAnnouncementTriggers();


  // Badge Updater
  startBadgePolling();

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

  if (document.getElementById('feedbackTableContainer')) {
    setupTableSorter({
      containerId: 'feedbackTableContainer',
      endpoint: '/ajax/fetch-feedback-table.php'
    });
  }

  if (document.getElementById('respondentsTableContainer')) {
    setupTableSorter({
      containerId: 'respondentsTableContainer',
      endpoint: '/ajax/fetch-respondents-table.php'
    });
  }

});

function setupPasswordVisibilityToggles() {
  document.querySelectorAll('[data-toggle-password]').forEach(icon => {
    icon.addEventListener('click', () => {
      const fieldId = icon.getAttribute('data-toggle-password');
      toggleVisibility(fieldId, icon);
    });
  });
}