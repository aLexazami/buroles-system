
import { formatDate, refreshCurrentFolder, handleFileAction, removeItemFromUI, renderItems, resolveItemSize, normalizeFileNameInput, isValidFileName, getExtension, isFolderNameValid, removeItemRow } from './file-manager.js';
import { insertItemSorted, getItems } from './stores/fileStore.js';
import { renderFlash } from './flash.js';
import { fileRoutes } from './endpoints/fileRoutes.js';

// Modal Helpers
export function toggleModal(modalId, show) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  if (show) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    requestAnimationFrame(() => {
      modal.classList.remove('opacity-0');
      modal.classList.add('opacity-100');
    });
    document.body.classList.add('overflow-hidden');
  } else {
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0');

    // Wait for transition to finish before hiding
    const handleTransitionEnd = () => {
      modal.classList.remove('flex');
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
      modal.removeEventListener('transitionend', handleTransitionEnd);
    };

    modal.addEventListener('transitionend', handleTransitionEnd);
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

  const passwordInput = document.getElementById('superAdminPasswordInput');
  if (passwordInput) passwordInput.value = '';

  const targetInput = document.getElementById('targetUserId');
  if (targetInput) targetInput.value = '';

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

  const input = document.getElementById('comment-file-id');
  const textarea = document.getElementById('comment-text');
  if (input) input.value = '';
  if (textarea) textarea.value = '';
}

export function initCommentButtons() {
  // 🧩 Open modal when comment button is clicked
  document.querySelectorAll('.comment-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const fileId = btn.dataset.fileId;
      openCommentModal(fileId);
    });
  });

  // 🧹 Cancel button closes and resets modal
  document.getElementById('cancelComment')?.addEventListener('click', closeCommentModal);

  // 🚀 Submit comment via JS
  document.getElementById('submitComment')?.addEventListener('click', async () => {
    const fileId = document.getElementById('comment-file-id')?.value;
    const comment = document.getElementById('comment-text')?.value.trim();

    if (!fileId || !comment) {
      renderFlash('warning', 'Please enter a comment before posting.');
      return;
    }

    try {
      const res = await fetch(fileRoutes.comment, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ file_id: fileId, comment })
      });

      if (res.ok) {
        closeCommentModal();
        renderFlash('success', 'Comment posted successfully.');
      } else {
        renderFlash('error', 'Failed to post comment. Please try again.');
      }
    } catch (err) {
      console.error('Error posting comment:', err);
      renderFlash('error', 'Something went wrong. Please try again.');
    }
  });

  // ⎋ Escape key closes and resets modal
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
  const emailInput = document.querySelector('[name="recipient_email"]');

  if (!modal || !input) return;

  input.value = fileId;
  toggleModal('shareModal', true);

  if (emailInput) emailInput.focus(); // ✅ Auto-focus email field
}

export function closeShareModal() {
  toggleModal('shareModal', false);
}

export function initShareHandler() {
  const form = document.getElementById('shareForm');
  const modal = document.getElementById('shareModal');
  const cancelBtn = document.getElementById('cancelShare');
  const permissionSelector = document.querySelector('[name="permission"]');
  const description = document.getElementById('accessLevelDescription');
  const DEFAULT_AVATAR = '/assets/img/default-avatar.png';

  if (!form || !modal) return;

  // 🛡️ Prevent double-binding
  if (form.dataset.bound === 'true') return;
  form.dataset.bound = 'true';

  // 📘 Permission descriptions
  const definitions = {
    read: 'Can view the file but not modify it.',
    write: 'Can edit the file content.',
    share: 'Can re-share the file with others.',
    delete: 'Can permanently delete the file.'
  };

  const updateDescription = () => {
    const value = permissionSelector?.value;
    description.textContent = definitions[value] || '';
  };

  if (permissionSelector && description) {
    permissionSelector.addEventListener('change', updateDescription);
    updateDescription(); // ✅ Set default on load
  }

  // 📨 Submit handler
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const payload = {
      file_id: form.querySelector('#share-file-id')?.value,
      recipient_email: form.querySelector('[name="recipient_email"]')?.value,
      permission: permissionSelector?.value
    };

    try {
      const endpoint = fileRoutes?.share;
      if (!endpoint) {
        renderFlash('error', 'Sharing is temporarily unavailable');
        return;
      }

      const res = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();

      if (!data.success) {
        renderFlash('error', data.message || 'Share failed');
        return;
      }

      renderFlash('success', data.message || 'File shared successfully');
      form.reset();
      updateDescription(); // ✅ Reset description to default
      toggleModal('shareModal', false);
    } catch (err) {
      renderFlash('error', 'Error sharing file');
    }
  });

  // ❌ Cancel handler
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      form.reset();
      updateDescription(); // ✅ Reset description to default

      const preview = document.getElementById('recipientPreview');
      if (preview) preview.innerHTML = '';

      const avatar = document.getElementById('recipientAvatar');
      if (avatar) avatar.src = DEFAULT_AVATAR;

      const dropdown = document.getElementById('autocompleteDropdown');
      if (dropdown) dropdown.classList.add('hidden');

      toggleModal('shareModal', false);
    });
  }
}

