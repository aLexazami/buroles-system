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

export function setupRecipientDropdown() {
  const toggle = document.getElementById('dropdown-toggle');
  const menu = document.getElementById('dropdown-menu');
  const selected = document.getElementById('selected-recipient');
  const hiddenInput = document.getElementById('recipient-id');

  if (!toggle || !menu || !selected || !hiddenInput) return;

  toggle.addEventListener('click', () => {
    menu.classList.toggle('hidden');
  });

  document.querySelectorAll('.recipient-option').forEach(option => {
    option.addEventListener('click', () => {
      const name = option.querySelector('span.text-gray-800')?.textContent;
      const role = option.querySelector('span.text-white')?.textContent;
      const id = option.dataset.id;

      selected.innerHTML = `
        <span class="bg-emerald-800 text-white px-2 py-1 rounded text-xs font-semibold">${role}</span>
        <span class="text-gray-800 font-semibold">${name}</span>
      `;
      hiddenInput.value = id;
      menu.classList.add('hidden');
    });
  });

  document.addEventListener('click', (e) => {
    if (!document.getElementById('recipient-dropdown')?.contains(e.target)) {
      menu.classList.add('hidden');
    }
  });
}