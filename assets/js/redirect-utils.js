export function startRedirectCountdown({ countdownId, progressBarId, redirectUrl, delaySeconds }) {
  const countdownEl = document.getElementById(countdownId);
  const progressBar = document.getElementById(progressBarId);
  const total = delaySeconds;
  let seconds = total;

  if (!countdownEl || !progressBar || !redirectUrl) return;

  const interval = setInterval(() => {
    seconds--;
    if (seconds <= 0) {
      clearInterval(interval);
      window.location.href = redirectUrl;
    } else {
      countdownEl.textContent = seconds;
      const percent = ((total - seconds) / total) * 100;
      progressBar.style.width = percent + '%';
    }
  }, 1000);
}