export function initPermissionDescription() {
  const selector = document.getElementById('permissionSelector');
  const description = document.getElementById('accessLevelDescription');

  if (!selector || !description) return;

  const definitions = {
    read: 'Can view the file but not modify it.',
    write: 'Can edit the file content.',
    share: 'Can re-share the file with others.',
    delete: 'Can permanently delete the file.'
  };

  const updateDescription = () => {
    const value = selector.value;
    description.textContent = definitions[value] || '';
  };

  selector.addEventListener('change', updateDescription);
  updateDescription(); // initialize on load
}

// Info Modal in File Manager
// Info Modal in File Manager
export async function openFileInfoModal(item) {
  const modal = document.getElementById('file-info-modal');
  if (!modal) return;

  const title = modal.querySelector('.info-title');
  const content = modal.querySelector('.info-content');
  const closeBtn = modal.querySelector('#closeInfo');
  if (!title || !content || !closeBtn) return;

  title.textContent = item.name || 'File Info';

  const sizeText = await resolveItemSize(item);

  // ✅ Get current view from body dataset
  const view = document.body.dataset.view || 'my-files';

  // ✅ Pass view into renderInfoContent
  content.innerHTML = renderInfoContent({ ...item, sizeText }, view);

  toggleModal('file-info-modal', true);
  closeBtn.onclick = () => toggleModal('file-info-modal', false);
}

function renderInfoContent(item, view = 'my-files') {
  const {
    name,
    type,
    sizeText,
    updated_at,
    owner_first_name,
    owner_last_name,
    recipient_first_name,
    recipient_last_name,
    recipient_email,
    shared_with,
    deleted_by_first_name,
    deleted_by_last_name,
    deleted_by_user_id,
    mime_type,
    path,
    parent_name,
    permissions,
    source_type,
    inherited_from
  } = item;

  const accessText = permissions?.length
    ? `${permissions.join(', ')} (${source_type === 'inherited' ? 'Inherited' : 'Direct'})`
    : '—';

  const originText = parent_name
    ? `${parent_name}${inherited_from ? ` (via ${inherited_from})` : ''}`
    : '—';

  const sharedByText = owner_first_name && owner_last_name
    ? `${owner_first_name} ${owner_last_name}`
    : '—';

  const sharedToText = recipient_first_name && recipient_last_name
    ? `${recipient_first_name} ${recipient_last_name}`
    : recipient_email || (shared_with ? `User ID ${shared_with}` : '—');

  const deletedByText = deleted_by_first_name && deleted_by_last_name
    ? `${deleted_by_first_name} ${deleted_by_last_name}`
    : deleted_by_user_id
      ? `User ID ${deleted_by_user_id}`
      : '—';

  const showSharedTo = view === 'shared-by-me' || view === 'trash';
  const showDeletedBy = view === 'trash';

  return `
    <div class="text-sm text-gray-700 space-y-2">
      <div><strong>Name:</strong> ${name}</div>
      <div><strong>Type:</strong> ${type}</div>
      <div><strong>Size:</strong> ${sizeText}</div>
      <div><strong>Updated:</strong> ${formatDate(updated_at)}</div>
      <div><strong>Owner:</strong> ${sharedByText}</div>
      ${showSharedTo && sharedToText !== '—' ? `<div><strong>Shared To:</strong> ${sharedToText}</div>` : ''}
      ${showDeletedBy && deletedByText !== '—' ? `<div><strong>Deleted By:</strong> ${deletedByText}</div>` : ''}
      <div><strong>MIME Type:</strong> ${mime_type || '—'}</div>
      <div><strong>Path:</strong> ${path}</div>
      <div><strong>Origin Location:</strong> ${originText}</div>
      <div><strong>Access:</strong> ${accessText}</div>
    </div>
  `;
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
  const openBtn = document.querySelector('[data-action="create-folder"]');
  const input = document.querySelector('#createFolderModal [name="folder_name"]');

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => toggleModal('createFolderModal', false));
  }

  if (openBtn && input) {
    openBtn.addEventListener('click', () => {
      input.value = 'New Folder';
      input.focus();
      toggleModal('createFolderModal', true);
    });
  }
}

