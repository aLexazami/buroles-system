export function initAnnouncementCarousel() {
  const slides = document.querySelectorAll('.announcement-slide');
  const dots = document.querySelectorAll('.dot');
  const dotTrack = document.getElementById('dot-track');

  if (!dotTrack || slides.length === 0 || dots.length === 0) return; // 🛡️ Prevent error

  const maxVisibleDots = 10;
  const dotSize = 24;
  let current = 0;

  function showSlide(index) {
    slides.forEach((slide, i) => {
      slide.classList.toggle('hidden', i !== index);
    });

    dots.forEach((dot, i) => {
      dot.classList.toggle('opacity-100', i === index);
      dot.classList.toggle('bg-emerald-500', i === index);
      dot.classList.toggle('bg-gray-300', i !== index);
    });

    const offset = Math.max(0, index - Math.floor(maxVisibleDots / 2));
    dotTrack.style.transform = `translateX(-${offset * dotSize}px)`;
  }

  document.getElementById('prev-announcement')?.addEventListener('click', () => {
    current = (current - 1 + slides.length) % slides.length;
    showSlide(current);
  });

  document.getElementById('next-announcement')?.addEventListener('click', () => {
    current = (current + 1) % slides.length;
    showSlide(current);
  });

  dots.forEach(dot => {
    dot.addEventListener('click', () => {
      current = parseInt(dot.dataset.index);
      showSlide(current);
    });
  });

  showSlide(current);
}

export function bindAnnouncementReadTracking() {
  document.querySelectorAll('[data-viewer-trigger]').forEach(el => {
    const id = el.dataset.id;

    el.addEventListener('click', () => {
      fetch('/api/announcement-read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ announcement_id: id })
      });

      el.classList.remove('bg-emerald-50', 'hover:bg-emerald-100');
      el.classList.add('bg-white', 'hover:bg-gray-100');

      const badge = el.querySelector('span');
      if (badge && badge.textContent.trim().toLowerCase() === 'new') {
        badge.remove();
      }
    });
  });
}

export function bindAnnouncementViewerTriggers() {
  document.querySelectorAll('[data-viewer-trigger]').forEach(el => {
    el.addEventListener('click', () => {
      const title = el.dataset.title || '';
      const body = el.dataset.body || '';
      const role = el.dataset.role || '';
      const date = el.dataset.date || '';

      const viewerTitle = document.querySelector('#viewerTitle');
      if (viewerTitle) viewerTitle.textContent = title;

      const viewerBody = document.querySelector('#viewerBody');
      if (viewerBody) viewerBody.textContent = body;

      const viewerMeta = document.querySelector('#viewerMeta');
      if (viewerMeta) viewerMeta.textContent = `Posted for role ${role} on ${date}`;

      const viewer = document.querySelector('#announcement-viewer');
      if (viewer) viewer.classList.remove('hidden');
    });
  });

  document.querySelector('#closeAnnouncementViewer')?.addEventListener('click', () => {
    document.querySelector('#announcement-viewer')?.classList.add('hidden');
  });
}

export function setupAnnouncementPagination() {
  const container = document.querySelector('#announcement-container');
  const pagination = document.querySelector('#pagination-container');

  function loadPage(page = 1) {
    container.innerHTML = '<p>Loading...</p>';

    fetch(`/api/announcements.php?page=${page}`)
      .then(res => res.json())
      .then(data => {
        container.innerHTML = data.announcements.map(note => `
          <div class="relative cursor-pointer p-5 border-l-4 border-emerald-600 transition ${note.is_new ? 'bg-emerald-50 hover:bg-emerald-100' : 'bg-white hover:bg-gray-100'}"
            data-viewer-trigger
            data-id="${note.id}"
            data-title="${note.title}"
            data-body="${note.body}"
            data-role="${note.target_role_id}"
            data-date="${new Date(note.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}">
            ${note.is_new ? '<span class="new-badge absolute top-2 left-1 text-[8px] bg-green-600 text-white px-2 py-1 rounded-full z-10">New</span>' : ''}
            <h3 class="font-semibold text-gray-800">${note.title}</h3>
            <p class="text-xs text-gray-500 italic">${note.time_ago}</p>
          </div>
        `).join('');

        pagination.innerHTML = `
  <nav class="mt-6 flex justify-center items-center gap-2">
    <button data-page="1"
      ${data.pagination.current_page <= 1 ? 'disabled aria-disabled="true"' : ''}
      class="px-3 py-1 rounded cursor-pointer text-sm text-white font-medium transition
        ${data.pagination.current_page <= 1
            ? 'border cursor-not-allowed'
            : 'bg-emerald-800 hover:bg-emerald-700'}">
      First
    </button>

    <button data-page="${data.pagination.current_page - 1}"
      ${data.pagination.current_page <= 1 ? 'disabled aria-disabled="true"' : ''}
      class="px-3 py-1 rounded cursor-pointer text-sm text-white font-medium transition
        ${data.pagination.current_page <= 1
            ? 'border cursor-not-allowed'
            : 'bg-emerald-800 hover:bg-emerald-700'}">
      Previous
    </button>

    <button data-page="${data.pagination.current_page + 1}"
      ${data.pagination.current_page >= data.pagination.total_pages ? 'disabled aria-disabled="true"' : ''}
      class="px-3 py-1 rounded cursor-pointer text-sm text-white font-medium transition
        ${data.pagination.current_page >= data.pagination.total_pages
            ? 'border  cursor-not-allowed'
            : 'bg-emerald-800 hover:bg-emerald-700'}">
      Next
    </button>

    <button data-page="${data.pagination.total_pages}"
      ${data.pagination.current_page >= data.pagination.total_pages ? 'disabled aria-disabled="true"' : ''}
      class="px-3 py-1 rounded cursor-pointer text-sm text-white font-medium transition
        ${data.pagination.current_page >= data.pagination.total_pages
            ? 'border  cursor-not-allowed'
            : 'bg-emerald-800 hover:bg-emerald-700'}">
      Last
    </button>
  </nav>
`;
        bindPaginationButtons();
        bindAnnouncementViewerTriggers();
        bindAnnouncementReadTracking();
      })
      .catch(err => {
        container.innerHTML = '<p class="text-red-600">Failed to load announcements.</p>';
        console.error('Error loading announcements:', err);
      });
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