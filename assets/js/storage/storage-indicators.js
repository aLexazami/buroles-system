export function refreshStorageIndicator(retries = 3) {
  const container = document.querySelector('#storage-indicator');
  if (!container) {
    if (retries > 0) {
      setTimeout(() => refreshStorageIndicator(retries - 1), 100);
    }
    return;
  }

  fetch('/ajax/get-storage-stats.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success) return;

      container.className = `${data.boxHighlight} border border-emerald-200 rounded-md px-4 py-3 text-sm sm:text-md text-gray-700 mb-4`;

      const label = container.querySelector('.storage-label');
      if (label) {
        label.innerHTML = `📦 Storage Used: <strong>${data.usedDisplay}</strong> of <strong>${data.limitDisplay}</strong>`;
      }

      const bar = container.querySelector('.storage-bar');
      if (bar) {
        bar.className = `h-full ${data.barColor}`;
        bar.style.width = `${Math.min(100, data.percentUsed)}%`;
      }

      const warning = container.querySelector('.storage-warning');
      if (warning) {
        if (data.percentUsed >= 90) {
          warning.classList.remove('hidden');
        } else {
          warning.classList.add('hidden');
        }
      }
    });
}