export function initEmailAutocomplete() {
  const input = document.getElementById('shareRecipientEmail');
  const suggestions = document.getElementById('emailSuggestions');
  const avatar = document.getElementById('selectedAvatar');
  let allUsers = [];

  if (!input || !suggestions || !avatar) return;
  
  // Fetch all users once
  fetch('/controllers/fetch-all-users.php')
    .then(res => {
      if (!res.ok) throw new Error('Failed to fetch users');
      return res.json();
    })
    .then(data => {
      allUsers = data;
    })
    .catch(err => {
      console.error('User fetch error:', err);
    });

  // Input listener
  input.addEventListener('input', () => {
    const query = input.value.trim().toLowerCase();
    suggestions.innerHTML = '';

    // Auto-clear avatar if input is empty
    if (query.length === 0) {
      avatar.src = '/assets/img/add-user.png';
      suggestions.classList.add('hidden');
      return;
    }

    // Filter matches
    const matches = allUsers.filter(user =>
      user.email.toLowerCase().includes(query)
    );

    if (matches.length === 0) {
      suggestions.classList.add('hidden');
      return;
    }

    // Render suggestions
    matches.forEach(user => {
      const li = document.createElement('li');
      li.className = 'flex items-center gap-2 px-2 py-1 hover:bg-emerald-100 cursor-pointer rounded';
      li.innerHTML = `
        <img src="${user.avatar_path || '/assets/img/default-avatar.png'}" class="w-6 h-6 rounded-full" alt="Avatar">
        <span>${user.email}</span>
      `;
      li.addEventListener('click', () => {
        input.value = user.email;
        avatar.src = user.avatar_path || '/assets/img/default-avatar.png';
        suggestions.innerHTML = '';
        suggestions.classList.add('hidden');
      });
      suggestions.appendChild(li);
    });

    suggestions.classList.remove('hidden');
  });

  // Hide suggestions on outside click
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#shareRecipientEmail') && !e.target.closest('#emailSuggestions')) {
      suggestions.classList.add('hidden');
    }
  });
}