
import { formatSize, formatDate, refreshCurrentFolder, handleFileAction, removeItemFromUI , renderItems } from './file-manager.js';
import { insertItemSorted, getItems } from './stores/fileStore.js';
import { renderFlash } from './flash.js';

// Modal Helpers
export function toggleModal(modalId, show) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  if (show) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    void modal.offsetHeight;
    modal.classList.remove('opacity-0');
    modal.classList.add('opacity-100');
    document.body.classList.add('overflow-hidden');
  } else {
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0');
    setTimeout(() => {
      modal.classList.remove('flex');
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }, 200);
  }
}

//  Password Modal Logic
let pendingPasswordHref = '';

export function openPasswordModal(userId) {
  document.getElementById('targetUserId').value = userId;
  toggleModal('passwordModal', true);
}

export function closePasswordModal() {
  toggleModal('passwordModal', false);
  document.getElementById('superAdminPasswordInput').value = '';
  document.getElementById('targetUserId').value = '';
  pendingPasswordHref = '';
  pendingChainedRedirect = '';
}

export function initPasswordButtons() {
  //  Trigger modal from table

  document.querySelectorAll('[data-manage-password]').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const userId = link.dataset.managePassword;
      pendingPasswordHref = link.getAttribute('href');
      openPasswordModal(userId);
    });
  });



  //  Submit on Enter key
  document.getElementById('superAdminPasswordInput')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') submitSuperAdminPassword();
  });

  // ⎋ Close on Escape key
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closePasswordModal();
  });

  //  Verify button
  document.getElementById('submitSuperAdminPassword')?.addEventListener('click', submitSuperAdminPassword);

  //  Cancel button
  document.getElementById('cancelSuperAdminPassword')?.addEventListener('click', closePasswordModal);
}

function submitSuperAdminPassword() {
  const password = document.getElementById('superAdminPasswordInput').value;
  const userId = document.getElementById('targetUserId').value;
  const verifyBtn = document.getElementById('submitSuperAdminPassword');
  verifyBtn.disabled = true;

  if (!password || !userId || !pendingPasswordHref) {
    alert('Missing credentials.');
    return;
  }

  fetch('/api/verify-superadmin.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ password, user_id: userId })
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        window.location.href = pendingPasswordHref;
      } else {
        alert('Verification failed.');
      }
      closePasswordModal();
    })
    .catch(() => {
      alert('Server error. Please try again.');
      closePasswordModal();
    })
    .finally(() => {
      verifyBtn.disabled = false;
    });
}



// Unlock Modal Logic
let pendingUnlockUserId = '';
let pendingManagePasswordUrl = '';
let pendingChainedRedirect = '';

export function openUnlockModal(userId, manageUrl) {
  pendingUnlockUserId = userId;
  pendingManagePasswordUrl = manageUrl;
  toggleModal('unlockModal', true);
}

export function closeUnlockModal() {
  toggleModal('unlockModal', false);
  pendingUnlockUserId = '';
  pendingManagePasswordUrl = '';
  pendingChainedRedirect = '';
}

export function initUnlockButtons() {
  document.querySelectorAll('.open-unlock-modal').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const userId = btn.dataset.userId;
      const manageUrl = btn.dataset.manageUrl;
      openUnlockModal(userId, manageUrl);
    });
  });

  document.getElementById('cancelUnlockModal')?.addEventListener('click', closeUnlockModal);

  document.getElementById('justUnlockBtn')?.addEventListener('click', () => {
    if (!pendingUnlockUserId) return;

    fetch('/api/unlock-user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: pendingUnlockUserId })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert('Unlock failed.');
        }
      })
      .catch(() => alert('Server error.'))
      .finally(closeUnlockModal);
  });

  document.getElementById('unlockAndResetBtn')?.addEventListener('click', () => {
    if (!pendingUnlockUserId || !pendingManagePasswordUrl) return;

    pendingPasswordHref = pendingManagePasswordUrl.includes('?')
      ? pendingManagePasswordUrl + '&unlock=1'
      : pendingManagePasswordUrl + '?unlock=1';

    document.getElementById('targetUserId').value = pendingUnlockUserId;
    pendingChainedRedirect = 'manage-password';

    closeUnlockModal();
    toggleModal('passwordModal', true);
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeUnlockModal();
  });
}

//Announcement Modal
export function initAnnouncementModal() {
  const modalId = 'announcementModal';
  const openBtn = document.getElementById('openAnnouncementModal');
  const cancelBtn = document.getElementById('cancelAnnouncementModal');
  const textarea = document.getElementById('announcementBody');

  // 🟢 Modal open/close handlers
  openBtn?.addEventListener('click', () => toggleModal(modalId, true));
  cancelBtn?.addEventListener('click', () => toggleModal(modalId, false));

  // ⎋ Escape key closes modal
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') toggleModal(modalId, false);
  });

  // ✏️ Auto-resize textarea without scroll
  if (textarea) {
    textarea.style.overflowY = 'hidden'; // ⛔ Prevent vertical scroll

    const resize = () => {
      textarea.style.height = 'auto'; // Reset height
      textarea.style.height = textarea.scrollHeight + 'px'; // Expand to fit content
    };

    textarea.addEventListener('input', resize);
    resize(); // Trigger once on load
  }
}

