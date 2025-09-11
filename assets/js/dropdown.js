export function initDropdownMenus() {
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

  document.addEventListener('click', () => {
    document.querySelectorAll('.menu-toggle').forEach(btn => {
      const menu = document.getElementById(btn.dataset.target);
      if (menu) menu.classList.add('hidden');
    });
  });
}