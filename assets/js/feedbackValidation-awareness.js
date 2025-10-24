document.addEventListener("DOMContentLoaded", () => {
  persistInputValues("form");

  const form = document.querySelector("form");
  let errorContainer = document.getElementById("feedback-form-error2");

  if (!errorContainer) {
    errorContainer = document.createElement("p");
    errorContainer.id = "feedback-form-error2";
    errorContainer.className = "text-red-600 font-bold text-sm";
    form.querySelector(".js-citizen-awareness-form")?.appendChild(errorContainer);
  }

  if (form) {
    form.addEventListener("submit", (event) => {
      const selected = document.querySelector('input[name="yes_no"]:checked');
      if (!selected) {
        event.preventDefault();
        errorContainer.textContent = "Please select Yes or No.";
        return;
      }

    });
  }
});

function persistInputValues(formSelector) {
  const form = document.querySelector(formSelector);
  if (!form) return;

  const inputs = form.querySelectorAll("input");

  inputs.forEach(input => {
    const key = `feedback_${input.name}`;
    const saved = sessionStorage.getItem(key);

    if (saved !== null && input.type === "radio") {
      input.checked = input.value === saved;
    }

    input.addEventListener("change", () => {
      if (input.checked) {
        sessionStorage.setItem(key, input.value);
      }
    });
  });
}