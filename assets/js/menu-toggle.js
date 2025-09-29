export function setupMenuToggle() {
  const menuBtn = document.getElementById('menu-btn-mobile');
  const menu = document.getElementById('menu-links');

  if (menuBtn && menu) {
    const links = menu.querySelectorAll('.menu-link');

    // Toggle menu on button click
    menuBtn.addEventListener('click', () => {
      menu.classList.toggle('hidden');

      if (!menu.classList.contains('hidden')) {
        links.forEach((link, i) => {
          setTimeout(() => link.classList.add('show'), i * 100);
        });
      } else {
        links.forEach(link => link.classList.remove('show'));
      }
    });

    // Close menu when clicking outside
    document.addEventListener('click', (event) => {
      const isClickInsideMenu = menu.contains(event.target);
      const isClickOnButton = menuBtn.contains(event.target);

      if (!menu.classList.contains('hidden') && !isClickInsideMenu && !isClickOnButton) {
        menu.classList.add('hidden');
        links.forEach(link => link.classList.remove('show'));
      }
    });
  }
}

export function setupSidebarToggle() {
  const openSidebar = document.getElementById('open-sidebar');
  const mobileSidebar = document.getElementById('mobile-sidebar');

  if (openSidebar && mobileSidebar) {
    openSidebar.addEventListener('click', () => {
      mobileSidebar.classList.remove('-translate-x-full');
    });
  }

  // Close sidebar when clicking outside
  document.addEventListener('click', (event) => {
    const isOpen = !mobileSidebar.classList.contains('-translate-x-full');
    const clickedInsideSidebar = mobileSidebar.contains(event.target);
    const clickedToggleButton = openSidebar.contains(event.target);

    if (isOpen && !clickedInsideSidebar && !clickedToggleButton) {
      mobileSidebar.classList.add('-translate-x-full');
    }
  });
}