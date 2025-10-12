import {
  setPreviewState,
  moveToNext,
  moveToPrevious,
  hasNext,
  hasPrevious,
  moveToNextPreviewable,
  moveToPreviousPreviewable
} from './carousel-state.js';
import { toggleModal } from './modal.js';

pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

// 🖼️ Generate preview HTML based on MIME type
export function getPreviewHTML(item) {
  const currentView = document.body.dataset.view || 'my-files';
  const currentFolder = document.body.dataset.folderId || '';
  const fileUrl = `preview.php?id=${item.id}&view=${encodeURIComponent(currentView)}&folder=${encodeURIComponent(currentFolder)}`;
  const mime = item.mime_type || '';
  console.log('MIME Type:', mime);

  const isImage = mime.startsWith('image/');
  const isVideo = mime.startsWith('video/');
  const isAudio = mime.startsWith('audio/');
  const isText = mime.startsWith('text/');
  const isPDF = mime === 'application/pdf';

  const officeTypes = new Set([
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
  ]);

  const renderers = {
    image: `
  <div class="w-full flex justify-center items-center min-h-full">
    <img src="${fileUrl}" class="max-w-full max-h-[80vh] object-contain rounded shadow" />
  </div>
`,
    pdf: `
  <div class="pdf-container w-full flex flex-col items-center px-4 space-y-4">
    <!-- Canvases will be injected here -->
  </div>
`,
    video: `
  <div class="w-full flex justify-center items-center min-h-full px-4 py-4">
    <video controls src="${fileUrl}" class="w-full max-h-[80vh] rounded shadow"></video>
  </div>
`,
    audio: `
  <div class="w-full flex justify-center items-center min-h-full px-4 py-4">
    <audio controls src="${fileUrl}" class="w-full max-w-2xl"></audio>
  </div>
`,
    text: `
  <div class="w-full flex justify-center items-center min-h-full px-4 py-4">
    <iframe src="${fileUrl}" class="w-full h-[80vh] border rounded shadow"></iframe>
  </div>
`,
    office: `
  <div class="w-full flex justify-center items-center min-h-full px-4 py-4 text-center text-sm text-gray-500">
    Office file preview not supported inline.<br>
    <a href="${fileUrl}" class="text-blue-600 underline">Download</a>
  </div>
`,
    fallback: `
  <div class="w-full flex justify-center items-center min-h-full px-4 py-4 text-center text-sm text-gray-500">
    Preview not available for this file type.<br>
    <a href="${fileUrl}" class="text-blue-600 underline">Download</a>
  </div>
`,
  };

  if (isImage) return renderers.image;
  if (isPDF) return renderers.pdf;
  if (isVideo) return renderers.video;
  if (isAudio) return renderers.audio;
  if (isText) return renderers.text;
  if (officeTypes.has(mime)) return renderers.office;

  return renderers.fallback;
}

// 📁 Open preview overlay
export function openFilePreview(item, items = []) {
  if (!items || items.length === 0) return;
  setPreviewState(items, item.id);
  renderPreviewOverlay(item);
  
}

// 🎬 Render preview overlay
function renderPreviewOverlay(item) {
  const overlay = document.getElementById('preview-overlay');
  const titleContainer = overlay?.querySelector('.preview-title');
  const preview = overlay?.querySelector('.preview-content');
  const navContainer = document.getElementById('pdf-navigation');
  const indicator = document.getElementById('page-indicator');

  // ✅ Hide navigation bar and remove page indicator before rendering
  navContainer?.classList.add('hidden');
  navContainer.innerHTML = '';
  indicator?.remove();

  if (!overlay || !titleContainer || !preview) return;

  // ✅ Update title and icon
  const titleText = titleContainer.querySelector('#fileTitle');
  const icon = titleContainer.querySelector('#fileTypeIcon');

  if (titleText) titleText.textContent = item.name || 'Preview';

  if (icon) {
    const mime = item.mime_type || '';
    let iconPath = '/assets/img/file-icon.png'; // default

    if (mime.startsWith('image/')) {
      iconPath = '/assets/img/file-icons/image-icon.png';
    } else if (mime === 'application/pdf') {
      iconPath = '/assets/img/file-icons/pdf.png';
    } else if (mime.startsWith('video/')) {
      iconPath = '/assets/img/file-icons/video.png';
    } else if (mime.startsWith('audio/')) {
      iconPath = '/assets/img/file-icons/audio.png';
    } else if (
      mime.includes('word') ||
      mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ) {
      iconPath = '/assets/img/file-icons/doc.png';
    } else if (
      mime.includes('excel') ||
      mime === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ) {
      iconPath = '/assets/img/file-icons/excel.png';
    } else if (item.type === 'folder') {
      iconPath = '/assets/img/folder.png';
    }

    icon.src = iconPath;
    icon.alt = mime || 'File Type';
  }

  // ✅ Render preview content
  preview.innerHTML = item.type === 'folder'
    ? `<div class="text-sm text-gray-500">Preview not available for folders.</div>`
    : getPreviewHTML(item);

  if (item.mime_type === 'application/pdf') {
    renderPDFPreview(item.id);
  }

  // ✅ Show overlay using reusable helper
  toggleModal('preview-overlay', true);

  setupPreviewListeners();
  setupPreviewActions(item);
  setupPreviewNavigation(renderPreviewOverlay);
}

