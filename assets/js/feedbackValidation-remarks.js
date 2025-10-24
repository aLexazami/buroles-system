document.addEventListener("DOMContentLoaded", () => {
  const remarksField = document.getElementById("remarks");

  // Restore saved remarks
  const savedRemarks = sessionStorage.getItem("feedback_remarks");
  if (remarksField && savedRemarks !== null) {
    remarksField.value = savedRemarks;
  }

  // Save remarks on input
  if (remarksField) {
    remarksField.addEventListener("input", () => {
      sessionStorage.setItem("feedback_remarks", remarksField.value);
    });
  }

  // Clear sessionStorage on submit
  const form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", () => {
      sessionStorage.clear();
    });
  }
});