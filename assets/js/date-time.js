// Set the initial date and time
// This script updates the date and time every second
// and formats it in a readable way.
function updateDateTime() {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
  const now = new Date().toLocaleString('en-US', options);
  document.getElementById('date-time').textContent = now;
}

setInterval(updateDateTime, 1000); // Update every second
updateDateTime(); // Initial call to display immediately