export function initFolderCreationHandler() {
  const form = document.getElementById('createFolderForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const folderNameInput = form.querySelector('[name="folder_name"]');
    const folderName = folderNameInput?.value.trim() || '';
    const INVALID_CHARS = '\\ / : * ? " < > |';

    if (!isFolderNameValid(folderName)) {
      renderFlash('error', `A file name can't contain any of the following characters: ${INVALID_CHARS}`);
      folderNameInput?.focus();
      return;
    }

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
          folderNameInput?.focus(); // ✅ Focus input on error
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

  const nameDisplay = document.getElementById('delete-item-name');
  if (nameDisplay) {
    nameDisplay.textContent = itemName ? `‘${itemName}’` : 'This item';
  }

  toggleModal('deleteModal', true);
}

export function setupDeleteModal() {
  const modal = document.getElementById('deleteModal');
  const cancelBtn = document.getElementById('cancelDelete');
  const confirmBtn = document.getElementById('confirmDeleteBtn');

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      toggleModal('deleteModal', false);
    });
  }

  if (confirmBtn) {
    confirmBtn.addEventListener('click', async () => {
      const itemId = document.getElementById('delete-item-id')?.value;
      const currentView = document.body.dataset.view || 'my-files';

      toggleModal('deleteModal', false); // ✅ Close immediately

      try {
        const result = await handleFileAction('delete', {
          id: itemId,
          view: currentView
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
}

// Restore Items in File Manager
export function showRestoreModal(itemId) {
  document.getElementById('restore-item-id').value = itemId;
  toggleModal('restoreModal', true);
}

export function setupRestoreModal() {
  const cancelBtn = document.getElementById('cancelRestore');
  const confirmBtn = document.getElementById('confirmRestoreBtn');

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      toggleModal('restoreModal', false);
    });
  }

  if (confirmBtn) {
    confirmBtn.addEventListener('click', async () => {
      const itemId = document.getElementById('restore-item-id')?.value;
      confirmBtn.disabled = true;
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

        if (err.message?.includes('File missing from trash')) {
          renderFlash('info', 'Item may have already been restored or removed.');
        } else {
          renderFlash('error', err.message || 'Server error during restore');
        }

        refreshCurrentFolder();
      } finally {
        confirmBtn.disabled = false;
      }
    });
  }
}

// Permanent Delete in File Manager
export function showPermanentDeleteModal(itemId) {
  document.getElementById('permanent-delete-item-id').value = itemId;
  toggleModal('permanentDeleteModal', true);
}

export function setupPermanentDeleteModal() {
  const cancelBtn = document.getElementById('cancelPermanentDelete');
  const confirmBtn = document.getElementById('confirmPermanentDeleteBtn');

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      toggleModal('permanentDeleteModal', false);
    });
  }

  if (confirmBtn) {
    confirmBtn.addEventListener('click', async () => {
      const itemId = document.getElementById('permanent-delete-item-id')?.value;
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
}

// Delete Comment in File Manager
let commentToDeleteElement = null; // 🧠 Track the DOM element to remove

export function showDeleteCommentModal(commentId, domElement = null) {
  const input = document.getElementById('delete-comment-id');
  input.value = commentId;
  commentToDeleteElement = domElement; // ✅ Store reference
  toggleModal('deleteCommentModal', true);
}

export function setupDeleteCommentModal() {
  const cancelBtn = document.getElementById('cancelCommentDelete');
  const confirmBtn = document.getElementById('confirmCommentDeleteBtn');

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      toggleModal('deleteCommentModal', false);
    });
  }

  if (confirmBtn) {
    confirmBtn.addEventListener('click', async () => {
      const commentId = document.getElementById('delete-comment-id')?.value;
      toggleModal('deleteCommentModal', false);

      try {
        const res = await fetch(fileRoutes.deleteComment, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ comment_id: commentId })
        });

        const result = await res.json();
        if (result.success) {
          renderFlash('success', result.message || 'Comment deleted successfully');

          if (commentToDeleteElement) {
            commentToDeleteElement.remove(); // ✅ Remove from UI
            commentToDeleteElement = null;
          }
        } else {
          renderFlash('error', result.message || 'Failed to delete comment');
        }
      } catch (err) {
        console.error('Comment delete failed:', err);
        renderFlash('error', err.message || 'An error occurred while deleting the comment.');
      }
    });
  }
}

