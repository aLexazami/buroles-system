export function initPasswordStrength(inputId = 'new_password', barSelector = '#strengthBar > div') {
  const passwordInput = document.getElementById(inputId);
  const strengthBar = document.querySelector(barSelector);

  if (!passwordInput || !strengthBar) return;

  passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    let strength = 0;

    if (val.length >= 8) strength += 1;
    if (/[A-Z]/.test(val)) strength += 1;
    if (/[0-9]/.test(val)) strength += 1;
    if (/[^A-Za-z0-9]/.test(val)) strength += 1;

    const colors = [
      'w-0',
      'w-1/4 bg-red-400',
      'w-2/4 bg-yellow-400',
      'w-3/4 bg-emerald-400',
      'w-full bg-emerald-600'
    ];

    strengthBar.className = `h-full transition-all duration-300 ${colors[strength]}`;
  });
}

export function toggleVisibility(fieldId, icon) {
  const input = document.getElementById(fieldId);
  if (!input) return;

  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';
  icon.src = isPassword
    ? '/assets/img/eye-closed.png'
    : '/assets/img/eye-open.png';
}

export function initPasswordRules(inputId = 'new_password', rulesSelector = '#password-rules') {
  const passwordInput = document.getElementById(inputId);
  const rulesList = document.querySelector(rulesSelector);

  if (!passwordInput || !rulesList) return;

  passwordInput.addEventListener('input', () => {
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

      if (isValid) {
        icon.src = '/assets/img/check-icon.png';
        item.classList.remove('text-gray-500');
        item.classList.add('text-emerald-600');
        text.classList.add('line-through');
      } else {
        icon.src = '/assets/img/cross-icon.png';
        item.classList.remove('text-emerald-600');
        item.classList.add('text-gray-500');
        text.classList.remove('line-through');
      }
    });
  });
}