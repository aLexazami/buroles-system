export function setupRoleSwitcher() {
  // Desktop toggle
  const desktopBtn = document.getElementById('menu-btn-desktop');
  const roleSwitcherDesktop = document.getElementById('role-switcher-desktop');

  if (desktopBtn && roleSwitcherDesktop) {
    desktopBtn.addEventListener('click', e => {
      e.preventDefault();
      roleSwitcherDesktop.classList.toggle('hidden');
    });

    document.addEventListener('click', e => {
      if (!desktopBtn.contains(e.target) && !roleSwitcherDesktop.contains(e.target)) {
        roleSwitcherDesktop.classList.add('hidden');
      }
    });
  }

  // Role switching (desktop only)
  const roleLinks = roleSwitcherDesktop?.querySelectorAll('[data-role]') || [];
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
}