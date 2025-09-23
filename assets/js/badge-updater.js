// badgeUpdater.js

function updateBadgeCounts() {
  fetch('/api/unread-counts.php')
    .then(res => res.json())
    .then(data => {
      const msgBadge = document.querySelector('[data-badge="Messages"]');
      const notifBadge = document.querySelector('[data-badge="Notifications"]');

      if (msgBadge) {
        msgBadge.textContent = data.messages;
        msgBadge.style.display = data.messages > 0 ? 'inline-block' : 'none';
      }

      if (notifBadge) {
        notifBadge.textContent = data.notifications;
        notifBadge.style.display = data.notifications > 0 ? 'inline-block' : 'none';
      }
    });
}

// Start polling automatically
export function startBadgePolling(interval = 10000) {
  updateBadgeCounts(); // initial run
  setInterval(updateBadgeCounts, interval);
}