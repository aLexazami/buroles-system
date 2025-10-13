// UI & Role Toggles
import { setupMenuToggle, setupSidebarToggle, initMenuToggle, initActionMenu } from './menu-toggle.js';
import { setupRoleSwitcher } from './role-switcher.js';
import { setupUserActions } from './user-actions.js';

// File Manager Actions
import { initPasswordButtons, initUnlockButtons, initAnnouncementModal, initAnnouncementTriggers, initUploadModal, initCreateFolderModal, setupDeleteModal, initFolderCreationHandler, initUploadHandler, setupRestoreModal, setupPermanentDeleteModal, setupEmptyTrashModal, setupRenameModalHandler, initShareHandler, initManageAccessButtons} from './modal.js';
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
import { initFileSearch } from './file-search.js';


document.addEventListener('DOMContentLoaded', () => {
  // 📁 File Manager Initialization
  const folderId = document.body.dataset.folderId || null;
  const view = document.body.dataset.view || 'my-files';

  switch (view) {
    case 'trash':
      import('./file-manager.js').then(({ loadTrashView }) => {
        document.body.dataset.view = 'trash';
        loadTrashView(folderId);
      });
      break;

    case 'shared-with-me':
      import('./file-manager.js').then(({ loadSharedWithMe }) => {
        document.body.dataset.view = 'shared-with-me';
        loadSharedWithMe(folderId);
      });
      break;

    case 'shared-by-me':
      import('./file-manager.js').then(({ loadSharedByMe }) => {
        document.body.dataset.view = 'shared-by-me';
        loadSharedByMe(folderId);
      });
      break;

    default:
      import('./file-manager.js').then(({ loadFolder }) => {
        document.body.dataset.view = 'my-files';
        loadFolder(folderId);
      });
      break;
  }



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
  initActionMenu();
  setupRoleCheckboxToggle();
  setupAnnouncementPagination();
  initEmailAutocomplete();
  initMenuToggle();


  // 📁 Modal Initializers
  initAnnouncementModal();
  initAnnouncementTriggers();
  initCreateFolderModal();
  initUploadModal();
  setupDeleteModal();
  setupRestoreModal();
  setupPermanentDeleteModal();
  setupEmptyTrashModal();
  setupRenameModalHandler();
  initShareHandler();
  initManageAccessButtons();


  // Handler
  initFolderCreationHandler();
  initUploadHandler();

  // Badge Updater
  startBadgePolling();
  initFileSearch();


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

  if (document.getElementById('recipientEmailInput')) {
    initEmailAutocomplete();
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