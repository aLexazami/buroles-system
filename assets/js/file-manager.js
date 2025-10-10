// file-manager.js
import { initCommentButtons, initShareButtons, openFileInfoModal } from './modal.js';
import { openFilePreview } from './carousel-preview.js';


export function loadFolder(folderId) {
  // Load file/folder items
  fetch('/controllers/file-manager/getFolderContents.php?folder_id=' + folderId)
    .then(res => res.json())
    .then(items => {
      renderItems(items);
      initCommentButtons(); // ✅ Attach comment listeners
      initShareButtons();   // ✅ Attach share listeners
    })
    .catch(err => {
      console.error('Failed to load folder contents:', err);
      const fileList = document.getElementById('file-list');
      if (fileList) {
        fileList.innerHTML = `
      <div class="text-center text-red-500 py-12">Failed to load folder contents.</div>
    `;
      } else {
        console.warn('file-list container not found.');
      }
    });

  // Load breadcrumb trail
  fetch('/controllers/file-manager/getBreadcrumbTrail.php?folder_id=' + folderId)
    .then(res => res.json())
    .then(trail => {
      renderBreadcrumb(trail);
    })
    .catch(err => {
      console.error('Failed to load breadcrumb trail:', err);
    });
}

export function renderBreadcrumb(trail) {
  const container = document.getElementById('breadcrumb');
  if (!container) return;

  container.innerHTML = ''; // Clear existing

  trail.forEach((folder, index) => {
    const link = document.createElement('a');
    link.textContent = folder.name;
    link.href = '#';
    link.dataset.folderId = folder.id;
    link.classList.add('breadcrumb-link');

    link.addEventListener('click', (e) => {
      e.preventDefault();
      loadFolder(folder.id);
    });

    container.appendChild(link);

    if (index < trail.length - 1) {
      const separator = document.createElement('span');
      separator.textContent = ' > ';
      container.appendChild(separator);
    }
  });
}

function getItemIcon(item) {
  if (item.type === 'folder') {
    return '/assets/img/folder.png';
  }

  const mime = item.mime || item.mime_type || '';

  // 📷 Images
  if (mime === 'image/png' || mime === 'image/jpeg' || mime.startsWith('image/')) {
    return '/assets/img/file-icons/image-icon.png';
  }

  // 🎥 Videos
  if (mime.startsWith('video/')) {
    return '/assets/img/file-icons/video.png';
  }

  // 🔊 Audio
  if (mime.startsWith('audio/')) {
    return '/assets/img/file-icons/audio.png';
  }

  // 📄 PDF
  if (mime === 'application/pdf') {
    return '/assets/img/file-icons/pdf.png';
  }

  // 📦 ZIP
  if (
    mime === 'application/zip' ||
    mime === 'application/x-zip-compressed' ||
    mime === 'application/x-7z-compressed' ||
    mime === 'application/x-rar-compressed'
  ) {
    return '/assets/img/file-icons/zip-icon.png';
  }

  // 📝 Word
  if (
    mime === 'application/msword' ||
    mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
  ) {
    return '/assets/img/file-icons/doc.png';
  }

  // 📊 Excel
  if (
    mime === 'application/vnd.ms-excel' ||
    mime === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  ) {
    return '/assets/img/file-icons/xls.png';
  }

  // 📈 PowerPoint
  if (
    mime === 'application/vnd.ms-powerpoint' ||
    mime === 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
  ) {
    return '/assets/img/file-icons/ppt-icon.png';
  }

  // 📃 Text
  if (mime.startsWith('text/')) {
    return '/assets/img/file-icons/text-icon.png';
  }

  // 🗂 Default
  return '/assets/img/file-icons/file-icon.png';
}

