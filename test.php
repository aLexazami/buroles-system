<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Password Rule Test</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: sans-serif; padding: 2rem; }
    .rule-icon { width: 16px; height: 16px; }
    .text-gray { color: gray; }
    .text-green { color: green; }
    .line-through { text-decoration: line-through; }
  </style>
</head>
<body>
  <h2>Set New Password</h2>
  <input type="password" id="new_password" placeholder="Enter password" style="width: 100%; padding: 0.5rem;" />

  <ul id="password-rules" style="margin-top: 1rem;">
    <li data-rule="length" class="text-gray"><img class="rule-icon" src="/assets/img/cross-icon.png"> Minimum 8 characters</li>
    <li data-rule="uppercase" class="text-gray"><img class="rule-icon" src="/assets/img/cross-icon.png"> At least one uppercase letter</li>
    <li data-rule="number" class="text-gray"><img class="rule-icon" src="/assets/img/cross-icon.png"> At least one number</li>
    <li data-rule="special" class="text-gray"><img class="rule-icon" src="/assets/img/cross-icon.png"> At least one special character</li>
  </ul>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      console.log('DOM loaded');

      const passwordInput = document.getElementById('new_password');
      const rulesList = document.getElementById('password-rules');

      if (!passwordInput || !rulesList) {
        console.log('Missing input or rules list');
        return;
      }

      ['input', 'change', 'keyup'].forEach(evt => {
        passwordInput.addEventListener(evt, () => {
          console.log(`Event triggered: ${evt}`);
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

            if (!item || !icon) return;

            icon.src = isValid ? '/assets/img/check-icon.png' : '/assets/img/cross-icon.png';
            item.classList.toggle('text-green', isValid);
            item.classList.toggle('text-gray', !isValid);
            item.classList.toggle('line-through', isValid);
          });
        });
      });
    });
  </script>
</body>
</html>