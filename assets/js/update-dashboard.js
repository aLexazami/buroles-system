document.addEventListener('DOMContentLoaded', function () {
  function safeUpdate(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  }

  function updateDashboardCounts() {
    fetch('/controllers/get-counts.php')
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
      })
      .then(data => {
        safeUpdate('new-count', data.new);
        safeUpdate('weekly-count', data.weekly);
        safeUpdate('annual-count', data.annual);
      })
      .catch(error => {
        console.error('Dashboard counts fetch failed:', error);
      });
  }

  updateDashboardCounts();
  setInterval(updateDashboardCounts, 10000);
});