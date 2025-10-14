

export function bindAnnouncementTriggers() {
  document.querySelectorAll('[data-viewer-trigger]').forEach(el => {
    el.addEventListener('click', () => {
      const { id, title = '', body = '', roleName = '', date = '' } = el.dataset;

      fetch('/api/announcement-read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ announcement_id: id })
      });

      el.classList.remove('bg-emerald-50', 'hover:bg-emerald-100');
      el.classList.add('bg-white', 'hover:bg-gray-100');
      el.querySelector('.new-badge')?.remove();

      const viewerTitle = document.querySelector('#viewerTitle');
      if (viewerTitle) viewerTitle.textContent = title;

      const viewerBody = document.querySelector('#viewerBody');
      if (viewerBody) viewerBody.textContent = body;

      const viewerMeta = document.querySelector('#viewerMeta');
      if (viewerMeta) viewerMeta.textContent = `Posted for ${roleName} on ${date}`;

      document.querySelector('#announcement-viewer')?.classList.remove('hidden');
    });
  });

  document.querySelector('#closeAnnouncementViewer')?.addEventListener('click', () => {
    document.querySelector('#announcement-viewer')?.classList.add('hidden');
  });
}

export function setupAnnouncementPagination() {
  const container = document.querySelector('#announcement-container');
  const list = document.querySelector('#announcement-list'); // where cards are injected
  const pagination = document.querySelector('#pagination-container');
  const fallback = document.getElementById('announcement-fallback');
  fallback.classList.remove('hidden');

  if (!container || !list || !pagination || !fallback) return;

  function loadPage(page = 1) {
    list.innerHTML = '<p>Loading...</p>';
    pagination.innerHTML = '';
    pagination.classList.add('hidden'); // hide pagination by default
    fallback.classList.add('hidden');   // hide fallback by default

    fetch(`/api/announcements.php?page=${page}`)
      .then(res => res.json())
      .then(data => {
        const hasAnnouncements = data.announcements.length > 0;

        // Inject cards or show fallback
        if (hasAnnouncements) {
          const cardsHTML = data.announcements.map(renderAnnouncementCard).join('');
          list.innerHTML = cardsHTML;
          fallback.classList.add('hidden');
        } else {
          list.innerHTML = '';
          fallback.classList.remove('hidden');
        }

        bindAnnouncementTriggers();

        // Show pagination only if needed
        if (data.pagination.total_pages > 1) {
          pagination.innerHTML = renderPagination(data.pagination);
          pagination.classList.remove('hidden');
          bindPaginationButtons();
        }
      })
      .catch(err => {
        list.innerHTML = '<p class="text-red-600">Failed to load announcements.</p>';
        console.error('Error loading announcements:', err);
      });
  }

  function renderAnnouncementCard(note) {
    const dateFormatted = new Date(note.created_at).toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric'
    });

    const newBadge = !note.already_read
      ? `<span class="new-badge absolute top-2 left-1 text-[8px] bg-green-600 text-white px-2 py-1 rounded-full z-10">New</span>`
      : '';

    const deleteButton = window.isSuperAdmin
      ? `
        <div class="mt-6 flex justify-end items-center gap-x-2">
          <form method="POST" action="/actions/announcement/delete-announcement.php">
            <input type="hidden" name="announcement_id" value="${note.id}">
            <button type="submit"
              class="rounded-full p-2 hover:bg-red-100 hover:scale-110 transition-transform duration-200 cursor-pointer"
              onclick="event.stopPropagation();">
              <img src="/assets/img/delete-icon.png" alt="Delete Announcement" class="w-5 h-5" />
            </button>
          </form>
        </div>
      `
      : '';

    return `
      <div class="relative p-5 mb-4 border-l-4 border-emerald-600 transition ${note.already_read ? 'bg-white hover:bg-gray-100' : 'bg-emerald-50 hover:bg-emerald-100'}"
        data-viewer-trigger
        data-id="${note.id}"
        data-title="${note.title}"
        data-body="${note.body}"
        data-role-name="${note.role_name}"
        data-date="${dateFormatted}">
        ${newBadge}
        <h3 class="font-semibold text-gray-800 break-words">${note.title}</h3>
        <p class="mt-3 text-sm text-gray-700 break-words whitespace-pre-line">${note.body}</p>
        <hr class="mt-5 pt-2 border-gray-300">
        <p class="text-xs text-gray-500 italic">${note.time_ago}</p>
        ${deleteButton}
      </div>
    `;
  }

  function renderPagination(p) {
    return `
      <nav class="mb-5 flex justify-center items-center gap-2">
        ${renderPageButton('First', 1, p.current_page <= 1)}
        ${renderPageButton('Previous', p.current_page - 1, p.current_page <= 1)}
        ${renderPageButton('Next', p.current_page + 1, p.current_page >= p.total_pages)}
        ${renderPageButton('Last', p.total_pages, p.current_page >= p.total_pages)}
      </nav>
    `;
  }

  function renderPageButton(label, page, disabled) {
    return `
      <button data-page="${page}"
        ${disabled ? 'disabled aria-disabled="true"' : ''}
        class="px-3 py-1 rounded text-sm text-white font-medium transition ${disabled ? 'border cursor-not-allowed' : 'bg-emerald-800 cursor-pointer hover:bg-emerald-700'}">
        ${label}
      </button>
    `;
  }

  function bindPaginationButtons() {
    pagination.querySelectorAll('button[data-page]').forEach(btn => {
      btn.addEventListener('click', () => {
        const page = parseInt(btn.dataset.page);
        loadPage(page);
      });
    });
  }

  loadPage();
}