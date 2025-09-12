export function initUploadActions() {
  const getCurrentPath = () =>
    new URLSearchParams(window.location.search).get('path') || '';

  const injectCurrentPath = (formId, inputName = 'path') => {
    const form = document.getElementById(formId);
    const input = form?.querySelector(`input[name="${inputName}"]`);
    if (input) input.value = getCurrentPath();
  };

  const setupUpload = ({ triggerId, inputId, formId, validate }) => {
    const trigger = document.getElementById(triggerId);
    const input = document.getElementById(inputId);
    const form = document.getElementById(formId);

    if (!trigger || !input || !form) return;

    injectCurrentPath(formId);

    let hasSubmitted = false; // ✅ Prevent double submission

    trigger.addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
      if (hasSubmitted) return;

      if (typeof validate === 'function' && !validate(input.files)) return;

      if (input.files.length > 0) {
        hasSubmitted = true;
        form.submit();
      }
    });

    // Optional: reset flag if modal closes or form resets
    form.addEventListener('reset', () => {
      hasSubmitted = false;
    });
  };

  setupUpload({
    triggerId: 'uploadFolderTrigger',
    inputId: 'uploadFolderInput',
    formId: 'uploadFolderForm',
    validate: (files) => {
      if (files.length === 0) {
        alert('The folder is empty. Please add at least one file before uploading.');
        return false;
      }

      const validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'text/plain'];
      const hasValidFile = Array.from(files).some(file => validTypes.includes(file.type));

      if (!hasValidFile) {
        alert('No supported files found in the folder. Please include at least one PDF, image, or text file.');
        return false;
      }

      return true;
    }
  });

  setupUpload({
    triggerId: 'uploadTrigger',
    inputId: 'uploadInput',
    formId: 'uploadForm'
    // No validation needed for single file upload
  });
}