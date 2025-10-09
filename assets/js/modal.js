// Modal Helpers
import { formatSize, formatDate } from './file-manager.js';

function toggleModal(modalId, show) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  if (show) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  } else {
    modal.classList.remove('flex');
    modal.classList.add('hidden');
  }

  document.body.classList.toggle('overflow-hidden', show);
}

/*
//  Rename Modal Logic
export function openRenameModal(name, type, userId, path) {
  toggleModal('renameModal', true);

  const modal = document.getElementById('renameModal');
  const extension = name.includes('.') ? name.split('.').pop() : '';
  modal.dataset.extension = extension;

  document.getElementById('renameType').value = type;
  document.getElementById('renameOldName').value = name;
  document.getElementById('renameNewName').value = name;
  document.getElementById('renameTypeLabel').textContent = type;
  document.getElementById('renameUserId').value = userId;
  document.getElementById('renamePath').value = path;

  const hint = document.getElementById('renameExtensionHint');
  if (type === 'file' && extension) {
    hint.textContent = `If you omit the extension, it will be preserved as ".${extension}"`;
    hint.classList.remove('hidden');
  } else {
    hint.classList.add('hidden');
    hint.textContent = '';
  }
}

export function closeRenameModal() {
  toggleModal('renameModal', false);
}

export function initRenameButtons() {
  document.querySelectorAll('.rename-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      openRenameModal(
        btn.dataset.name,
        btn.dataset.type,
        btn.dataset.userId,
        btn.dataset.path
      );
    });
  });

  document.getElementById('cancelRename')?.addEventListener('click', closeRenameModal);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeRenameModal();
  });

  const renameForm = document.getElementById('renameForm');
  if (renameForm) {
    renameForm.addEventListener('submit', () => {
      const modal = document.getElementById('renameModal');
      const extension = modal?.dataset.extension;
      const input = document.getElementById('renameNewName');

      if (
        extension &&
        input &&
        !input.value.includes('.') &&
        !input.value.toLowerCase().endsWith(`.${extension.toLowerCase()}`)
      ) {
        input.value += `.${extension}`;
      }
    });
  }
}

//  Delete Modal Logic
export function openDeleteModal(name, type, userId, path) {
  toggleModal('deleteModal', true);

  document.getElementById('deleteType').value = type;
  document.getElementById('deleteName').value = name;
  document.getElementById('deletePath').value = path;
  document.getElementById('deleteTypeLabel').textContent = type;
  document.getElementById('deleteItemName').textContent = name;
  document.getElementById('deleteUserId').value = userId;
}

export function closeDeleteModal() {
  toggleModal('deleteModal', false);
}

export function initDeleteButtons() {
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      openDeleteModal(
        btn.dataset.name,
        btn.dataset.type,
        btn.dataset.userId,
        btn.dataset.path
      );
    });
  });

  document.getElementById('cancelDelete')?.addEventListener('click', closeDeleteModal);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDeleteModal();
  });
}
*/

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

/*
// Share Modal Logic
export function closeShareModal() {
  toggleModal('shareModal', false);

  const form = document.getElementById('shareForm');
  const avatar = document.getElementById('selectedAvatar');
  const suggestions = document.getElementById('emailSuggestions');
  const accessSelect = document.getElementById('shareAccessLevel');
  const description = document.getElementById('accessLevelDescription');

  if (form) form.reset();

  if (avatar) {
    avatar.src = '/assets/img/add-user.png';
  }

  if (suggestions) {
    suggestions.innerHTML = '';
    suggestions.classList.add('hidden');
  }

  // Reapply default access description
  if (accessSelect && description) {
    const accessDescriptions = {
      view: 'Can only view the item.',
      comment: 'Can view and add comments.',
      editor: 'Can organize, add, and edit files.'
    };
    description.textContent = accessDescriptions[accessSelect.value] || '';
  }
}

export function openShareModal() {
  toggleModal('shareModal', true);
}

export function initShareButton() {
  const modal = document.getElementById('shareModal');
  const cancelBtn = document.getElementById('cancelShare');
  const accessSelect = document.getElementById('shareAccessLevel');
  const description = document.getElementById('accessLevelDescription');
  const emailInput = document.getElementById('shareRecipientEmail');
  const suggestionsBox = document.getElementById('emailSuggestions');
  const avatarPreview = document.getElementById('selectedAvatar');

  const accessDescriptions = {
    view: 'Can only view the item.',
    comment: 'Can view and add comments.',
    editor: 'Can organize, add, and edit files.'
  };

  function updateDescription() {
    if (description && accessSelect) {
      description.textContent = accessDescriptions[accessSelect.value] || '';
    }
  }

  function resetShareModalFields(name, path, type, ownerId) {
    const fullPath = path !== '' ? `${path}/${name}` : name;

    document.getElementById('shareItemPath').value = fullPath;
    document.getElementById('shareItemType').value = type;
    document.getElementById('shareOwnerId').value = ownerId;
    document.getElementById('shareModalLabel').textContent = name;

    emailInput.value = '';
    accessSelect.value = 'view';
    if (avatarPreview) avatarPreview.src = '/assets/img/add-user.png';
    if (suggestionsBox) {
      suggestionsBox.innerHTML = '';
      suggestionsBox.classList.add('hidden');
    }
    updateDescription();
  }

  document.querySelectorAll('.open-share-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const { name, path, type, userId: ownerId } = btn.dataset;
      resetShareModalFields(name, path, type, ownerId);
      toggleModal('shareModal', true);
    });
  });

  if (cancelBtn) {
    cancelBtn.addEventListener('click', closeShareModal);
  }

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeShareModal();
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeShareModal();
  });

  if (accessSelect && description) {
    accessSelect.addEventListener('change', updateDescription);
    updateDescription();
  }
}
*/
/*
// Revoke Modal Logic
export function setupRevokeModal() {
  const revokeItemName = document.getElementById('revokeItemName');
  const revokeItemId = document.getElementById('revokeItemId');
  const revokeItemType = document.getElementById('revokeItemType');
  const revokeSharedWith = document.getElementById('revokeSharedWith');
  const cancelRevoke = document.getElementById('cancelRevoke');
  const revokeModal = document.getElementById('revokeModal');

  // Only run if modal exists on the page
  if (
    revokeItemName &&
    revokeItemId &&
    revokeItemType &&
    revokeSharedWith &&
    cancelRevoke &&
    revokeModal
  ) {
    document.querySelectorAll('.revoke-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        revokeItemName.textContent = btn.dataset.name;
        revokeItemId.value = btn.dataset.id;
        revokeItemType.value = btn.dataset.type;
        revokeSharedWith.value = btn.dataset.sharedWith;
        toggleModal('revokeModal', true);
      });
    });

    cancelRevoke.addEventListener('click', () => {
      toggleModal('revokeModal', false);
    });

    revokeModal.addEventListener('click', e => {
      if (e.target === revokeModal) toggleModal('revokeModal', false);
    });
  }
}
*/

