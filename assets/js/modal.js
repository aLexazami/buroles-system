// Modal Helpers
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
      edit: 'Can organize, add, and edit files.'
    };
    description.textContent = accessDescriptions[accessSelect.value] || '';
  }
}

export function openShareModal() {
  toggleModal('shareModal', true);
}

export function initShareButton() {
  const openBtn = document.getElementById('openShareModal');
  const cancelBtn = document.getElementById('cancelShare');
  const modal = document.getElementById('shareModal');
  const accessSelect = document.getElementById('shareAccessLevel');
  const description = document.getElementById('accessLevelDescription');

  if (openBtn) {
    openBtn.addEventListener('click', openShareModal);
  }

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

  // Access Level Description Logic
  if (accessSelect && description) {
    const accessDescriptions = {
      view: 'Can only view the item.',
      comment: 'Can view and add comments.',
      edit: 'Can organize, add, and edit files.'
    };

    const updateDescription = () => {
      const selected = accessSelect.value;
      description.textContent = accessDescriptions[selected] || '';
    };

    accessSelect.addEventListener('change', updateDescription);
    updateDescription(); // Initialize on load
  }
}