// 📄 Render multi-page PDF
function renderPDFPreview(fileId) {
  const currentView = document.body.dataset.view || 'my-files';
  const currentFolder = document.body.dataset.folderId || '';
  const fileUrl = `preview.php?id=${fileId}&view=${encodeURIComponent(currentView)}&folder=${encodeURIComponent(currentFolder)}`;
  const container = document.querySelector('.pdf-container');
  const scrollContainer = document.querySelector('#preview-overlay .flex-1');
  const navContainer = document.getElementById('pdf-navigation');
  if (!container || !scrollContainer || !navContainer) return;

  // ✅ Clear previous content and show navigation bar
  container.innerHTML = '';
  navContainer.innerHTML = '';
  navContainer.classList.remove('hidden');
  container.classList.add('pb-10');

  // 🌀 Global loading spinner
  const globalSpinner = document.createElement('div');
  globalSpinner.className = 'w-full flex justify-center items-center py-6';
  globalSpinner.innerHTML = `
    <div class="flex items-center gap-2 animate-fade-in">
      <div class="animate-spin w-6 h-6 border-4 border-gray-300 border-t-blue-500 rounded-full"></div>
      <span class="text-sm text-gray-500">Loading PDF…</span>
    </div>
  `;
  container.appendChild(globalSpinner);

  // 🔢 Navigation bar
  const nav = document.createElement('div');
  nav.className = 'flex justify-center';
  navContainer.appendChild(nav);

  const navGroup = document.createElement('div');
  navGroup.className = 'flex items-center gap-2 bg-[rgba(0,0,0,0.8)] px-2 py-1 rounded';
  nav.appendChild(navGroup);


  // ◀ Prev button with icon
  const prevBtn = document.createElement('button');
  prevBtn.className = 'p-2 hover:bg-[rgba(255,250,250,0.2)] rounded shadow';
  prevBtn.innerHTML = `<img src="/assets/img/left-arrow-white.png" alt="Previous" class="w-4 h-4 cursor-pointer" />`;
  navGroup.appendChild(prevBtn);

  // 🔽 Custom dropdown
  const dropdownWrapper = document.createElement('div');
  dropdownWrapper.className = 'relative flex justify-center w-full';
  navGroup.appendChild(dropdownWrapper);

  const dropdownTrigger = document.createElement('button');
  dropdownTrigger.className = 'px-3 py-2 bg-white/80 hover:bg-white text-sm rounded shadow cursor-pointer ';
  dropdownTrigger.textContent = 'Page 1 ▼';
  dropdownWrapper.appendChild(dropdownTrigger);

  // Track open state
  let isDropdownOpen = false;

  // Toggle dropdown
  dropdownTrigger.onclick = (e) => {
    e.stopPropagation();
    dropdownList.classList.toggle('hidden');
    isDropdownOpen = !dropdownList.classList.contains('hidden');
  };

  // Close on outside click
  document.addEventListener('click', (e) => {
    if (!dropdownWrapper.contains(e.target)) {
      dropdownList.classList.add('hidden');
      isDropdownOpen = false;
    }
  });

  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (!isDropdownOpen) return;

    const items = dropdownList.querySelectorAll('li');
    const active = dropdownList.querySelector('li.active');
    let index = Array.from(items).indexOf(active);

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (index < items.length - 1) index++;
      items.forEach(i => i.classList.remove('active'));
      items[index].classList.add('active');
      items[index].scrollIntoView({ block: 'nearest' });
    }

    if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (index > 0) index--;
      items.forEach(i => i.classList.remove('active'));
      items[index].classList.add('active');
      items[index].scrollIntoView({ block: 'nearest' });
    }

    if (e.key === 'Enter') {
      e.preventDefault();
      if (items[index]) items[index].click();
    }

    if (e.key === 'Escape') {
      dropdownList.classList.add('hidden');
      isDropdownOpen = false;
    }
  });

  const dropdownList = document.createElement('ul');
  dropdownList.className = `
  absolute bottom-full mb-2 w-32 max-h-60 overflow-y-auto bg-white border rounded shadow hidden z-50
  transition-all duration-150 ease-in-out left-1/2 -translate-x-1/2
`;
  dropdownWrapper.appendChild(dropdownList);

  // ▶ Next button with icon
  const nextBtn = document.createElement('button');
  nextBtn.className = 'p-2 hover:bg-[rgba(255,250,250,0.2)] rounded shadow';
  nextBtn.innerHTML = `<img src="/assets/img/right-arrow-white.png" alt="Next" class="w-4 h-4 cursor-pointer" />`;
  navGroup.appendChild(nextBtn);


  // 🔁 Load PDF
  pdfjsLib.getDocument(fileUrl).promise.then(pdf => {
    const totalPages = pdf.numPages;
    let currentPage = 1;

    // Populate dropdown
    for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
      const item = document.createElement('li');
      item.className = 'px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer';
      item.textContent = `Page ${pageNum}`;

      item.onclick = () => {
        scrollToPage(pageNum);
        dropdownList.classList.add('hidden');
      };

      // ✅ Highlight on hover for keyboard navigation
      item.onmouseenter = () => {
        dropdownList.querySelectorAll('li').forEach(i => i.classList.remove('active'));
        item.classList.add('active:bg-gray-200');
      };

      dropdownList.appendChild(item);
    }

    // Toggle dropdown
    dropdownTrigger.onclick = () => {
      dropdownList.classList.toggle('hidden');
    };

    // Scroll to page
    function scrollToPage(pageNum) {
      const target = document.getElementById(`pdf-page-${pageNum}`);
      if (target) scrollContainer.scrollTo({ top: target.offsetTop, behavior: 'smooth' });
      dropdownTrigger.textContent = `Page ${pageNum} ▼`;
      currentPage = pageNum;
    }

    // Prev/Next navigation
    prevBtn.onclick = () => {
      if (currentPage > 1) scrollToPage(currentPage - 1);
    };

    nextBtn.onclick = () => {
      if (currentPage < totalPages) scrollToPage(currentPage + 1);
    };

    // Lazy load pages
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.dataset.rendered) {
          const pageNum = parseInt(entry.target.dataset.pageNum);

          pdf.getPage(pageNum).then(page => {
            const viewport = page.getViewport({ scale: 1.2 });
            const canvas = document.createElement('canvas');
            canvas.className = 'block w-full h-auto shadow';
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            const context = canvas.getContext('2d');
            page.render({ canvasContext: context, viewport }).promise.then(() => {
              entry.target.appendChild(canvas);
              entry.target.dataset.rendered = 'true';

              if (globalSpinner) globalSpinner.remove();
            });
          });
        }
      });
    }, {
      root: scrollContainer,
      rootMargin: '100px',
      threshold: 0.1
    });

    // Create page wrappers
    for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
      const wrapper = document.createElement('div');
      wrapper.id = `pdf-page-${pageNum}`;
      wrapper.dataset.pageNum = pageNum;
      wrapper.className = 'w-full max-w-4xl mx-auto mb-6 flex flex-col items-center';

      container.appendChild(wrapper);
      observer.observe(wrapper);
    }

    // 📌 Scroll detection inside modal
    scrollContainer.addEventListener('scroll', () => {
      const pages = document.querySelectorAll('[id^="pdf-page-"]');
      let current = 1;
      let closestDistance = Infinity;

      pages.forEach((page, index) => {
        const offset = page.offsetTop;
        const distance = Math.abs(scrollContainer.scrollTop - offset);
        if (distance < closestDistance) {
          closestDistance = distance;
          current = index + 1;
        }
      });

      current = Math.max(1, Math.min(current, pages.length));
      dropdownTrigger.textContent = `Page ${current} ▼`;
      currentPage = current;
    });
  });
}

