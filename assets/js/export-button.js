export function initExportDropdown(toggleId = 'exportToggle', menuId = 'exportMenu') {
  const toggle = document.getElementById(toggleId);
  const menu = document.getElementById(menuId);

  if (!toggle || !menu) return;

  toggle.addEventListener('click', () => {
    menu.classList.toggle('hidden');
  });

  document.addEventListener('click', (e) => {
    if (!toggle.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.add('hidden');
    }
  });
}