// Announcement Viewer Modal
export function openAnnouncementViewer(el) {
  const title = el.dataset.title || 'Untitled';
  const body = el.dataset.body || 'No content available.';
  const role = el.dataset.role || 'For All';
  const date = el.dataset.date || 'Unknown date';

  document.getElementById('viewerTitle').textContent = title;
  document.getElementById('viewerBody').textContent = body;
  document.getElementById('viewerMeta').textContent = `${role} • Posted on ${date}`;

  toggleModal('announcementViewer', true);
}

export function closeAnnouncementViewer() {
  toggleModal('announcementViewer', false);
}

export function initAnnouncementTriggers() {
  document.querySelectorAll('[data-viewer-trigger]').forEach(el => {
    el.addEventListener('click', () => openAnnouncementViewer(el));
  });

  document.getElementById('closeAnnouncementViewer')?.addEventListener('click', closeAnnouncementViewer);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeAnnouncementViewer();
  });
}

// ************************************************************************ //

// 💬 Comment Files  Modal Logic
export function openCommentModal(fileId) {
  const modal = document.getElementById('commentModal');
  const input = document.getElementById('comment-file-id');
  if (!modal || !input) return;

  input.value = fileId;
  toggleModal('commentModal', true);
}

export function closeCommentModal() {
  toggleModal('commentModal', false);

  const modal = document.getElementById('commentModal');
  if (!modal) return;

  const form = modal.querySelector('form');
  if (form) form.reset(); // ✅ Clears textarea and hidden input
}

export function initCommentButtons() {
  document.querySelectorAll('.comment-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const fileId = btn.dataset.fileId;
      openCommentModal(fileId);
    });
  });

  document.getElementById('cancelComment')?.addEventListener('click', closeCommentModal);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      const modal = document.getElementById('commentModal');
      if (modal?.classList.contains('flex')) closeCommentModal();
    }
  });
}

// 🔗 Share Modal Logic
export function openShareModal(fileId) {
  const modal = document.getElementById('shareModal');
  const input = document.getElementById('share-file-id');
  if (!modal || !input) return;

  input.value = fileId;
  toggleModal('shareModal', true);
}

export function closeShareModal() {
  toggleModal('shareModal', false);
}

export function initShareButtons() {
  document.querySelectorAll('.share-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const fileId = btn.dataset.fileId;
      openShareModal(fileId);
    });
  });

  document.getElementById('cancelShare')?.addEventListener('click', closeShareModal);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      const modal = document.getElementById('shareModal');
      if (modal?.classList.contains('flex')) closeShareModal();
    }
  });
}

// Info Modal in File Manager
export function openFileInfoModal(item) {
  const modal = document.getElementById('file-info-modal');
  const title = modal?.querySelector('.info-title');
  const content = modal?.querySelector('.info-content');
  const closeBtn = modal?.querySelector('#closeInfo');

  if (!modal || !title || !content || !closeBtn) return;

  title.textContent = item.name || 'File Info';

  content.innerHTML = `
    <div class="text-sm text-gray-700 space-y-2">
      <div><strong>Name:</strong> ${item.name}</div>
      <div><strong>Type:</strong> ${item.type}</div>
      <div><strong>Size:</strong> ${formatSize(item.size)}</div>
      <div><strong>Updated:</strong> ${formatDate(item.updated_at)}</div>
      <div><strong>Owner:</strong> ${item.owner_first_name} ${item.owner_last_name}</div>
      <div><strong>MIME Type:</strong> ${item.mime_type || '—'}</div>
      <div><strong>Path:</strong> ${item.path}</div>
    </div>
  `;

  // ✅ Show modal using helper
  toggleModal('file-info-modal', true);

  // ✅ Close modal using helper
  closeBtn.onclick = () => toggleModal('file-info-modal', false);
}

// Upload Modal in File Manager
export function initUploadModal() {
  const cancelBtn = document.getElementById('cancelUploadBtn');
  const openBtn = document.getElementById('openUploadBtn');
  const uploadInput = document.getElementById('uploadInput');
  const fileNameDisplay = document.getElementById('fileName');

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      toggleModal('uploadModal', false);
      if (uploadInput) uploadInput.value = '';
      if (fileNameDisplay) fileNameDisplay.textContent = 'No file chosen';
    });
  }

  if (openBtn) {
    openBtn.addEventListener('click', () => toggleModal('uploadModal', true));
  }

  if (uploadInput && fileNameDisplay) {
    uploadInput.addEventListener('change', () => {
      const file = uploadInput.files[0];
      fileNameDisplay.textContent = file ? file.name : 'No file chosen';
    });
  }
}

