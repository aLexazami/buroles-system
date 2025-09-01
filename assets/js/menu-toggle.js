export function setupMenuToggle() {
  const menuBtn = document.getElementById('menu-btn-mobile');
  const menu = document.getElementById('menu-links');

  if (menuBtn && menu) {
    const links = menu.querySelectorAll('.menu-link');

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
  }
}