// Empty Trash All Items in Filde Manager
export function setupEmptyTrashModal() {
  const emptyTrashBtn = document.getElementById('empty-trash-btn');
  const cancelBtn = document.getElementById('cancelEmptyTrash');
  const confirmBtn = document.getElementById('confirmEmptyTrashBtn');

  // 🧭 Open modal when button is clicked
  if (emptyTrashBtn) {
    emptyTrashBtn.addEventListener('click', () => {
      toggleModal('emptyTrashModal', true);
    });
  }

  // ❌ Cancel button closes modal
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      toggleModal('emptyTrashModal', false);
    });
  }

  // ✅ Confirm button triggers permanent deletion
  if (confirmBtn) {
    confirmBtn.addEventListener('click', async () => {
      toggleModal('emptyTrashModal', false);

      try {
        const result = await handleFileAction('emptyTrash', { view: 'trash' });
        renderFlash('success', result.message || 'Trash emptied successfully');
        refreshCurrentFolder();
      } catch (err) {
        console.error('Empty trash failed:', err);
        renderFlash('error', err.message || 'An error occurred while emptying the trash.');
      }
    });
  }
}

// Rename Modal in File Manager
export function showRenameModal(itemId, currentName = '') {
  const input = document.getElementById('rename-input');
  const nameDisplay = document.getElementById('rename-item-name');
  const hiddenId = document.getElementById('rename-item-id');

  if (hiddenId) hiddenId.value = itemId;
  if (nameDisplay) nameDisplay.textContent = currentName ? `‘${currentName}’` : 'this item';

  if (input) {
    input.value = currentName || '';
    input.dataset.originalName = currentName || '';
    input.focus();
  }

  toggleModal('renameModal', true);
}

export function setupRenameModalHandler() {
  const confirmBtn = document.getElementById('confirmRenameBtn');
  const cancelBtn = document.getElementById('cancelRename');
  const inputEl = document.getElementById('rename-input');
  const modal = document.getElementById('renameModal');

  const INVALID_CHARS = '\\ / : * ? " < > |';

  async function handleRenameSubmit() {
    const itemId = document.getElementById('rename-item-id')?.value;
    const input = inputEl?.value.trim();
    const originalName = inputEl?.dataset.originalName;

    if (!itemId || !input || !originalName) return;

    const originalExt = getExtension(originalName);

    // ✅ Validate raw input before normalization
    if (!isValidFileName(input, originalExt)) {
      renderFlash('error', `Invalid name. A file name can't contain: ${INVALID_CHARS}, and must end with .${originalExt}`);
      return;
    }

    // ✅ Normalize: auto-append extension if missing
    const finalName = normalizeFileNameInput(input, originalName);

    try {
      const result = await handleFileAction('rename', { id: itemId, name: finalName });
      if (result.success) {
        toggleModal('renameModal', false);
        refreshCurrentFolder();
        renderFlash('success', 'Item renamed successfully.');
      }
    } catch (err) {
      renderFlash('error', 'Rename failed. Please try again.');
    }
  }

  function handleRenameCancel() {
    if (inputEl && inputEl.dataset.originalName) {
      inputEl.value = inputEl.dataset.originalName;
    }
    toggleModal('renameModal', false);
  }

  if (confirmBtn) confirmBtn.addEventListener('click', handleRenameSubmit);
  if (cancelBtn) cancelBtn.addEventListener('click', handleRenameCancel);

  // ⌨️ Keyboard support
  document.addEventListener('keydown', (e) => {
    const isOpen = modal?.classList.contains('opacity-100') || !modal?.classList.contains('hidden');
    if (!isOpen) return;

    if (e.key === 'Enter') {
      e.preventDefault();
      handleRenameSubmit();
    }

    if (e.key === 'Escape') {
      e.preventDefault();
      handleRenameCancel();
    }
  });
}

