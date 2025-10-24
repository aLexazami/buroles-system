import { updateServiceOptions } from './serviceAvailedOptions.js';

document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const errorContainer = document.getElementById("feedback-form-error");

  persistClientInfoInputs();

  if (form) {
    form.addEventListener("submit", (event) => {
      if (errorContainer) errorContainer.textContent = "";

      const requiredFields = ["date", "age", "sex", "customer_type", "service_availed", "region"];
      for (const id of requiredFields) {
        const el = document.getElementById(id);
        if (!el || !el.value) {
          event.preventDefault();
          if (errorContainer) {
            errorContainer.textContent = `Please fill out: ${id.replace("_", " ")}`;
          }
          return;
        }
      }

    });
  }

  const customerTypeEl = document.getElementById("customer_type");
  if (customerTypeEl) {
    const savedCustomerType = sessionStorage.getItem("feedback_customer_type");
    if (savedCustomerType) {
      customerTypeEl.value = savedCustomerType;
      updateServiceOptions(savedCustomerType);
    }

    customerTypeEl.addEventListener("change", () => {
      const selectedType = customerTypeEl.value;
      sessionStorage.setItem("feedback_customer_type", selectedType);
      updateServiceOptions(selectedType);
    });
  }
});

function persistClientInfoInputs() {
  const inputs = document.querySelectorAll("input, select");

  inputs.forEach(input => {
    const key = `feedback_${input.name}`;
    const saved = sessionStorage.getItem(key);

    if (saved !== null) {
      if (input.tagName === "SELECT") {
        Array.from(input.options).forEach(option => {
          option.selected = option.value === saved;
        });
      } else {
        input.value = saved;
      }
    }

    input.addEventListener("change", () => {
      sessionStorage.setItem(key, input.value);
    });
  });
}