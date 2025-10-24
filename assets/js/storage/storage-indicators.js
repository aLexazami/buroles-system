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

      // 🧱 Update container styling
      container.className = `${data.boxHighlight} border border-emerald-200 rounded-md px-4 py-3 text-sm sm:text-md text-gray-700 mb-4`;

      // 📦 Update label
      const label = container.querySelector('.storage-label');
      if (label) {
        label.innerHTML = `📦 Storage Used: <strong>${data.usedDisplay}</strong> of <strong>${data.limitDisplay}</strong>`;
      }

      // 📊 Update bar
      const bar = container.querySelector('.storage-bar');
      if (bar) {
        bar.className = `h-full ${data.barColor}`;
        bar.style.width = `${Math.min(100, data.percentUsed)}%`;
      }

      // ⚠️ Update warning
      const warning = container.querySelector('.storage-warning');
      if (warning) {
        const currentView = document.body.dataset.view || 'my-files';

        if (data.percentUsed >= 90) {
          warning.classList.remove('hidden');
          warning.textContent =
            currentView === 'trash'
              ? "⚠️ You're nearing your storage limit. Consider emptying your trash to free up space."
              : "⚠️ You're nearing your storage limit. Consider deleting unused files.";
        } else {
          warning.classList.add('hidden');
        }
      }
    })
    .catch(err => {
      console.error('Failed to refresh storage indicator:', err);
    });
}