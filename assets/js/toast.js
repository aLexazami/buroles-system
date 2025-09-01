export function showToast(message, type = 'success', duration = 3000) {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `px-4 py-2 rounded shadow-md text-white text-sm mb-2 ${
    type === 'error' ? 'bg-red-600' : type === 'warning' ? 'bg-yellow-500' : 'bg-green-600'
  }`;

  toast.style.opacity = '1';
  toast.style.transform = 'translateY(0)';
  toast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
  toast.textContent = message;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
    setTimeout(() => toast.remove(), 500);
  }, duration);
}