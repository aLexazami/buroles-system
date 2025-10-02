export function setupRoleCheckboxToggle() {
  const allCheckbox = document.querySelector('input[value="100"]');
  const otherCheckboxes = document.querySelectorAll('.role-checkbox:not([value="100"])');

  if (!allCheckbox || otherCheckboxes.length === 0) return;

  allCheckbox.addEventListener('change', () => {
    if (allCheckbox.checked) {
      otherCheckboxes.forEach(cb => {
        cb.checked = false;
        cb.disabled = true;
      });
    } else {
      otherCheckboxes.forEach(cb => cb.disabled = false);
    }
  });
}