// 🧩 Setup listeners
function setupPreviewListeners() {
  setupMobileActionToggle()
  setupSwipeNavigation(); // ✅ added back
}

// for Mobile Menu Toggle in file-manager.php
export function setupMobileActionToggle() {
  const toggleBtn = document.getElementById('mobileActionToggle');
  const menu = document.getElementById('mobileActionMenu');
  const overlay = document.getElementById('preview-overlay');

  if (!toggleBtn || !menu || !overlay) return;

  // Ensure menu starts off-screen
  menu.style.transform = 'translateY(100%)';

  // Show menu
  const showMenu = () => {
    menu.classList.remove('hidden');
    menu.classList.add('flex');
    void menu.offsetHeight; // Force reflow
    menu.style.transform = 'translateY(0)';
    document.body.classList.add('overflow-hidden');
  };

  // Hide menu
  const hideMenu = () => {
    menu.style.transform = 'translateY(100%)';
    setTimeout(() => {
      menu.classList.remove('flex');
      menu.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }, 200);
  };

  // Toggle on button click
  toggleBtn.onclick = () => {
    const isHidden = menu.classList.contains('hidden');
    isHidden ? showMenu() : hideMenu();
  };

  // Swipe down to close (passive listener to avoid Chrome warning)
  let startY = 0;
  menu.addEventListener('touchstart', (e) => {
    startY = e.touches[0].clientY;
  }, { passive: true });

  menu.addEventListener('touchend', (e) => {
    const endY = e.changedTouches[0].clientY;
    const deltaY = endY - startY;
    if (deltaY > 50) hideMenu();
  });

  // Tap outside to close
  overlay.addEventListener('click', (e) => {
    const isInsideMenu = menu.contains(e.target);
    const isToggleBtn = toggleBtn.contains(e.target);
    if (!isInsideMenu && !isToggleBtn && !menu.classList.contains('hidden')) {
      hideMenu();
    }
  });
}

