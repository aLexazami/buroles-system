export function initUploadActions() {
  // Upload Folder
  const folderTrigger = document.getElementById('uploadFolderTrigger');
  const folderInput = document.getElementById('uploadFolderInput');
  const folderForm = document.getElementById('uploadFolderForm');

  if (folderTrigger && folderInput && folderForm) {
    folderTrigger.addEventListener('click', () => folderInput.click());

    folderInput.addEventListener('change', () => {
      if (folderInput.files.length === 0) {
        alert('The folder is empty. Please add at least one file before uploading.');
        return;
      }
      folderForm.submit();
    });
  }

  // Upload File
  const fileTrigger = document.getElementById('uploadTrigger');
  const fileInput = document.getElementById('uploadInput');
  const fileForm = document.getElementById('uploadForm');

  if (fileTrigger && fileInput && fileForm) {
    fileTrigger.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0) {
        fileForm.submit();
      }
    });
  }
}