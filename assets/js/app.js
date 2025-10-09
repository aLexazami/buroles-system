// UI & Role Toggles
import { setupMenuToggle, setupSidebarToggle, initMenuToggle } from './menu-toggle.js';
import { setupRoleSwitcher } from './role-switcher.js';
import { setupUserActions } from './user-actions.js';

// File Manager Actions
import { initPasswordButtons, initUnlockButtons, initAnnouncementModal, initAnnouncementTriggers } from './modal.js';
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
import { setupAnnouncementPagination } from './announcementCarousel.js';
import { setupRoleCheckboxToggle } from './checkbox.js';
import { startRedirectCountdown } from './redirect-utils.js';
import { initEmailAutocomplete } from './search-autocomplete.js';
import { loadFolder } from './file-manager.js';



document.addEventListener('DOMContentLoaded', () => {
  // 📁 File Manager Initialization
  const folderId = document.body.dataset.folderId || null;
  loadFolder(folderId);

  // 🧭 UI & Role Toggles
  setupRoleSwitcher();
  setupUserActions();
  initDropdownMenus();

  // 📁 File Manager Actions
  initUploadActions();
  initExportDropdown();
  initPasswordButtons();
  initUnlockButtons();

  setupAvatarPreview();
  initPasswordStrength();
  setupPasswordVisibilityToggles();
  setupRecipientDropdown();
  initNotificationActions();
  setupRoleCheckboxToggle();
  setupMobileActionToggle();
  setupAnnouncementPagination();
  initEmailAutocomplete();
  initMenuToggle();


  // 📁 Modal Initializers
  initAnnouncementModal();
  initAnnouncementTriggers();
  initCreateFolderModal();



  // Badge Updater
  startBadgePolling();

  // 🔍 Search Filters
  setupSearchFilter({
    inputId: 'unifiedSearch',
    clearId: 'clearUnifiedSearch',
    selector: '.folder-item, .file-item',
    scope: 'dataset'
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

  if (document.getElementById('menu-btn-mobile') && document.getElementById('menu-links')) {
    setupMenuToggle();
  }

  if (document.getElementById('open-sidebar') && document.getElementById('mobile-sidebar')) {
    setupSidebarToggle();
  }

  // Only run on redirect-success page
  if (document.getElementById('countdown') && document.getElementById('progressBar')) {
    const redirectUrl = document.body.dataset.redirectUrl;
    const delaySeconds = parseInt(document.body.dataset.delaySeconds, 10);

    startRedirectCountdown({
      countdownId: 'countdown',
      progressBarId: 'progressBar',
      redirectUrl,
      delaySeconds
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