// Manage Access Modal in File Manager
let accessUpdates = {};

export function openManageAccessModal(fileId) {
  const modal = document.getElementById('manageAccessModal');
  const input = document.getElementById('manage-access-file-id');
  if (!modal || !input) return;

  input.value = fileId;
  accessUpdates = {}; // reset tracked changes
  fetchAccessList(fileId);
  toggleModal('manageAccessModal', true);
}

export function closeManageAccessModal() {
  const modal = document.getElementById('manageAccessModal');
  if (!modal) return;

  const form = modal.querySelector('form');
  if (form) form.reset();

  document.getElementById('accessList').innerHTML = '';
  accessUpdates = {};

  // 🧭 Reset button label to "Done"
  const saveBtn = form?.querySelector('button[type="submit"]');
  if (saveBtn) saveBtn.textContent = 'Done';

  toggleModal('manageAccessModal', false);
}

export function initManageAccessButtons() {
  const form = document.getElementById('manageAccessForm');
  if (!form || form.dataset.bound === 'true') return;
  form.dataset.bound = 'true'; // 🛡️ Prevent double-binding

  const saveBtn = form.querySelector('button[type="submit"]');
  const fileIdInput = form.querySelector('#manage-access-file-id');

  if (!saveBtn || !fileIdInput) return;

  // 🔗 Modal behavior
  bindManageAccessTriggers();
  bindModalDismissal();

  // 📨 Form submission (only for existing access updates)
  bindFormSubmission(form, saveBtn, fileIdInput);

  // 🔽 Global dropdown dismissal
  document.addEventListener('click', () => {
    document.querySelectorAll('.permission-dropdown').forEach(d => d.classList.add('hidden'));
  });
}

function bindManageAccessTriggers() {
  document.querySelectorAll('.manage-access-btn').forEach(btn => {
    const fileId = btn.dataset.fileId;
    if (fileId) {
      btn.addEventListener('click', () => openManageAccessModal(fileId));
    }
  });
}

function bindModalDismissal() {
  document.getElementById('cancelManageAccess')?.addEventListener('click', closeManageAccessModal);

  document.addEventListener('keydown', e => {
    const modal = document.getElementById('manageAccessModal');
    if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
      closeManageAccessModal();
    }
  });
}

function bindFormSubmission(form, saveBtn, fileIdInput) {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const fileId = fileIdInput.value;
    if (!fileId) {
      renderFlash('error', 'Missing file ID');
      return;
    }

    const hasUpdates = Object.keys(accessUpdates).length > 0;
    if (!hasUpdates) {
      renderFlash('info', 'No changes to save');
      closeManageAccessModal();
      return;
    }

    // 🔍 Filter out inherited updates
    const updates = Object.entries(accessUpdates)
      .filter(([user_id]) => {
        const row = document.querySelector(`.access-row[data-user-id="${user_id}"]`);
        return row?.dataset.inherited !== 'true';
      })
      .map(([user_id, permission]) => ({
        user_id,
        permission,
        file_id: fileId
      }));

    // ❌ If all updates were inherited, show warning
    if (updates.length === 0) {
      renderFlash('info', 'Inherited access cannot be changed here. Manage access at the parent folder.');
      return;
    }

    const payload = { file_id: fileId, updates };
    const originalText = saveBtn.textContent;

    saveBtn.disabled = true;
    saveBtn.innerHTML = `<span class="animate-spin mr-2">⏳</span>Saving...`;

    try {
      const endpoint = fileRoutes?.manageAccess;
      if (!endpoint) {
        renderFlash('error', 'Manage Access endpoint not available');
        return;
      }

      const res = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!data.success) {
        renderFlash('error', data.message || 'Failed to update access');
        return;
      }

      renderFlash('success', data.message || 'Access updated successfully');
      refreshCurrentFolder();
      fetchAccessList(fileId);
      document.getElementById('accessList')?.scrollTo({ top: 0, behavior: 'smooth' });

      updates.forEach(update => {
        if (update.permission === 'revoke') {
          removeItemRow(update.file_id);
        }
      });

      accessUpdates = {};
      updateSubmitButtonLabel();
      closeManageAccessModal();
    } catch (err) {
      console.error('❌ Error updating access:', err);
      renderFlash('error', 'Server error. Please try again.');
    } finally {
      saveBtn.disabled = false;
      saveBtn.textContent = originalText;
    }
  });
}

