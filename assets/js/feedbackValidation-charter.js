document.addEventListener("DOMContentLoaded", () => {
  persistCharterInputs();

  const form = document.querySelector("form");
  let errorContainer = document.getElementById("feedback-form-error3");

  if (!errorContainer) {
    errorContainer = document.createElement("p");
    errorContainer.id = "feedback-form-error3";
    errorContainer.className = "text-red-600 font-bold text-sm";
    form.querySelector(".js-citizen-charter-form")?.appendChild(errorContainer);
  }

  if (form) {
    form.addEventListener("submit", (event) => {
      const requiredGroups = ["cc-1", "cc-2", "cc-3"];
      const missing = requiredGroups.filter(name => !document.querySelector(`input[name="${name}"]:checked`));

      if (missing.length > 0) {
        event.preventDefault();
        errorContainer.textContent = "Please answer all questions in the Citizen Charter section.";
        return;
      }
    });
  }
});

function persistCharterInputs() {
  const inputs = document.querySelectorAll("input[type='radio']");

  inputs.forEach(input => {
    const key = `feedback_${input.name}`;
    const saved = sessionStorage.getItem(key);

    if (saved !== null) {
      input.checked = input.value === saved;
    }

    input.addEventListener("change", () => {
      if (input.checked) {
        sessionStorage.setItem(key, input.value);
      }
    });
  });
}