export function initUploadHandler() {
  const form = document.getElementById('uploadForm');
  const fileNameDisplay = document.getElementById('fileName');

  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    // 📁 Inject current folderId as folder_id
    const currentFolderId = document.body.dataset.folderId || '';
    formData.set('folder_id', currentFolderId);

    try {
      const response = await fetch('/controllers/file-manager/upload.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        renderFlash('error', data.error || 'Upload failed');
        return;
      }

      if (data.item) {
        insertItemSorted(data.item);         // ✅ Update store
        renderItems(getItems());             // ✅ Re-render UI
        toggleModal('uploadModal', false);
        form.reset();
        if (fileNameDisplay) fileNameDisplay.textContent = 'No file chosen';
        renderFlash('success', 'File uploaded successfully');
      }
    } catch (err) {
      console.error('Upload error:', err);
      renderFlash('error', 'Error uploading file');
    }
  });
}

// Create Folder Modal in File Manager
export function initCreateFolderModal() {
  const cancelBtn = document.getElementById('cancelCreateFolderBtn');
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => toggleModal('createFolderModal', false));
  }

  const openBtn = document.querySelector('[data-action="create-folder"]');
  if (openBtn) {
    openBtn.addEventListener('click', () => toggleModal('createFolderModal', true));
  }
}

export function initFolderCreationHandler() {
  const form = document.getElementById('createFolderForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    // 📁 Inject current folderId as parent_id
    const currentFolderId = document.body.dataset.folderId || '';
    formData.set('parent_id', currentFolderId);

    fetch('/controllers/file-manager/create-folder.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success && data.item) {
          insertItemSorted(data.item);       // ✅ Update store
          renderItems(getItems());           // ✅ Re-render UI
          toggleModal('createFolderModal', false);
          form.reset();
          renderFlash('success', 'Folder created successfully');
        } else {
          renderFlash('error', data.error || 'Failed to create folder');
        }
      })
      .catch(() => {
        renderFlash('error', 'Error creating folder');
      });
  });
}

// Delete Confirmation Modal in File Manager
export function showDeleteModal(itemId, itemName = '') {
  document.getElementById('delete-item-id').value = itemId;

  // Optional: show item name in modal
  const nameDisplay = document.getElementById('delete-item-name');
  if (nameDisplay) {
    nameDisplay.textContent = itemName;
  }

  toggleModal('deleteModal', true);
}

export function setupDeleteModal() {
  const modal = document.getElementById('deleteModal');
  const cancelBtn = document.getElementById('cancelDelete');
  const confirmBtn = document.getElementById('confirmDeleteBtn');

  cancelBtn.addEventListener('click', () => {
    toggleModal('deleteModal', false);
  });

  confirmBtn.addEventListener('click', async () => {
    const itemId = document.getElementById('delete-item-id').value;
    const currentView = document.body.dataset.view || 'my-files';

    toggleModal('deleteModal', false); // ✅ Close immediately

    try {
      const result = await handleFileAction('delete', {
        id: itemId,
        view: currentView // ✅ Pass view context
      });

      renderFlash('success', result.message || 'Item deleted successfully');

      setTimeout(() => {
        refreshCurrentFolder();
      }, 300);
    } catch (err) {
      console.error('Delete failed:', err);
      renderFlash('error', err.message || 'An error occurred while deleting the item.');
    }
  });
}

// Restore Items in File Manager
export function showRestoreModal(itemId) {
  document.getElementById('restore-item-id').value = itemId;
  toggleModal('restoreModal', true);
}

export function setupRestoreModal() {
  const cancelBtn = document.getElementById('cancelRestore');
  const confirmBtn = document.getElementById('confirmRestoreBtn');

  cancelBtn.addEventListener('click', () => {
    toggleModal('restoreModal', false);
  });

  confirmBtn.addEventListener('click', async () => {
    const itemId = document.getElementById('restore-item-id').value;
    toggleModal('restoreModal', false);

    try {
      const result = await handleFileAction('restore', { id: itemId });

      if (result.success) {
        renderFlash('success', 'File restored successfully');
        removeItemFromUI(itemId);
        refreshCurrentFolder();
      } else {
        renderFlash('error', 'Restore failed');
      }
    } catch (err) {
      console.error('Restore failed:', err);
      renderFlash('error', err.message || 'Server error during restore');
    }
  });
}

// Permanent Delete in File Manager
export function showPermanentDeleteModal(itemId) {
  document.getElementById('permanent-delete-item-id').value = itemId;
  toggleModal('permanentDeleteModal', true);
}

export function setupPermanentDeleteModal() {
  const cancelBtn = document.getElementById('cancelPermanentDelete');
  const confirmBtn = document.getElementById('confirmPermanentDeleteBtn');

  cancelBtn.addEventListener('click', () => {
    toggleModal('permanentDeleteModal', false);
  });

  confirmBtn.addEventListener('click', async () => {
    const itemId = document.getElementById('permanent-delete-item-id').value;
    toggleModal('permanentDeleteModal', false);

    try {
      const result = await handleFileAction('deletePermanent', { id: itemId });
      renderFlash('success', result.message || 'Item permanently deleted');
      refreshCurrentFolder();
    } catch (err) {
      console.error('Permanent delete failed:', err);
      renderFlash('error', err.message || 'Failed to delete item permanently');
    }
  });
}
