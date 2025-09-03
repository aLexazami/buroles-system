document.addEventListener("DOMContentLoaded", function () {
  const sectionClasses = [
    "js-terms-agreement-form",
    "js-client-information-form",
    "js-citizen-awareness-form",
    "js-citizen-charter-form",
    "js-client-satisfaction-form",
  ];

  const sections = sectionClasses.map(cls => document.querySelector(`.${cls}`));
  let currentSectionIndex = parseInt(sessionStorage.getItem("currentSectionIndex")) || 0;

  // Restore section visibility
  updateSection(currentSectionIndex);

  // Persist input values across reloads
  sectionClasses.forEach(cls => persistInputValues(`.${cls}`));

  // Restore customer type and service availed
  const savedCustomerType = sessionStorage.getItem("feedback_customer_type");
  if (savedCustomerType) {
    const customerTypeEl = document.getElementById("customer_type");
    customerTypeEl.value = savedCustomerType;
    updateServiceOptions(savedCustomerType); // Restore service options and selection
  }

  // Terms agreement logic
  const agreeCheckbox = document.getElementById("agree-checkbox");
  const agreeButton = document.getElementById("agree-button");

  agreeCheckbox.addEventListener("change", () => {
    agreeButton.disabled = !agreeCheckbox.checked;
  });

  agreeButton.addEventListener("click", () => {
    currentSectionIndex = 1;
    updateSection(currentSectionIndex);
  });

  function updateSection(index) {
    sections.forEach((section, i) => {
      section.style.display = i === index ? "block" : "none";
    });
    sessionStorage.setItem("currentSectionIndex", index);
  }

  function persistInputValues(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    const inputs = form.querySelectorAll("input, select, textarea");

    inputs.forEach(input => {
      const key = `feedback_${input.name}`;
      const saved = sessionStorage.getItem(key);

      // Restore saved value
      if (saved !== null) {
        if (input.tagName === "SELECT") {
          Array.from(input.options).forEach(option => {
            option.selected = option.value === saved;
          });
        } else if (input.type === "radio" || input.type === "checkbox") {
          input.checked = input.value === saved;
        } else {
          input.value = saved;
        }
      }

      // Save on change
      input.addEventListener("change", () => {
        if (input.type === "radio" || input.type === "checkbox") {
          if (input.checked) {
            sessionStorage.setItem(key, input.value);
          }
        } else {
          sessionStorage.setItem(key, input.value);
        }

        // Trigger service update if customer type changes
        if (input.name === "customer_type") {
          updateServiceOptions(input.value);
        }
      });
    });
  }

  function updateServiceOptions(customerType) {
    const serviceAvailed = document.getElementById("service_availed");
    const savedServiceId = sessionStorage.getItem("feedback_service_availed");

    serviceAvailed.innerHTML = '<option value="" disabled selected>Service Availed</option>';

    fetch(`/controllers/get-services.php?type=${encodeURIComponent(customerType)}`)
      .then(response => response.json())
      .then(data => {
        if (Array.isArray(data)) {
          data.forEach(service => {
            const option = document.createElement("option");
            option.value = service.id;
            option.textContent = service.name;
            if (savedServiceId && service.id === savedServiceId) {
              option.selected = true;
            }
            serviceAvailed.appendChild(option);
          });
        } else {
          console.warn("No services returned:", data);
        }
      })
      .catch(error => {
        console.error("Failed to load services:", error);
      });
  }

  function validateFields(fields, errorContainerId) {
    const errorContainer = document.getElementById(errorContainerId);
    if (errorContainer) errorContainer.textContent = "";

    for (const field of fields) {
      const el = document.getElementById(field.id);
      if (!el || !el.value) {
        if (errorContainer) {
          errorContainer.textContent = `Please fill out the required field: ${field.name}`;
        }
        return false;
      }
    }
    return true;
  }

  function validateRadioGroup(name, errorContainerId, message) {
    const errorContainer = document.getElementById(errorContainerId);
    if (errorContainer) errorContainer.textContent = "";

    const selected = document.querySelector(`input[name="${name}"]:checked`);
    if (!selected) {
      if (errorContainer) errorContainer.textContent = message;
      return false;
    }
    return true;
  }

  function validateClientInformationForm() {
    return validateFields([
      { id: "date", name: "Date" },
      { id: "age", name: "Age" },
      { id: "sex", name: "Sex" },
      { id: "customer_type", name: "Customer Type" },
      { id: "service_availed", name: "Service Availed" },
      { id: "region", name: "Region" },
    ], "feedback-form-error");
  }

  function validateCitizenCharterAwareness() {
    return validateRadioGroup("yes_no", "feedback-form-error2", "Please select Yes or No.");
  }

  function validateCitizenCharterForm() {
    return (
      validateRadioGroup("cc-1", "feedback-form-error3", "Please select an option for CC1.") &&
      validateRadioGroup("cc-2", "feedback-form-error3", "Please select an option for CC2.") &&
      validateRadioGroup("cc-3", "feedback-form-error3", "Please select an option for CC3.")
    );
  }

  function validateClientSatisfactionForm() {
    const box = document.querySelector(".js-client-satisfaction-form");
    const radios = box.querySelectorAll("input[type='radio']");
    const errorContainer = document.getElementById("feedback-form-error4");
    const names = new Set();

    radios.forEach(r => names.add(r.name));

    let allFilled = true;
    names.forEach(name => {
      const selected = box.querySelector(`input[name="${name}"]:checked`);
      if (!selected) allFilled = false;
    });

    if (!allFilled) {
      errorContainer.textContent = "Please answer all questions in the Client Satisfaction section.";
      return false;
    }

    errorContainer.textContent = "";
    return true;
  }

  function handleNavigation(type) {
    if (type === "next") {
      if (currentSectionIndex === 1 && !validateClientInformationForm()) return;
      if (currentSectionIndex === 2) {
        if (!validateCitizenCharterAwareness()) return;
        const yesNo = document.querySelector("input[name='yes_no']:checked");
        currentSectionIndex = yesNo?.value === "no" ? 4 : 3;
      } else if (currentSectionIndex === 3 && !validateCitizenCharterForm()) return;
      else if (currentSectionIndex === 4 && !validateClientSatisfactionForm()) return;
      else if (currentSectionIndex < sections.length - 1) currentSectionIndex++;
    } else if (type === "previous") {
      const yesNo = document.querySelector("input[name='yes_no']:checked");
      if (currentSectionIndex === 4 && yesNo?.value === "no") currentSectionIndex = 2;
      else if (currentSectionIndex > 0) currentSectionIndex--;
    }

    updateSection(currentSectionIndex);
  }

  document.querySelectorAll("button[value='next']").forEach(btn =>
    btn.addEventListener("click", () => handleNavigation("next"))
  );

  document.querySelectorAll("button[value='previous']").forEach(btn =>
    btn.addEventListener("click", () => handleNavigation("previous"))
  );

  document.querySelector('button[name="submit"]').addEventListener("click", function (event) {
    const yesNo = document.querySelector("input[name='yes_no']:checked");
    const skipCharter = yesNo?.value === "no";

    if (!skipCharter && !validateCitizenCharterForm()) {
      event.preventDefault();
      return;
    }

    if (!validateClientSatisfactionForm()) {
      event.preventDefault();
    } else {
      sessionStorage.clear(); // Clear progress and inputs after successful submit
    }
  });
});