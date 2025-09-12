export function initCreateFolderModal() {
  const modal = document.getElementById('createFolderModal');
  const openBtn = document.getElementById('openCreateFolderModal');
  const cancelBtn = document.getElementById('cancelCreateFolder');
  const pathInput = document.getElementById('createFolderPath');

  if (!modal || !openBtn || !cancelBtn) return;

  const toggleModal = (show) => {
    modal.classList.toggle('hidden', !show);
    modal.classList.toggle('flex', show);
    document.body.classList.toggle('overflow-hidden', show);
  };

  openBtn.addEventListener('click', () => {
    const currentPath = new URLSearchParams(window.location.search).get('path') || '';
    if (pathInput) pathInput.value = currentPath;
    toggleModal(true);
  });

  cancelBtn.addEventListener('click', () => {
    toggleModal(false);
  });
}