export function renderItems(items) {
  const container = document.getElementById('file-list');
  if (!container) return;
  container.innerHTML = '';

  if (items.length === 0) {
    container.innerHTML = `<div class="text-center text-gray-500 py-12">No files found in this folder.</div>`;
    return;
  }

  items.forEach(item => {
    const perms = Array.isArray(item.permissions) ? item.permissions : [];

    const row = document.createElement('div');
    row.className = 'flex items-center justify-between py-4 px-2 hover:bg-emerald-50 cursor-pointer';

    row.addEventListener('click', () => {
      if (item.type === 'folder') {
        loadFolder(item.id);
      } else {
        openFilePreview(item, items);
      }
    });

    // 📄 Icon + Label
    const icon = document.createElement('img');
    icon.src = getItemIcon(item);
    icon.alt = item.type;
    icon.className = 'w-5 h-5 mr-3';

    const label = document.createElement('span');
    label.textContent = item.name;
    label.className = 'text-gray-800 font-medium';

    const labelWrapper = document.createElement('div');
    labelWrapper.className = 'flex items-center gap-2';
    labelWrapper.appendChild(icon);
    labelWrapper.appendChild(label);

    // ⋯ Menu
    const menuWrapper = document.createElement('div');
    menuWrapper.className = 'relative';

    const menuButton = document.createElement('button');
    const menuIcon = document.createElement('img');
    menuIcon.src = '/assets/img/dots-icon.png';
    menuIcon.alt = 'Menu';
    menuIcon.className = 'w-5 h-5 pointer-events-none';
    menuButton.appendChild(menuIcon);
    menuButton.setAttribute('aria-label', 'Open menu');
    menuButton.setAttribute('type', 'button');
    menuButton.className = 'hover:bg-emerald-100 rounded-full px-2 py-2 cursor-pointer';

    const menu = document.createElement('div');
    menu.className = 'file-list-menu absolute right-8 sm:right-10 top-0 sm:top-1 bg-white rounded shadow-lg hidden text-sm w-40 sm:w-44 transition ease-out duration-150 font-semibold';

    menuButton.addEventListener('click', (e) => {
      e.stopPropagation();
      const isHidden = menu.classList.contains('hidden');
      document.querySelectorAll('.file-list-menu').forEach(m => m.classList.add('hidden'));
      if (isHidden) {
        menu.classList.remove('hidden');
      } else {
        menu.classList.add('hidden');
      }
    });

    menu.addEventListener('click', (e) => {
      e.stopPropagation();
    });

    document.addEventListener('click', (e) => {
      const isClickInside = menu.contains(e.target) || menuButton.contains(e.target);
      if (!isClickInside) menu.classList.add('hidden');
    });

    // 🔧 Menu Item Helper
    const createMenuItem = (type, labelText, iconPath, colorClass, onClick, isLink = false, href = '') => {
      const wrapper = isLink ? document.createElement('a') : document.createElement('button');
      wrapper.className = `flex items-center gap-3 w-full text-left px-4 py-2 hover:bg-emerald-100 ${colorClass}`;
      if (isLink) wrapper.href = href;
      if (onClick) wrapper.addEventListener('click', onClick);

      const icon = document.createElement('img');
      icon.src = iconPath;
      icon.alt = labelText;
      icon.className = 'w-4 h-4';

      const label = document.createElement('span');
      label.textContent = labelText;

      wrapper.appendChild(icon);
      wrapper.appendChild(label);
      return wrapper;
    };

    // ℹ️ Info
    const infoBtn = createMenuItem('info', 'Info', '/assets/img/info-icon.png', 'cursor-pointer', () => openFileInfoModal(item));
    menu.appendChild(infoBtn);

    // ⬇️ Download
    const downloadLink = createMenuItem('download', 'Download', '/assets/img/download-icon.png', 'cursor-pointer', null, true, `/download.php?id=${item.id}`);
    menu.appendChild(downloadLink);

    // 💬 Comment
    if (perms.includes('comment')) {
      const commentBtn = createMenuItem('comment', 'Comment', '/assets/img/comment.png', 'cursor-pointer', null);
      commentBtn.classList.add('comment-btn');
      commentBtn.dataset.fileId = item.id;
      menu.appendChild(commentBtn);
    }

    // 🔗 Share
    if (perms.includes('share')) {
      const shareBtn = createMenuItem('share', 'Share', '/assets/img/share-icon.png', 'cursor-pointer', null);
      shareBtn.classList.add('share-btn');
      shareBtn.dataset.fileId = item.id;
      menu.appendChild(shareBtn);
    }

    // 🗑️ Delete
    if (perms.includes('delete')) {
      const deleteBtn = createMenuItem('delete', 'Delete', '/assets/img/delete-icon.png', 'text-red-600 cursor-pointer', () => confirmDelete(item.id));
      menu.appendChild(deleteBtn);
    }

    menuWrapper.appendChild(menuButton);
    menuWrapper.appendChild(menu);
    row.appendChild(labelWrapper);
    row.appendChild(menuWrapper);
    container.appendChild(row);
  });
}

export function formatSize(bytes) {
  if (bytes === 0) return '—';
  const units = ['B', 'KB', 'MB', 'GB'];
  let i = 0;
  while (bytes >= 1024 && i < units.length - 1) {
    bytes /= 1024;
    i++;
  }
  return `${bytes.toFixed(1)} ${units[i]}`;
}

export function formatDate(ts) {
  return new Date(ts).toLocaleString();
}

