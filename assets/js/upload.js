export function initUploadActions() {
  const getCurrentPath = () =>
    new URLSearchParams(window.location.search).get('path') || '';

  const injectCurrentPath = (form) => {
    const input = form?.querySelector('input[name="path"]');
    if (input) input.value = getCurrentPath();
  };

  const setupUpload = ({ triggerId, inputId, formId }) => {
    const trigger = document.getElementById(triggerId);
    const input = document.getElementById(inputId);
    const form = document.getElementById(formId);

    if (!trigger || !input || !form) return;

    injectCurrentPath(form);

    let hasSubmitted = false;

    trigger.addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
      if (hasSubmitted || input.files.length === 0) return;
      hasSubmitted = true;
      form.submit();
    });

    form.addEventListener('reset', () => {
      hasSubmitted = false;
    });
  };

  // 📄 Single file upload setup
  setupUpload({
    triggerId: 'uploadTrigger',
    inputId: 'uploadInput',
    formId: 'uploadForm'
  });
}