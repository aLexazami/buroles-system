document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-alert]').forEach(alert => {
    setTimeout(() => alert.remove(), 3000);
  });
});