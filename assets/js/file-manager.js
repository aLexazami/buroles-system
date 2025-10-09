// file-manager.js
import { initCommentButtons, initShareButtons, openFileInfoModal} from './modal.js';
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
      document.getElementById('file-list').innerHTML = `
        <div class="text-center text-red-500 py-12">Failed to load folder contents.</div>
      `;
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
  container.innerHTML = ''; // Clear existing

  trail.forEach((folder, index) => {
    const link = document.createElement('a');
    link.textContent = folder.name;
    link.href = '#';
    link.dataset.folderId = folder.id;
    link.classList.add('breadcrumb-link');

    link.addEventListener('click', (e) => {
      e.preventDefault();
      loadFolder(folder.id); // Load that folder's contents
    });

    container.appendChild(link);

    if (index < trail.length - 1) {
      const separator = document.createElement('span');
      separator.textContent = ' > ';
      container.appendChild(separator);
    }
  });
}

export function renderItems(items) {
  const container = document.getElementById('file-list');
  container.innerHTML = '';

  if (items.length === 0) {
    container.innerHTML = `<div class="text-center text-gray-500 py-12">No files found in this folder.</div>`;
    return;
  }

  items.forEach(item => {
    const perms = Array.isArray(item.permissions) ? item.permissions : [];

    const row = document.createElement('div');
    row.className = 'flex items-center justify-between py-4 px-2 hover:bg-gray-50 cursor-pointer';

    // 🔹 Make entire row clickable (except menu)
    row.addEventListener('click', () => {
      if (item.type === 'folder') {
        loadFolder(item.id);
      } else {
        openFilePreview(item, items);
      }
    });

    // 🔹 Left: File name
    const label = document.createElement('span');
    label.textContent = item.name;
    label.className = 'text-gray-800 font-medium';

    // 🔹 Right: Three-dot menu
    const menuWrapper = document.createElement('div');
    menuWrapper.className = 'relative';

    const menuButton = document.createElement('button');
    menuButton.innerHTML = '⋯';
    menuButton.className = 'text-gray-500 hover:text-gray-700 px-2 py-1';
    menuButton.addEventListener('click', (e) => {
      e.stopPropagation(); // Prevent row click
      const isHidden = menu.classList.contains('hidden');
      document.querySelectorAll('.file-list-menu').forEach(m => m.classList.add('hidden'));
      if (isHidden) menu.classList.remove('hidden');
    });

    const menu = document.createElement('div');
    menu.className = 'file-list-menu absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg hidden z-10';
    menu.addEventListener('click', (e) => {
      e.stopPropagation(); // Prevent row click
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      const isClickInside = menu.contains(e.target) || menuButton.contains(e.target);
      if (!isClickInside) menu.classList.add('hidden');
    });

    // ℹ️ Info
    const infoBtn = document.createElement('button');
    infoBtn.className = 'block w-full text-left px-4 py-2 hover:bg-gray-100 text-blue-600';
    infoBtn.textContent = 'ℹ️ Info';
    infoBtn.addEventListener('click', () => openFileInfoModal(item));

    // ⬇️ Download
    const downloadLink = document.createElement('a');
    downloadLink.href = `/download.php?id=${item.id}`;
    downloadLink.className = 'block px-4 py-2 hover:bg-gray-100 text-green-600';
    downloadLink.textContent = '⬇️ Download';

    menu.appendChild(infoBtn);
    menu.appendChild(downloadLink);

    // 💬 Comment
    if (perms.includes('comment')) {
      const commentBtn = document.createElement('button');
      commentBtn.className = 'comment-btn block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-600';
      commentBtn.textContent = '💬 Comment';
      commentBtn.dataset.fileId = item.id;
      menu.appendChild(commentBtn);
    }

    // 🔗 Share
    if (perms.includes('share')) {
      const shareBtn = document.createElement('button');
      shareBtn.className = 'share-btn block w-full text-left px-4 py-2 hover:bg-gray-100 text-purple-600';
      shareBtn.textContent = '🔗 Share';
      shareBtn.dataset.fileId = item.id;
      menu.appendChild(shareBtn);
    }

    // 🗑️ Delete
    if (perms.includes('delete')) {
      const deleteBtn = document.createElement('button');
      deleteBtn.className = 'block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600';
      deleteBtn.textContent = '🗑️ Delete';
      deleteBtn.addEventListener('click', () => confirmDelete(item.id));
      menu.appendChild(deleteBtn);
    }

    menuWrapper.appendChild(menuButton);
    menuWrapper.appendChild(menu);
    row.appendChild(label);
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

