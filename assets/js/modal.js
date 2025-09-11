export function openRenameModal(name, type) {
  const modal = document.getElementById('renameModal');
  if (!modal) return;

  modal.classList.remove('hidden');
  modal.classList.add('modal-visible');
  document.body.classList.add('overflow-hidden');

  // Extract extension (e.g. "jpg") and store it in modal dataset
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

  modal.classList.remove('modal-visible');
  modal.classList.add('hidden');
  document.body.classList.remove('overflow-hidden');
}

export function initRenameButtons() {
  document.querySelectorAll('.rename-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      openRenameModal(btn.dataset.name, btn.dataset.type);
    });
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeRenameModal();
  });

  // Auto-append extension on form submit
  const renameForm = document.getElementById('renameForm');
  if (renameForm) {
    renameForm.addEventListener('submit', e => {
      const modal = document.getElementById('renameModal');
      const extension = modal?.dataset.extension;
      const input = document.getElementById('renameNewName');

      if (extension && input && !input.value.toLowerCase().endsWith(`.${extension.toLowerCase()}`)) {
        input.value += `.${extension}`;
      }
    });
  }
}

window.closeRenameModal = closeRenameModal;