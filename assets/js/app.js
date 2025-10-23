// UI & Role Toggles
import { initActionMenu, initMenuToggle, setupMenuToggle, setupSidebarToggle } from './menu-toggle.js';
import { setupRoleSwitcher } from './role-switcher.js';
import { setupUserActions } from './user-actions.js';

// File Manager Actions
import { initDropdownMenus, initNotificationActions, setupRecipientDropdown } from './dropdown.js';
import { initAddStudentModal, initAnnouncementModal, initAnnouncementTriggers, initCreateAdvisoryHandler, initCreateAdvisoryModal, initCreateFolderModal, initFolderCreationHandler, initGradeLevelHandler, initGradeLevelModal, initGradeLevelSectionSync, initGradeSectionModal, initManageAccessButtons, initPasswordButtons, initShareHandler, initUnlockButtons, initUploadHandler, initUploadModal, setupDeleteCommentModal, setupDeleteModal, setupEmptyTrashModal, setupPermanentDeleteModal, setupRenameModalHandler, setupRestoreModal, initSchoolYearModal,initStudentClassAdvisoryDeleteModal  } from './modal.js';
import { refreshGradeLevels, refreshGradeSections, refreshSchoolYears} from './school-management/school-tools.js';
import { initAdvisoryGrid } from '/assets/js/school-management/create-advisory.js';
import { initStudentTable } from '/assets/js/school-management/student-loader.js';
import {  initStudentList} from '/assets/js/school-management/student-list-advisory.js';
import { initUploadActions } from './upload.js';
import { initExportDropdown } from '/assets/js/export-button.js';

// Search Filters (Unified)
import { setupSearchFilter } from './search-filter.js';

// Preview
import { setupAnnouncementPagination } from './announcementCarousel.js';
import { setupAvatarPreview } from './avatar-preview.js';
import { startBadgePolling } from './badge-updater.js';
import { setupRoleCheckboxToggle } from './checkbox.js';
import { initFileSearch } from './file-search.js';
import { initPasswordStrength, toggleVisibility } from './password-utils.js';
import { startRedirectCountdown } from './redirect-utils.js';
import { initEmailAutocomplete } from './search-autocomplete.js';
import { setupTableSorter } from '/assets/js/table-sorter.js';


document.addEventListener('DOMContentLoaded', () => {
  // 🧪 Global Error Logger
  window.addEventListener('error', (event) => {
    fetch('/log/client-error.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        message: event.message,
        source: event.filename,
        line: event.lineno,
        column: event.colno,
        error: event.error?.stack || null, // ✅ capture stack trace if available
        userAgent: navigator.userAgent,
        timestamp: new Date().toISOString()
      })
    });
  });

  // 🧪 Feature Usage Logger
  window.logFeature = function (action, details = {}) {
    fetch('/log/feature-usage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, details })
    });
  };
  // 📁 File Manager Initialization
  // ✅ Only run file manager logic on file-manager.php
  if (window.location.pathname.includes('/file-manager.php')) {
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

      case 'comments':
        import('./file-manager.js').then(({ loadUserComments, loadReceivedComments, toggleActive }) => {
          // Load default view
          loadUserComments();

          // Wire up toggle buttons
          const myBtn = document.getElementById('toggleMyComments');
          const receivedBtn = document.getElementById('toggleReceivedComments');

          if (myBtn && receivedBtn) {
            myBtn.addEventListener('click', () => {
              toggleActive(myBtn, receivedBtn);
              loadUserComments();
            });

            receivedBtn.addEventListener('click', () => {
              toggleActive(receivedBtn, myBtn);
              loadReceivedComments();
            });
          }
        });
        break;


      default:
        import('./file-manager.js').then(({ loadFolder }) => {
          document.body.dataset.view = 'my-files';
          loadFolder(folderId);
        });
        break;
    }
  }

  if (window.location.pathname.includes('/student-list.php')) {
    initAddStudentModal();
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
  initManageAccessButtons();
  setupDeleteCommentModal();
  initGradeLevelModal();
  initGradeSectionModal();
  initCreateAdvisoryModal();
  initGradeLevelSectionSync();
  initSchoolYearModal();
  initAdvisoryGrid();
  initStudentClassAdvisoryDeleteModal();

  if (document.getElementById('classId')) {
  const roleSlug = document.body.dataset.role || 'admin'; // fallback to admin
  import('/assets/js/school-management/student-list-advisory.js').then(({ initStudentList }) => {
    initStudentList(roleSlug);
  });
}


  if (window.location.pathname.includes('/student.php')) {
    initStudentTable();

    const gradeLevelFilter = document.getElementById('gradeLevelFilter');
    gradeLevelFilter?.addEventListener('change', e => {
      const selectedGrade = e.target.value;
      import('/assets/js/school-management/student-loader.js').then(({ refreshStudentTable }) => {
        refreshStudentTable(selectedGrade);
      });
    });
  }

  // Handler
  initFolderCreationHandler();
  initUploadHandler();
  setupRenameModalHandler();
  initShareHandler();
  initGradeLevelHandler();
  initCreateAdvisoryHandler();

  // Badge Updater
  startBadgePolling();
  initFileSearch();


  setupSearchFilter({
    inputId: 'staffSearch',
    clearId: 'clearStaffSearch',
    selector: '.staff-item',
    scope: 'dataset' // uses data-name attribute
  });

  if (document.getElementById('recipientEmailInput')) {
    initEmailAutocomplete();
  }

  if (document.getElementById('gradeLevelTableBody')) {
    refreshGradeLevels();
  }

  if (document.getElementById('gradeSectionTableBody')) {
    refreshGradeSections();
  }

  if (document.getElementById('schoolYearTableBody')) {
    refreshSchoolYears();
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