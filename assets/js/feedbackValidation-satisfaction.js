document.addEventListener("DOMContentLoaded", () => {
  persistSatisfactionInputs();

  const form = document.querySelector("form");
  const errorContainer = document.createElement("p");
  errorContainer.id = "feedback-form-error4";
  errorContainer.className = "text-red-600 font-bold text-sm";
  form?.insertBefore(errorContainer, form.querySelector(".flex"));

  if (form) {
    form.addEventListener("submit", (event) => {
      const allAnswered = validateSatisfactionQuestions();
      if (!allAnswered) {
        event.preventDefault();
        errorContainer.textContent = "Please answer all questions in the Client Satisfaction section.";
        return;
      }
    });
  }
});

function validateSatisfactionQuestions() {
  const radios = document.querySelectorAll("input[type='radio']");
  const questionNames = new Set();

  radios.forEach(radio => questionNames.add(radio.name));

  for (const name of questionNames) {
    const selected = document.querySelector(`input[name="${name}"]:checked`);
    if (!selected) return false;
  }

  return true;
}

function persistSatisfactionInputs() {
  const radios = document.querySelectorAll("input[type='radio']");

  radios.forEach(input => {
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