// 🧩 Setup action buttons
function setupPreviewActions(item) {
  const closeBtn = document.getElementById('closePreview');
  const overlay = document.getElementById('preview-overlay');

  if (closeBtn && overlay) {
    closeBtn.onclick = () => {
      overlay.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    };
  }

  const downloadBtn = document.getElementById('downloadPreview');
  if (downloadBtn) {
    downloadBtn.onclick = () => {
      window.open(`preview.php?id=${item.id}`, '_blank');
    };
  }

  const shareBtn = document.getElementById('sharePreview');
  if (shareBtn) {
    shareBtn.onclick = () => openShareModal(item.id);
  }

  const commentBtn = document.getElementById('commentPreview');
  if (commentBtn) {
    commentBtn.onclick = () => openCommentModal(item.id);
  }
}

function setupSwipeNavigation() {
  const previewArea = document.querySelector('.preview-content');
  if (!previewArea) return;

  let touchStartX = 0;

  previewArea.addEventListener('touchstart', function (e) {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  previewArea.addEventListener('touchend', function (e) {
    const touchEndX = e.changedTouches[0].screenX;
    handleSwipeGesture(touchStartX, touchEndX);
  }, { passive: true });
}

function handleSwipeGesture(startX, endX, threshold = 50) {
  const deltaX = endX - startX;
  if (Math.abs(deltaX) < threshold) return;

  const direction = deltaX > 0 ? 'right' : 'left';
  navigatePreview(direction);
}

function navigatePreview(direction) {
  if (direction === 'left' && hasNext()) {
    const item = moveToNext();
    if (item && item.type !== 'folder') {
      renderPreviewOverlay(item);
    }
  }

  if (direction === 'right' && hasPrevious()) {
    const item = moveToPrevious();
    if (item && item.type !== 'folder') {
      renderPreviewOverlay(item);
    }
  }
}

// 🧩 Setup prev/next buttons Overlay
export function setupPreviewNavigation(renderPreviewOverlay) {
  const prevBtn = document.getElementById('prevPreview');
  const nextBtn = document.getElementById('nextPreview');

  // ✅ Dry-run checks for previewable items
  const hasNextPreviewable = !!moveToNextPreviewable(true);
  const hasPrevPreviewable = !!moveToPreviousPreviewable(true);

  // ◀ Prev button
  if (prevBtn) {
    prevBtn.style.display = hasPrevPreviewable ? 'flex' : 'none';
    prevBtn.onclick = () => {
      const item = moveToPreviousPreviewable();
      if (item) {
        renderPreviewOverlay(item);
      }
    };
  }

  // ▶ Next button
  if (nextBtn) {
    nextBtn.style.display = hasNextPreviewable ? 'flex' : 'none';
    nextBtn.onclick = () => {
      const item = moveToNextPreviewable();
      if (item) {
        renderPreviewOverlay(item);
      }
    };
  }
}
