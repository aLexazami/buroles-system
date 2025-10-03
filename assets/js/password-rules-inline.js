function initPasswordRules(inputId = 'new_password', rulesSelector = '#password-rules') {
  const passwordInput = document.getElementById(inputId);
  const rulesList = document.querySelector(rulesSelector);
  const confirmInput = document.getElementById('confirm_password');

  if (!passwordInput || !rulesList) return;

  const validate = () => {
    const value = passwordInput.value;
    const rules = {
      length: value.length >= 8,
      uppercase: /[A-Z]/.test(value),
      number: /[0-9]/.test(value),
      special: /[^A-Za-z0-9]/.test(value)
    };

    Object.entries(rules).forEach(([key, isValid]) => {
      const item = rulesList.querySelector(`[data-rule="${key}"]`);
      const icon = item?.querySelector('.rule-icon');
      const text = item?.querySelector('span');

      if (!item || !icon || !text) return;

      icon.src = isValid ? '/assets/img/check-icon.png' : '/assets/img/cross-icon.png';
      item.classList.toggle('text-emerald-600', isValid);
      item.classList.toggle('text-gray-500', !isValid);
      text.classList.toggle('line-through', isValid);
    });

    if (confirmInput) {
      const matchItem = rulesList.querySelector('[data-rule="match"]');
      const matchIcon = matchItem?.querySelector('.rule-icon');
      const matchText = matchItem?.querySelector('span');
      const isMatch = value === confirmInput.value;

      if (matchItem && matchIcon && matchText) {
        matchIcon.src = isMatch ? '/assets/img/check-icon.png' : '/assets/img/cross-icon.png';
        matchItem.classList.toggle('text-emerald-600', isMatch);
        matchItem.classList.toggle('text-gray-500', !isMatch);
        matchText.classList.toggle('line-through', isMatch);
      }
    }
  };

  ['input', 'change', 'keyup'].forEach(evt => {
    passwordInput.addEventListener(evt, validate);
    if (confirmInput) confirmInput.addEventListener(evt, validate);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-password-rules]').forEach(input => {
    initPasswordRules(input.id, input.dataset.rulesSelector);
  });
});