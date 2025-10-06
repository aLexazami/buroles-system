export function initEmailAutocomplete() {
  const input = document.getElementById('shareRecipientEmail');
  const suggestions = document.getElementById('emailSuggestions');
  const avatar = document.getElementById('selectedAvatar');
  const ownerEmail = document.getElementById('shareOwnerEmail')?.value?.trim().toLowerCase() || '';

  if (!input || !suggestions || !avatar) return;

  let allUsers = [];
  let isReady = false;
  let activeIndex = -1;
  let debounceTimer = null;

  // 🔹 Fetch users once
  fetch(`/controllers/fetch-all-users.php?exclude=${encodeURIComponent(ownerEmail)}`)
    .then(res => res.ok ? res.json() : [])
    .then(data => {
      allUsers = Array.isArray(data)
        ? data.filter(user => user.email.toLowerCase() !== ownerEmail)
        : [];
      isReady = true;
    })
    .catch(() => {
      allUsers = [];
    });

  // 🔹 Render suggestion list
  function renderSuggestions(matches) {
    suggestions.innerHTML = '';
    activeIndex = -1;

    matches.forEach((user, index) => {
      const li = document.createElement('li');
      li.className = 'flex items-center gap-2 px-2 py-1 hover:bg-emerald-100 cursor-pointer rounded';
      if (index === 0) li.classList.add('highlighted');
      li.innerHTML = `
        <img src="${user.avatar_path || '/assets/img/default-avatar.png'}" class="w-6 h-6 rounded-full" alt="Avatar">
        <span>${user.email}</span>
      `;
      li.addEventListener('click', () => {
        input.value = user.email;
        avatar.src = user.avatar_path || '/assets/img/add-user.png';
        suggestions.innerHTML = '';
        suggestions.classList.add('hidden');
      });
      suggestions.appendChild(li);
    });

    suggestions.classList.remove('hidden');
  }

  // 🔹 Debounced input listener
  input.addEventListener('input', () => {
    if (!isReady) return;

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const query = input.value.trim().toLowerCase();
      suggestions.innerHTML = '';

      if (query.length === 0) {
        avatar.src = '/assets/img/add-user.png';
        suggestions.classList.add('hidden');
        return;
      }

      const matches = allUsers.filter(user =>
        user.email.toLowerCase().includes(query)
      );

      if (matches.length === 0) {
        suggestions.classList.add('hidden');
        return;
      }

      renderSuggestions(matches);
    }, 300); // 300ms debounce
  });

  // 🔹 Keyboard navigation
  input.addEventListener('keydown', (e) => {
    const items = suggestions.querySelectorAll('li');
    const isDropdownVisible = !suggestions.classList.contains('hidden');

    if (!isDropdownVisible || items.length === 0) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      activeIndex = (activeIndex + 1) % items.length;
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      activeIndex = (activeIndex - 1 + items.length) % items.length;
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (activeIndex >= 0 && items[activeIndex]) {
        items[activeIndex].click();
      }
      return;
    }

    items.forEach((item, index) => {
      item.classList.toggle('highlighted', index === activeIndex);
    });
  });

  // 🔹 Click outside to hide
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#shareRecipientEmail') && !e.target.closest('#emailSuggestions')) {
      suggestions.classList.add('hidden');
    }
  });
}