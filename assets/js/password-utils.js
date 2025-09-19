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