import { showToast } from './toast.js';

function showConfirm(message) {
  return new Promise(resolve => {
    const modal = document.getElementById('confirm-modal');
    const msg = document.getElementById('confirm-modal-message');
    const yesBtn = document.getElementById('confirm-modal-yes');
    const noBtn = document.getElementById('confirm-modal-no');

    msg.textContent = message;
    modal.style.display = 'flex';

    function cleanup(result) {
      modal.style.display = 'none';
      yesBtn.removeEventListener('click', onYes);
      noBtn.removeEventListener('click', onNo);
      resolve(result);
    }
    function onYes() { cleanup(true); }
    function onNo() { cleanup(false); }

    yesBtn.addEventListener('click', onYes);
    noBtn.addEventListener('click', onNo);
  });
}

export function setupUserActions() {
  // DELETE USER
  document.querySelectorAll('[data-delete-user]').forEach(button => {
    button.addEventListener('click', async () => {
      const userId = button.getAttribute('data-delete-user');
      if (!userId) return;
      const confirmed = await showConfirm('Are you sure you want to delete this user?');
      if (!confirmed) return;

      button.disabled = true;
      button.textContent = 'Deleting...';

      fetch('/controllers/delete-user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(userId)}`
      })
        .then(res => res.json())
        .then(data => {
          showToast(data.message, data.success ? 'success' : 'error');

          if (data.success) {
            window.location.reload(); // ✅ triggers PHP flash rendering
          } else {
            showToast(data.message || 'Something went wrong.', 'error');
            button.disabled = false;
            button.textContent = 'Delete';
          }

        })
        .catch(error => {
          console.error('Delete error:', error);
          showToast('Something went wrong.', 'error');
          button.disabled = false;
          button.textContent = 'Delete';
        });
    });
  });

  // ARCHIVE USER
  document.querySelectorAll('[data-archive-user]').forEach(button => {
    button.addEventListener('click', async () => {
      const userId = button.getAttribute('data-archive-user');
      if (!userId) return;
      const confirmed = await showConfirm('Are you sure you want to archive this user?');
      if (!confirmed) return;

      button.disabled = true;
      button.textContent = 'Archiving...';

      fetch('/controllers/archive-user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(userId)}`
      })
        .then(res => res.json())
        .then(data => {
          showToast(data.message, data.success ? 'success' : 'error');

          if (data.success) {
            window.location.reload(); // ✅ triggers PHP flash rendering
          } else {
            showToast(data.message || 'Something went wrong.', 'error');
            button.disabled = false;
            button.textContent = 'Archive';
          }

        })
        .catch(error => {
          console.error('Archive error:', error);
          showToast('Something went wrong.', 'error');
          button.disabled = false;
          button.textContent = 'Archive';
        });
    });
  });

  // RESTORE USER
  document.querySelectorAll('[data-restore-user]').forEach(button => {
    button.addEventListener('click', async () => {
      const userId = button.getAttribute('data-restore-user');
      if (!userId) return;
      const confirmed = await showConfirm('Are you sure you want to restore this user?');
      if (!confirmed) return;

      button.disabled = true;
      button.textContent = 'Restoring...';

      fetch('/controllers/restore-user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(userId)}`
      })
        .then(res => res.json())
        .then(data => {
          showToast(data.message, data.success ? 'success' : 'error');

          if (data.success) {
            window.location.reload(); // ✅ triggers PHP flash rendering
          } else {
            showToast(data.message || 'Something went wrong.', 'error');
            button.disabled = false;
            button.textContent = 'Restore';
          }

        })
        .catch(error => {
          console.error('Restore error:', error);
          showToast('Something went wrong.', 'error');
          button.disabled = false;
          button.textContent = 'Restore';
        });
    });
  });
}