/*
export function initCommentModal() {
  const modal = document.getElementById('commentModal');
  const label = document.getElementById('commentFileLabel');
  const nameInput = document.getElementById('commentFileName');
  const pathInput = document.getElementById('commentPath');
  const typeInput = document.getElementById('commentType');
  const folderIdInput = document.getElementById('commentFolderId');
  const parentIdInput = document.getElementById('commentParentId');
  const textarea = modal?.querySelector('textarea[name="comment"]');
  const cancelBtn = document.getElementById('cancelComment');

  if (!modal || !label || !nameInput || !pathInput || !typeInput || !folderIdInput || !parentIdInput || !textarea || !cancelBtn) return;

  function resetFields() {
    label.textContent = '';
    nameInput.value = '';
    pathInput.value = '';
    typeInput.value = 'file';
    folderIdInput.value = '';
    parentIdInput.value = '';
    textarea.value = '';
    textarea.style.height = 'auto';
  }

  function openModal({ name = '', path = '', type = 'file', folderId = '', parentId = '' }) {
    resetFields();
    label.textContent = name || 'Unnamed';
    nameInput.value = name;
    pathInput.value = path;
    typeInput.value = type;
    folderIdInput.value = folderId;
    parentIdInput.value = parentId;
    toggleModal('commentModal', true);
    setTimeout(() => textarea.focus(), 100);
  }

  function closeModal() {
    resetFields();
    toggleModal('commentModal', false);
  }

  function handleTrigger(btn) {
    const type = btn.dataset.type || 'file';
    const name = btn.dataset.folderName || btn.dataset.name || '';
    const path = btn.dataset.path || '';
    const folderId = btn.dataset.folderId || '';
    const parentId = btn.dataset.parentId || '';

    openModal({ name, path, type, folderId, parentId });
  }

  document.querySelectorAll('.comment-btn, .reply-btn').forEach(btn => {
    btn.addEventListener('click', () => handleTrigger(btn));
  });

  cancelBtn.addEventListener('click', closeModal);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && modal.classList.contains('flex')) {
      closeModal();
    }
  });
}
*/

/*
// Delete Comment Modal Logic
export function initDeleteCommentModal() {
  const modalId = 'deleteCommentModal';
  const typeInput = document.getElementById('deleteCommentType');
  const idInput = document.getElementById('deleteCommentId');
  const pathInput = document.getElementById('deleteCommentPath');
  const previewLabel = document.getElementById('deleteCommentPreview');
  const cancelBtn = document.getElementById('cancelCommentDelete');

  if (!typeInput || !idInput || !pathInput || !previewLabel || !cancelBtn) return;

  document.querySelectorAll('.comment-delete-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      typeInput.value = btn.dataset.type || 'file';
      idInput.value = btn.dataset.commentId || '';
      pathInput.value = btn.dataset.path || '';
      previewLabel.textContent = btn.dataset.commentText || 'this comment';
      toggleModal(modalId, true);
    });
  });

  cancelBtn.addEventListener('click', () => {
    toggleModal(modalId, false);
  });
}
  */

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

export function openFileInfoModal(item) {
  const modal = document.getElementById('file-info-modal');
  const title = modal.querySelector('.info-title');
  const content = modal.querySelector('.info-content');
  const closeBtn = modal.querySelector('#closeInfo');

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

  modal.classList.remove('hidden');
  document.body.classList.add('overflow-hidden');

  closeBtn.onclick = () => {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  };
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
