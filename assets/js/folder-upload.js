export function initCreateFolderModal() {
  const createModal = document.getElementById('createFolderModal');
  const openBtn = document.getElementById('openCreateFolderModal');
  const cancelBtn = document.getElementById('cancelCreateFolder');

  if (openBtn && createModal) {
    openBtn.addEventListener('click', () => {
      createModal.classList.remove('hidden');
      createModal.classList.add('flex');
      document.body.classList.add('overflow-hidden');
    });
  }

  if (cancelBtn && createModal) {
    cancelBtn.addEventListener('click', () => {
      createModal.classList.add('hidden');
      createModal.classList.remove('flex');
      document.body.classList.remove('overflow-hidden');
    });
  }
}

export function initUploadTrigger() {
  const uploadTrigger = document.getElementById('uploadTrigger');
  const uploadInput = document.getElementById('uploadInput');
  const uploadForm = document.getElementById('uploadForm');

  if (uploadTrigger && uploadInput && uploadForm) {
    uploadTrigger.addEventListener('click', () => {
      uploadInput.click();
    });

    uploadInput.addEventListener('change', () => {
      if (uploadInput.files.length > 0) {
        uploadForm.submit();
      }
    });
  }
}