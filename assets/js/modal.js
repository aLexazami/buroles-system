// 📁 Modal Helpers
function toggleModal(modalId, show) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.classList.toggle('hidden', !show);
  modal.classList.toggle('flex', show);
  document.body.classList.toggle('overflow-hidden', show);
}

// 📁 Rename Modal Logic
export function openRenameModal(name, type) {
  toggleModal('renameModal', true);

  const modal = document.getElementById('renameModal');
  const extension = name.includes('.') ? name.split('.').pop() : '';
  modal.dataset.extension = extension;

  document.getElementById('renameType').value = type;
  document.getElementById('renameOldName').value = name;
  document.getElementById('renameNewName').value = name;
  document.getElementById('renameTypeLabel').textContent = type;

  const hint = document.getElementById('renameExtensionHint');
  if (type === 'file' && extension) {
    hint.textContent = `If you omit the extension, it will be preserved as ".${extension}"`;
    hint.classList.remove('hidden');
  } else {
    hint.textContent = '';
    hint.classList.add('hidden');
  }
}

export function closeRenameModal() {
  toggleModal('renameModal', false);
}

export function initRenameButtons() {
  document.querySelectorAll('.rename-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      openRenameModal(btn.dataset.name, btn.dataset.type);
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
        !input.value.toLowerCase().endsWith(`.${extension.toLowerCase()}`)
      ) {
        input.value += `.${extension}`;
      }
    });
  }
}

// 🗑️ Delete Modal Logic
export function openDeleteModal(name, type) {
  toggleModal('deleteModal', true);

  const currentPath = new URLSearchParams(window.location.search).get('path') || '';

  document.getElementById('deleteType').value = type;
  document.getElementById('deleteName').value = name;
  document.getElementById('deletePath').value = currentPath;
  document.getElementById('deleteTypeLabel').textContent = type;
  document.getElementById('deleteItemName').textContent = name;
}

export function closeDeleteModal() {
  toggleModal('deleteModal', false);
}

export function initDeleteButtons() {
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      openDeleteModal(btn.dataset.name, btn.dataset.type);
    });
  });

  document.getElementById('cancelDelete')?.addEventListener('click', closeDeleteModal);

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDeleteModal();
  });
}