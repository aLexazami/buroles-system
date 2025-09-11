export function initDropdownMenus() {
  // Handle dot menu dropdowns
  document.querySelectorAll('.menu-toggle').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const targetId = btn.dataset.target;

      document.querySelectorAll('.menu-toggle').forEach(b => {
        const menu = document.getElementById(b.dataset.target);
        if (menu && menu.id !== targetId) menu.classList.add('hidden');
      });

      const menu = document.getElementById(targetId);
      if (menu) menu.classList.toggle('hidden');
    });
  });

  // Handle "+ New" dropdown toggle
  const newToggle = document.getElementById('newDropdownToggle');
  const newMenu = document.getElementById('newDropdownMenu');

  if (newToggle && newMenu) {
    newToggle.addEventListener('click', e => {
      e.stopPropagation();
      newMenu.classList.toggle('hidden');
    });
  }

  // Global click to close all dropdowns
  document.addEventListener('click', () => {
    // Close dot menus
    document.querySelectorAll('.menu-toggle').forEach(btn => {
      const menu = document.getElementById(btn.dataset.target);
      if (menu) menu.classList.add('hidden');
    });

    // Close "+ New" dropdown
    if (newMenu) newMenu.classList.add('hidden');
  });
}