async function fetchAccessList(fileId) {
  try {
    const res = await fetch(`/controllers/file-manager/getAccessList.php?file_id=${encodeURIComponent(fileId)}`);
    const users = await res.json();
    renderAccessList(users);
  } catch (err) {
    renderFlash('error', 'Failed to load access list');
  }
}

function updateSubmitButtonLabel() {
  const saveBtn = document.querySelector('#manageAccessForm button[type="submit"]');
  if (!saveBtn) return;

  const hasUpdates = Object.keys(accessUpdates).length > 0;
  saveBtn.textContent = hasUpdates ? 'Save Changes' : 'Done';
}

function renderAccessList(users = []) {
  const container = document.getElementById('accessList');
  container.innerHTML = '';

  const transitionDuration = 200;
  const dropdown = document.getElementById('permissionDropdown');

  // 🧭 Render dropdown options
  dropdown.innerHTML = ['read', 'write', 'share', 'delete', 'revoke'].map(p => `
    <button class="block w-full text-left px-10 py-2 hover:bg-emerald-100 ${p === 'revoke' ? 'text-black' : ''}" data-value="${p}">
      ${p.charAt(0).toUpperCase() + p.slice(1)}
    </button>
  `).join('');

  if (!users.length) {
    container.innerHTML = `
      <div class="text-sm text-gray-500 italic text-center py-4">
        No one currently has access to this item.
      </div>
    `;
    updateSubmitButtonLabel();
    return;
  }

  users.forEach(user => {
    const row = document.createElement('div');
    row.className = 'access-row flex items-center justify-between border rounded px-3 py-2 bg-white hover:bg-emerald-50 transition';
    row.dataset.userId = user.user_id;

    const fullName = user.name || 'Unnamed';
    const avatar = user.avatar || '/assets/img/default-avatar.png';

    row.innerHTML = `
      <div class="flex items-center gap-3 w-full">
        <img src="${avatar}" class="w-6 h-6 rounded-full object-cover" />
        <div class="flex-1 min-w-0">
          <div class="text-sm font-medium truncate">${fullName}</div>
          <div class="text-xs text-gray-500 truncate">${user.email}</div>
        </div>
        <button
          type="button"
          class="permission-toggle-btn flex items-center justify-between gap-2 font-semibold cursor-pointer hover:bg-emerald-100 px-3 py-1 text-sm"
          data-user-id="${user.user_id}"
          data-permission="${user.permission}"
        >
          <span class="current-permission">${user.permission}</span>
          <img src="/assets/img/arrow-down.png" class="w-2 h-2" />
        </button>
      </div>
    `;

    container.appendChild(row);

    const toggleBtn = row.querySelector('.permission-toggle-btn');
    toggleBtn.addEventListener('click', (e) => {
      e.stopPropagation();

      const isOpen = !dropdown.classList.contains('hidden') &&
        dropdown.dataset.userId === toggleBtn.dataset.userId;

      if (isOpen) {
        dropdown.classList.remove('scale-100', 'opacity-100');
        dropdown.classList.add('scale-0', 'opacity-0');
        setTimeout(() => dropdown.classList.add('hidden'), transitionDuration);
        return;
      }

      dropdown.dataset.userId = toggleBtn.dataset.userId;
      dropdown.classList.remove('hidden');

      const rect = toggleBtn.getBoundingClientRect();
      const dropdownHeight = dropdown.offsetHeight || 160;
      const top = rect.top + (rect.height / 2) - (dropdownHeight / 2);
      const left = rect.left - dropdown.offsetWidth - 8;

      dropdown.style.top = `${Math.max(top, 8)}px`;
      dropdown.style.left = `${Math.max(left, 8)}px`;

      dropdown.classList.remove('scale-0', 'opacity-0');
      dropdown.classList.add('scale-100', 'opacity-100');
    });
  });

  // 📝 Handle permission selection
  dropdown.querySelectorAll('button').forEach(btn => {
    btn.addEventListener('click', () => {
      const newPermission = btn.dataset.value;
      const userId = dropdown.dataset.userId;
      const row = document.querySelector(`.access-row[data-user-id="${userId}"]`);
      const toggleBtn = row?.querySelector('.permission-toggle-btn');

      if (toggleBtn) {
        toggleBtn.querySelector('.current-permission').textContent = newPermission;
        accessUpdates[userId] = newPermission;
        updateSubmitButtonLabel();
        row.classList.toggle('opacity-50', newPermission === 'revoke');
      }

      dropdown.classList.remove('scale-100', 'opacity-100');
      dropdown.classList.add('scale-0', 'opacity-0');
      setTimeout(() => dropdown.classList.add('hidden'), transitionDuration);
    });
  });

  // ❌ Global click to dismiss
  document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target)) {
      dropdown.classList.remove('scale-100', 'opacity-100');
      dropdown.classList.add('scale-0', 'opacity-0');
      setTimeout(() => dropdown.classList.add('hidden'), transitionDuration);
    }
  });

  updateSubmitButtonLabel();
}

