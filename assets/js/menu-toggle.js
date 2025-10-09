export function setupMenuToggle() {
  const menuBtn = document.getElementById('menu-btn-mobile');
  const menu = document.getElementById('menu-links');

  if (!menuBtn || !menu) return;

  const links = menu.querySelectorAll('.menu-link');
  const roleLinks = menu.querySelectorAll('[data-role]');

  // Toggle menu visibility
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

  // Role switching logic
  roleLinks.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const selectedRole = link.getAttribute('data-role');

      fetch('/controllers/switch-role.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `selected_role=${encodeURIComponent(selectedRole)}`
      })
        .then(response => {
          if (response.ok) {
            const redirects = {
              1: '/pages/main-staff.php',
              2: '/pages/main-admin.php',
              99: '/pages/main-super-admin.php'
            };
            window.location.href = redirects[selectedRole] || '/pages/dashboard.php';
          } else {
            alert('Failed to switch role.');
          }
        })
        .catch(error => console.error('Error switching role:', error));
    });
  });

  // Close menu when clicking outside
  document.addEventListener('click', event => {
    const isClickInsideMenu = menu.contains(event.target);
    const isClickOnButton = menuBtn.contains(event.target);

    if (!menu.classList.contains('hidden') && !isClickInsideMenu && !isClickOnButton) {
      menu.classList.add('hidden');
      links.forEach(link => link.classList.remove('show'));
    }
  });
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

// menu-toggle.js in shared-file.js
export function initMenuToggle() {
  const toggles = document.querySelectorAll('.menu-toggle');
  const dropdowns = document.querySelectorAll('.menu-dropdown');

  if (toggles.length === 0 && dropdowns.length === 0) return;

  let activeDropdown = null;

  toggles.forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();

      const wrapper = btn.closest('.relative');
      const dropdown = wrapper ? wrapper.querySelector('.menu-dropdown') : null;

      if (dropdown) {
        const isVisible = !dropdown.classList.contains('hidden');
        document.querySelectorAll('.menu-dropdown').forEach(d => d.classList.add('hidden'));
        if (!isVisible) {
          dropdown.classList.remove('hidden');
          activeDropdown = dropdown;
        } else {
          activeDropdown = null;
        }
      }
    });
  });

  dropdowns.forEach(dropdown => {
    dropdown.addEventListener('click', e => {
      e.stopPropagation();
    });
  });

  document.addEventListener('click', () => {
    if (activeDropdown) {
      activeDropdown.classList.add('hidden');
      activeDropdown = null;
    }
  });
}
// for Mobile Menu Toggle in file-manager.php
export function setupMobileActionToggle() {
  const toggleBtn = document.getElementById('mobileActionToggle');
  const menu = document.getElementById('mobileActionMenu');

  if (!toggleBtn || !menu) return;

  toggleBtn.onclick = () => {
    menu.classList.toggle('hidden');
  };
}
