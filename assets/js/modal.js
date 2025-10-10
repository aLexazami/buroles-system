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