// Attendance Modals in class-advisory.php
export function initAttendanceModal() {
  const cancelBtn = document.getElementById('cancelAttendanceBtn');
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => toggleModal('attendanceModal', false));
  }

  window.openAttendanceModal = function(studentId) {
    const input = document.getElementById('attendanceStudentId');
    if (input) {
      input.value = studentId;
      toggleModal('attendanceModal', true);
    }
  };
}

export function initAttendanceHandler() {
  const form = document.getElementById('attendanceForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const studentId = form.querySelector('[name="student_id"]')?.value;
    const status = form.querySelector('[name="status"]')?.value;

    if (!studentId || !status) {
      renderFlash('error', 'Please select a valid attendance status.');
      return;
    }

    const formData = new FormData();
    formData.append('student_id', studentId);
    formData.append('status', status);

    fetch('/controllers/teacher/submit-attendance.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          renderFlash('success', 'Attendance marked successfully.');
          toggleModal('attendanceModal', false);
          form.reset();
        } else {
          renderFlash('error', data.error || 'Failed to mark attendance.');
        }
      })
      .catch(() => {
        renderFlash('error', 'Error submitting attendance.');
      });
  });
}

// Add Student Modal in class-advisory.php
export function initAddStudentModal() {
  const cancelBtn = document.getElementById('cancelAddStudentBtn');
  const openBtn = document.querySelector('[data-action="add-student"]');

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => toggleModal('addStudentModal', false));
  }

  if (openBtn) {
    openBtn.addEventListener('click', () => {
      toggleModal('addStudentModal', true);
    });
  }
}

export function initAddStudentHandler() {
  const form = document.getElementById('addStudentForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/controllers/teacher/submit-student.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          renderFlash('success', 'Student added successfully.');
          toggleModal('addStudentModal', false);
          form.reset();
          // Optionally reload advisory list
          import('./teacher/class-advisory.js').then(({ initClassAdvisory }) => {
            initClassAdvisory();
          });
        } else {
          renderFlash('error', data.error || 'Failed to add student.');
        }
      })
      .catch(() => {
        renderFlash('error', 'Error adding student.');
      });
  });
}

// Class Advisory Modal in class-advisory.php
export function initCreateAdvisoryModal() {
  const cancelBtn = document.getElementById('cancelCreateAdvisoryBtn');
  const openBtn = document.querySelector('[data-action="create-advisory"]');

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => toggleModal('createAdvisoryModal', false));
  }

  if (openBtn) {
    openBtn.addEventListener('click', () => {
      toggleModal('createAdvisoryModal', true);
    });
  }
}

export function initCreateAdvisoryHandler() {
  const form = document.getElementById('createAdvisoryForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/controllers/teacher/submit-advisory.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          renderFlash('success', 'Advisory class created.');
          toggleModal('createAdvisoryModal', false);
          form.reset();
          import('./teacher/class-advisory.js').then(({ initClassAdvisory }) => {
            initClassAdvisory();
          });
        } else {
          renderFlash('error', data.error || 'Failed to create advisory class.');
        }
      })
      .catch(() => {
        renderFlash('error', 'Error creating advisory class.');
      });
  });
}

