// feedbacking-form.js
// This script handles the navigation between different sections of the feedback form.
// It shows/hides sections based on user input and manages the flow of the form.
document.addEventListener("DOMContentLoaded", function () {
  const sections = [
    document.querySelector(".js-terms-agreement-form"),
    document.querySelector(".js-client-information-form"),
    document.querySelector(".js-citizen-awareness-form"),
    document.querySelector(".js-citizen-charter-form"),
    document.querySelector(".js-client-satisfaction-form"),
  ];

  let currentSectionIndex = 0;

    const agreeCheckbox = document.getElementById("agree-checkbox");
const agreeButton = document.getElementById("agree-button");

agreeCheckbox.addEventListener("change", () => {
  agreeButton.disabled = !agreeCheckbox.checked;
});

agreeButton.addEventListener("click", () => {
  currentSectionIndex = 1; // Move to client info form
  showSection(currentSectionIndex);
});

  // Function to show the current section and hide others
  function showSection(index) {
    sections.forEach((section, i) => {
      section.style.display = i === index ? "block" : "none";
    });
  }

  


  // Function to validate required fields in client-information-form-box
  function validateClientInformationForm() {
    const requiredFields = [
      { id: "date", name: "Date" },
      { id: "age", name: "Age" },
      { id: "sex", name: "Sex" },
      { id: "customer_type", name: "Customer Type" },
      { id: "service_availed", name: "Service Availed" },
      { id: "region", name: "Region" },
    ];

    // Clear any existing error message
    const errorContainer = document.getElementById("feedback-form-error");
    if (errorContainer) {
      errorContainer.textContent = "";
    }

    // Check if all required fields are filled
    for (const field of requiredFields) {
      const element = document.getElementById(field.id);
      if (!element || !element.value) {
        if (errorContainer) {
          errorContainer.textContent = `Please fill out the required field: ${field.name}`;
        }
        return false;
      }
    }
    return true;
  }



  // Function to validate required fields in citizen-charter-form
  function validateCitizenCharterAwareness() {
    const errorContainer = document.getElementById("feedback-form-error2");
    if (errorContainer) {
      errorContainer.textContent = "";
    }

    // Check if a value is selected for the yes_no radio button
    const yesNoValue = document.querySelector("input[name='yes_no']:checked");
    if (!yesNoValue) {
      if (errorContainer) {
        errorContainer.textContent = "Please select Yes or No.";
      }
      return false;
    }

    return true;
  }

  // Function to validate required fields in citizen-charter-form
  function validateCitizenCharterForm() {
    const errorContainer = document.getElementById("feedback-form-error3");
    if (errorContainer) {
      errorContainer.textContent = "";
    }

    // Check if a value is selected for the cc-1 radio button
    const cc1Value = document.querySelector("input[name='cc-1']:checked");
    if (!cc1Value) {
      if (errorContainer) {
        errorContainer.textContent = "Please select an option for CC1.";
      }
      return false;
    }

    // Check if a value is selected for the cc-2 radio button
    const cc2Value = document.querySelector("input[name='cc-2']:checked");
    if (!cc2Value) {
      if (errorContainer) {
        errorContainer.textContent = "Please select an option for CC2.";
      }
      return false;
    }

    // Check if a value is selected for the cc-3 radio button
    const cc3Value = document.querySelector("input[name='cc-3']:checked");
    if (!cc3Value) {
      if (errorContainer) {
        errorContainer.textContent = "Please select an option for CC3.";
      }
      return false;
    }

    return true;
  }

  // Function to validate required fields in client-satisfaction-form-box
  function validateClientSatisfactionForm() {
    const clientSatisfactionBox = document.querySelector('.js-client-satisfaction-form');
    const radioGroups = clientSatisfactionBox.querySelectorAll('input[type="radio"]');
    const errorContainer = document.getElementById('feedback-form-error4');
    const radioNames = new Set();

    // Collect all unique radio input names
    radioGroups.forEach(radio => radioNames.add(radio.name));

    // Check if at least one radio is selected for each group
    let allFilled = true;
    radioNames.forEach(name => {
        const group = clientSatisfactionBox.querySelectorAll(`input[name="${name}"]:checked`);
        if (group.length === 0) {
            allFilled = false;
        }
    });

    if (!allFilled) {
        errorContainer.textContent = "Please answer all questions in the Client Satisfaction section.";
        return false;
    } else {
        errorContainer.textContent = "";
        return true;
    }
  }

  // Function to handle navigation between sections
  // This function is called when the user clicks the next or previous button
function handleNavigation(buttonType) {
  if (buttonType === "next") {
    if (currentSectionIndex === 1) {
      // Validate client-information-form
      if (!validateClientInformationForm()) {
        return;
      }
      currentSectionIndex++; // Move to citizen-awareness-form
    } else if (currentSectionIndex === 2) {
      // Validate citizen-awareness-form
      if (!validateCitizenCharterAwareness()) {
        return;
      }
      const yesNoValue = document.querySelector("input[name='yes_no']:checked");
      if (yesNoValue && yesNoValue.value === "no") {
        currentSectionIndex = 4; // Skip to client-satisfaction-form
      } else {
        currentSectionIndex++; // Move to citizen-charter-form
      }
    } else if (currentSectionIndex === 3) {
      // Validate citizen-charter-form
      if (!validateCitizenCharterForm()) {
        return;
      }
      currentSectionIndex++; // Move to client-satisfaction-form
    } else if (currentSectionIndex === 4) {
      // Validate client-satisfaction-form
      if (!validateClientSatisfactionForm()) {
        return;
      }
      currentSectionIndex++; // End or submit
    } else if (currentSectionIndex < sections.length - 1) {
      currentSectionIndex++;
    }
  } else if (buttonType === "previous") {
    if (currentSectionIndex === 4) {
      const yesNoValue = document.querySelector("input[name='yes_no']:checked");
      if (yesNoValue && yesNoValue.value === "no") {
        currentSectionIndex = 2;
      } else {
        currentSectionIndex--;
      }
    } else if (currentSectionIndex > 0) {
      currentSectionIndex--;
    }
  }

  showSection(currentSectionIndex);
}

  // Add event listeners to the next buttons
  document.querySelectorAll("button[value='next']").forEach((button) => {
    button.addEventListener("click", () => handleNavigation("next"));
  });

  // Add event listeners to the previous buttons
  document.querySelectorAll("button[value='previous']").forEach((button) => {
    button.addEventListener("click", () => handleNavigation("previous"));
  });

  // Add event listener to the submit button
document.querySelector('button[name="submit"]').addEventListener('click', function (event) {
  const errorContainer = document.getElementById('feedback-form-error4');
  const clientSatisfactionBox = document.querySelector('.js-client-satisfaction-form');
  const radioGroups = clientSatisfactionBox.querySelectorAll('input[type="radio"]');
  const radioNames = new Set();

  // Collect all unique radio input names
  radioGroups.forEach(radio => radioNames.add(radio.name));

  let allFilled = true;
  radioNames.forEach(name => {
    const group = clientSatisfactionBox.querySelectorAll(`input[name="${name}"]:checked`);
    if (group.length === 0) {
      allFilled = false;
    }
  });

  // Check if citizen charter awareness is "no"
  const yesNoValue = document.querySelector("input[name='yes_no']:checked");
  const skipCharterValidation = yesNoValue && yesNoValue.value === "no";

  // If not skipping, validate citizen charter form
  if (!skipCharterValidation && !validateCitizenCharterForm()) {
    event.preventDefault();
    return;
  }

  // Validate client satisfaction
  if (!allFilled) {
    event.preventDefault();
    errorContainer.textContent = "Please answer all questions in the Client Satisfaction section.";
  } else {
    errorContainer.textContent = "";
  }
});

  // Initialize the first section
  showSection(currentSectionIndex);
});