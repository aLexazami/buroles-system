// 📁 Rename Modal Logic
export function openRenameModal(name, type) {
  const modal = document.getElementById('renameModal');
  if (!modal) return;

  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.body.classList.add('overflow-hidden');

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
  const modal = document.getElementById('renameModal');
  if (!modal) return;

  modal.classList.add('hidden');
  modal.classList.remove('flex');
  document.body.classList.remove('overflow-hidden');
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

      if (extension && input && !input.value.toLowerCase().endsWith(`.${extension.toLowerCase()}`)) {
        input.value += `.${extension}`;
      }
    });
  }
}

// 🗑️ Delete Modal Logic
export function openDeleteModal(name, type) {
  const modal = document.getElementById('deleteModal');
  if (!modal) return;

  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.body.classList.add('overflow-hidden');

  document.getElementById('deleteType').value = type;
  document.getElementById('deleteName').value = name;
  document.getElementById('deleteTypeLabel').textContent = type;
  document.getElementById('deleteItemName').textContent = name;
}

export function closeDeleteModal() {
  const modal = document.getElementById('deleteModal');
  if (!modal) return;

  modal.classList.add('hidden');
  modal.classList.remove('flex');
  document.body.classList.remove('overflow-hidden');
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