// Grade Level Modal
export function initGradeLevelModal() {
  const modalId = 'addGradeLevelModal';
  const form = document.getElementById('addGradeLevelForm');
  const openBtn = document.querySelector('[data-action="add-grade-level"]');
  const cancelBtn = document.getElementById('cancelAddGradeLevelBtn');
  const backdrop = document.querySelector(`#${modalId} > .absolute.inset-0`);

  // Open modal
  if (openBtn) {
    openBtn.addEventListener('click', () => {
      toggleModal(modalId, true);
    });
  }

  // Cancel button closes and resets
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      toggleModal(modalId, false);
      if (form) form.reset();
    });
  }

  // Backdrop click closes and resets
  if (backdrop) {
    backdrop.addEventListener('click', () => {
      toggleModal(modalId, false);
      if (form) form.reset();
    });
  }
}

export function initGradeLevelHandler() {
  const form = document.getElementById('addGradeLevelForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const levelInput = form.querySelector('[name="level"]');
    const labelInput = form.querySelector('[name="label"]');

    const levelRaw = levelInput.value.trim();
    const label = labelInput.value.trim();

    // Validate level is a positive integer
    if (!/^\d+$/.test(levelRaw)) {
      renderFlash('error', 'Grade level must be a valid number.');
      levelInput.focus();
      return;
    }

    // Validate label is not empty
    if (label === '') {
      renderFlash('error', 'Label is required.');
      labelInput.focus();
      return;
    }

    const formData = new FormData();
    formData.set('level', parseInt(levelRaw, 10)); // convert to int
    formData.set('label', label);

    fetch('/controllers/admin/submit-grade-level.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          renderFlash('success', 'Grade level added.');
          toggleModal('addGradeLevelModal', false);
          form.reset();
          import('./admin/grade-level-and-section.js').then(({ refreshGradeLevels }) => {
            refreshGradeLevels(); // Optional: reload table
          });
        } else {
          renderFlash('error', data.error || 'Failed to add grade level.');
        }
      })
      .catch(() => {
        renderFlash('error', 'Error adding grade level.');
      });
  });
}

//Edit Grades Modal in grade-level-and-section.php
export function initGradeLevelEditHandler() {
  const form = document.getElementById('editGradeLevelForm');
  const cancelBtn = document.getElementById('cancelEditGradeLevelBtn');

  document.querySelectorAll('.edit-grade-level').forEach(button => {
    button.addEventListener('click', () => {
      document.getElementById('editGradeLevelId').value = button.dataset.id;
      document.getElementById('editLevel').value = button.dataset.level;
      document.getElementById('editLabel').value = button.dataset.label;
      toggleModal('editGradeLevelModal', true);
    });
  });

  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      toggleModal('editGradeLevelModal', false);
      form.reset();
    });
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/controllers/admin/update-grade-level.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          renderFlash('success', 'Grade level updated.');
          toggleModal('editGradeLevelModal', false);
          form.reset();
          import('./admin/grade-level-and-section.js').then(({ refreshGradeLevels }) => {
            refreshGradeLevels();
          });
        } else {
          renderFlash('error', data.error || 'Failed to update grade level.');
        }
      })
      .catch(() => {
        renderFlash('error', 'Error updating grade level.');
      });
  });
}

// Delete Grades Modal in grade-level-and-section.php
export function initGradeLevelDeleteModal() {
  const modalId = 'confirmDeleteGradeLevelModal';
  const form = document.getElementById('deleteGradeLevelForm');
  const cancelBtn = document.getElementById('cancelDeleteGradeLevelBtn');
  const labelSpan = document.getElementById('deleteGradeLevelLabel');
  const hiddenId = document.getElementById('deleteGradeLevelId');

  // Bind delete buttons
  document.querySelectorAll('.delete-grade-level').forEach(button => {
    button.addEventListener('click', () => {
      const id = button.dataset.id;
      const label = button.dataset.label;

      hiddenId.value = id;
      labelSpan.textContent = label;

      toggleModal(modalId, true);
    });
  });

  // Cancel button
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      toggleModal(modalId, false);
      form.reset();
    });
  }

  // Submit deletion
  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('/controllers/admin/delete-grade-level.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          renderFlash('success', 'Grade level deleted.');
          toggleModal(modalId, false);
          form.reset();
          import('./admin/grade-level-and-section.js').then(({ refreshGradeLevels }) => {
            refreshGradeLevels();
          });
        } else {
          renderFlash('error', data.error || 'Failed to delete grade level.');
        }
      })
      .catch(() => {
        renderFlash('error', 'Error deleting grade level.');
      });
  });
}