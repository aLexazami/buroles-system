// file-manager.js
import { initCommentButtons, initShareButtons, openFileInfoModal, showDeleteModal, showRestoreModal, showPermanentDeleteModal, setupEmptyTrashModal } from './modal.js';
import { openFilePreview } from './carousel-preview.js';
import { fileRoutes } from './endpoints/fileRoutes.js';
import { setItems, getItems, insertItemSorted } from './stores/fileStore.js';
import { renderFlash } from './flash.js';

export function refreshCurrentFolder() {
  const currentView = document.body.dataset.view || 'my-files';

  if (currentView === 'trash') {
    loadTrashView(document.body.dataset.folderId); // ✅ pass folderId
  } else {
    const currentFolderId = getCurrentFolderId();
    loadFolder(currentFolderId);
  }
}

export function getCurrentFolderId() {
  return document.body.dataset.folderId || null;
}

export async function loadFolder(folderId = null) {
  const currentView = document.body.dataset.view || 'my-files';
  const normalizedFolderId = folderId && typeof folderId === 'string' ? folderId : '';

  // 🧠 Sync folder state to <body>
  document.body.dataset.folderId = normalizedFolderId;

  try {
    // 📁 Fetch folder contents
    const contentsUrl = new URL('/controllers/file-manager/getFolderContents.php', window.location.origin);
    contentsUrl.searchParams.set('view', currentView);
    contentsUrl.searchParams.set('folder_id', normalizedFolderId);

    const contentsRes = await fetch(contentsUrl.toString());
    const contentsData = await contentsRes.json();

    setItems(contentsData.items);
    renderItems(getItems());
    initCommentButtons();
    initShareButtons();
  } catch (err) {
    console.error('Failed to load folder contents:', err);
    const fileList = document.getElementById('file-list');
    if (fileList) {
      fileList.innerHTML = `<div class="text-center text-red-500 py-12">Failed to load folder contents.</div>`;
    }
  }

  try {
    // 🧭 Fetch breadcrumb trail
    const breadcrumbUrl = new URL('/controllers/file-manager/getBreadcrumbTrail.php', window.location.origin);
    breadcrumbUrl.searchParams.set('folder_id', normalizedFolderId);

    const breadcrumbRes = await fetch(breadcrumbUrl.toString());
    const breadcrumbTrail = await breadcrumbRes.json();

    renderBreadcrumb(breadcrumbTrail);
  } catch (err) {
    console.error('Failed to load breadcrumb trail:', err);
  }
}

export async function loadTrashView(folderId = null) {
  const normalizedFolderId = typeof folderId === 'string' ? folderId : '';
  document.body.dataset.folderId = normalizedFolderId;
  document.body.dataset.view = 'trash';

  try {
    // 🔍 Fetch trash contents
    const url = new URL('/controllers/file-manager/getFolderContents.php', window.location.origin);
    url.searchParams.set('view', 'trash');
    url.searchParams.set('folder_id', normalizedFolderId);

    const res = await fetch(url.toString());
    const data = await res.json();

    document.body.dataset.folderIsDeleted = data.folder_is_deleted ? 'true' : 'false';

    setItems(data.items);
    renderItems(getItems());
    initCommentButtons();
    initShareButtons();

    // 🧭 Setup modal logic after rendering
    setupEmptyTrashModal();
  } catch (err) {
    console.error('Failed to load trash contents:', err);
    const fileList = document.getElementById('file-list');
    if (fileList) {
      fileList.innerHTML = `
        <div class="text-center text-red-500 py-12">
          Failed to load trash contents.
        </div>
      `;
    }
  }

  try {
    // 🧭 Fetch breadcrumb trail with trash context
    const breadcrumbUrl = new URL('/controllers/file-manager/getBreadcrumbTrail.php', window.location.origin);
    breadcrumbUrl.searchParams.set('folder_id', normalizedFolderId);
    breadcrumbUrl.searchParams.set('view', 'trash'); // ✅ Ensure trash-aware trail

    const breadcrumbRes = await fetch(breadcrumbUrl.toString());
    const breadcrumbTrail = await breadcrumbRes.json();

    renderBreadcrumb(breadcrumbTrail);
  } catch (err) {
    console.error('Failed to load breadcrumb trail:', err);
  }
}

export function insertItem(newItem, options = {}) {
  const container = document.getElementById('file-list');
  if (!container) return;

  // 🧠 Update store and sort
  insertItemSorted(newItem);
  const sortedItems = getItems(); // already sorted by insertItemSorted()

  // 🧩 Create row
  const newIndex = sortedItems.findIndex(item => item.id === newItem.id);
  const row = createFileRow(newItem, sortedItems);
  row.classList.add('opacity-0', 'transition-opacity', 'duration-300');

  // 🧼 Remove empty state if present
  const emptyState = container.querySelector('.empty-state');
  if (emptyState) emptyState.remove();

  // 📌 Insert at correct position
  const existingRows = container.querySelectorAll('[data-item-id]');
  if (existingRows.length === 0 || newIndex >= existingRows.length) {
    container.appendChild(row); // fallback for empty list
  } else {
    container.insertBefore(row, existingRows[newIndex]);
  }

  // 🎬 Animate appearance
  requestAnimationFrame(() => {
    row.classList.remove('opacity-0');
  });

  // 🎯 Optional scroll
  if (options.scrollIntoView) {
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

export function createFileRow(item, isTrashView = false) {
  const row = document.createElement('div');
  row.className = 'flex items-center justify-between p-2 hover:bg-emerald-50 cursor-pointer';
  row.dataset.itemId = item.id;
  row.dataset.parentId = item.parent_id || '';
  row.setAttribute('role', 'listitem');

  // 🧭 Click behavior
  row.addEventListener('click', () => {
    if (item.type === 'folder') {
      const currentView = document.body.dataset.view || 'my-files';
      if (currentView === 'trash') {
        loadTrashView(item.id);
      } else {
        loadFolder(item.id);
      }
    } else {
      openFilePreview(item);
    }
  });

  // 📄 Icon
  const icon = document.createElement('img');
  icon.src = getItemIcon(item);
  icon.alt = item.type;
  icon.className = 'w-5 h-5';

  // 📄 Label
  const label = document.createElement('span');
  label.textContent = item.name;
  label.className = 'text-gray-800 font-medium truncate';

  // 🏷️ Badge (conditionally visible)
  const badge = document.createElement('span');
  badge.textContent = `Removed From: ${item.original_parent_name}`;
  badge.className = 'hidden min-[650px]:inline-block text-xs text-gray-600 bg-emerald-100 px-2 py-1 rounded-md';

  // 📦 Label stack
  const labelStack = document.createElement('div');
  labelStack.className = 'flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2';
  labelStack.appendChild(label);
  if (isTrashView && item.original_parent_name) {
    labelStack.appendChild(badge);
  }

  // 📦 Label wrapper
  const labelWrapper = document.createElement('div');
  labelWrapper.className = 'flex items-center gap-3 min-w-0';
  labelWrapper.appendChild(icon);
  labelWrapper.appendChild(labelStack);

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
  menuButton.setAttribute('aria-haspopup', 'true');
  menuButton.setAttribute('type', 'button');
  menuButton.className = 'hover:bg-emerald-100 rounded-full px-2 py-2 cursor-pointer';

  const menu = document.createElement('div');
  menu.className = 'file-list-menu absolute right-8 sm:right-10 top-0 sm:top-1 bg-white rounded shadow-lg hidden text-sm w-40 sm:w-48 transition ease-out duration-150 font-semibold';
  menu.setAttribute('role', 'menu');

  // 🧠 Menu toggle logic
  menuButton.addEventListener('click', (e) => {
    e.stopPropagation();
    const isHidden = menu.classList.contains('hidden');
    document.querySelectorAll('.file-list-menu').forEach(m => m.classList.add('hidden'));
    menu.classList.toggle('hidden', !isHidden);
  });

  menu.addEventListener('click', (e) => e.stopPropagation());

  document.addEventListener('click', (e) => {
    const isClickInside = menu.contains(e.target) || menuButton.contains(e.target);
    if (!isClickInside) menu.classList.add('hidden');
  });

  // 🔧 Menu Item Helper
  const createMenuItem = (labelText, iconPath, colorClass, onClick, isLink = false, href = '') => {
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
  menu.appendChild(createMenuItem('Info', '/assets/img/info-icon.png', 'cursor-pointer', () => openFileInfoModal(item)));

  // ⬇️ Download
  menu.appendChild(createMenuItem('Download', '/assets/img/download-icon.png', 'cursor-pointer', null, true, `/download.php?id=${item.id}`));

  if (isTrashView) {
    menu.appendChild(createMenuItem('Restore', '/assets/img/restore-icon.png', ' cursor-pointer', () => showRestoreModal(item.id)));
    menu.appendChild(createMenuItem('Delete Permanently', '/assets/img/delete-perma.png', 'text-red-700 cursor-pointer', () => showPermanentDeleteModal(item.id)));
  } else {
    if (item.permissions?.includes('comment')) {
      const commentBtn = createMenuItem('Comment', '/assets/img/comment.png', 'cursor-pointer', null);
      commentBtn.classList.add('comment-btn');
      commentBtn.dataset.fileId = item.id;
      menu.appendChild(commentBtn);
    }

    if (item.permissions?.includes('share')) {
      const shareBtn = createMenuItem('Share', '/assets/img/share-icon.png', 'cursor-pointer', null);
      shareBtn.classList.add('share-btn');
      shareBtn.dataset.fileId = item.id;
      menu.appendChild(shareBtn);
    }

    if (item.permissions?.includes('delete')) {
      menu.appendChild(createMenuItem('Move to Trash', '/assets/img/delete-icon.png', 'text-emerald-600 cursor-pointer', () => showDeleteModal(item.id, item.name)));
    }
  }

  menuWrapper.appendChild(menuButton);
  menuWrapper.appendChild(menu);
  row.appendChild(labelWrapper);
  row.appendChild(menuWrapper);

  return row;
}

export function renderItems(items) {
  const container = document.getElementById('file-list');
  if (!container) return;

  container.innerHTML = '';

  const isTrashView = document.body.dataset.view === 'trash';
  const folderIsDeleted = document.body.dataset.folderIsDeleted === 'true';
  const parentId = document.body.dataset.folderId;
  const allItemsAreDeleted = items.every(item => item.is_deleted === true || item.is_deleted === '1');

  // 🧭 Trash header logic
  const trashHeader = document.getElementById('trash-header');
  if (trashHeader) {
    const shouldShowHeader = isTrashView && items.length > 0 && !allItemsAreDeleted;
    trashHeader.style.display = shouldShowHeader ? 'flex' : 'none';
  }

  // 🧠 Trash view fallback
  if (isTrashView && folderIsDeleted && (items.length === 0 || allItemsAreDeleted)) {
    const folderName = document.querySelector('.breadcrumb-current')?.textContent?.trim() || 'This folder';
    renderTrashFallbackState(container, folderName, parentId);
    return;
  }

  // 🧼 Empty state
  if (items.length === 0) {
    renderEmptyState(container, isTrashView ? 'trash' : 'default');
    return;
  }

  // ✅ Sort and render items
  const sortedItems = [...items].sort((a, b) => {
    if (a.type === 'folder' && b.type !== 'folder') return -1;
    if (a.type !== 'folder' && b.type === 'folder') return 1;
    return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
  });

  sortedItems.forEach(item => {
    const row = createFileRow(item, isTrashView);
    container.appendChild(row);
  });
}

export async function fetchFolderSize(folderId) {
  const res = await fetch('/api/get-folder-size.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: folderId })
  });
  const data = await res.json();
  return data.success ? formatSize(data.size) : '—';
}

export function formatSize(bytes) {
  if (typeof bytes !== 'number' || isNaN(bytes)) return '—';
  if (bytes === 0) return '0 B (0 bytes)';

  const units = ['B', 'KB', 'MB', 'GB'];
  let i = 0;
  let display = bytes;

  while (display >= 1024 && i < units.length - 1) {
    display /= 1024;
    i++;
  }

  return `${display.toFixed(1)} ${units[i]} (${bytes} bytes)`;
}

export async function resolveItemSize(item) {
  if (item.type === 'folder') {
    if (typeof item.size === 'number' && !isNaN(item.size)) {
      return formatSize(item.size); // ✅ Use prehydrated size
    }

    // 🧠 Fallback to API if size is missing
    try {
      const res = await fetch('/api/get-folder-size.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: item.id })
      });
      const data = await res.json();
      return data.success ? formatSize(data.size) : '—';
    } catch (err) {
      console.warn('⚠️ Folder size fetch failed:', err);
      return '—';
    }
  }

  return formatSize(item.size);
}

export function renderBreadcrumb(trail) {
  const container = document.getElementById('breadcrumb');
  if (!container) return;

  container.innerHTML = ''; // Clear existing

  const isTrashView = document.body.dataset.view === 'trash';

  trail.forEach((folder, index) => {
    const link = document.createElement('a');
    link.textContent = folder.name;
    link.href = '#';
    link.dataset.folderId = folder.id;
    link.classList.add('breadcrumb-link');

    link.addEventListener('click', (e) => {
      e.preventDefault();
      if (isTrashView) {
        loadTrashView(folder.id); // ✅ Stay inside trash view
      } else {
        loadFolder(folder.id);
      }
    });

    container.appendChild(link);

    if (index < trail.length - 1) {
      const separator = document.createElement('span');
      separator.textContent = ' > ';
      container.appendChild(separator);
    }
  });
}

export function formatDate(ts) {
  return new Date(ts).toLocaleString();
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


/**
 * Handles file-related actions (e.g., delete, rename, move) by dispatching requests
 * to the appropriate backend endpoint and managing UI feedback.
 *
 * @param {string} action - The type of action to perform (e.g., 'delete', 'rename').
 * @param {object} payload - The data to send with the request (e.g., { id, newName }).
 * @param {number} retries - Number of retry attempts on failure (default: 1).
 *
 * This function supports:
 * - Modular endpoint routing via `fileRoutes[action]`
 * - Optimistic UI updates for delete actions (instant feedback before server confirms)
 * - Rollback logic to restore UI if the server fails
 * - Retry mechanism for resilience against transient errors
 *
 * Returns:
 * - { success: true } for standard actions
 * - { success: true, optimistic: true, rollback: function } for optimistic actions
 *
 * Throws:
 * - Error if the action is unknown or the server response indicates failure
 */
export async function handleFileAction(action, payload = {}, retries = 1) {
  const endpoint = fileRoutes[action];
  if (!endpoint) throw new Error(`Unknown action: ${action}`);

  // ✅ Optimistic UI update for delete and restore
  if ((action === 'delete' || action === 'restore' || action === 'deletePermanent') && payload.id) {
    const rollback = () => {
      refreshCurrentFolder();
      renderFlash('error', `${action === 'restore' ? 'Restore' : 'Delete'} failed. Item reloaded.`);
    };

    // 🧹 Remove item and its children from UI
    removeItemFromUI(payload.id);
    removeChildrenFromUI?.(payload.id); // ✅ optional helper if you have one

    for (let attempt = 0; attempt <= retries; attempt++) {
      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Action failed');

        return { success: true, optimistic: true, rollback };
      } catch (err) {
        rollback();
        throw err;
      }
    }
  }

  // Default fallback for non-optimistic actions
  for (let attempt = 0; attempt <= retries; attempt++) {
    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!data.success) throw new Error(data.message || 'Action failed');
      return { success: true };
    } catch (err) {
      if (attempt === retries) throw err;
    }
  }
}

export function removeItemFromUI(id) {
  const itemRow = document.querySelector(`[data-item-id="${id}"]`);
  if (itemRow) itemRow.remove();

  const container = document.getElementById('file-list');
  const remaining = container.querySelectorAll('[data-item-id]');

  if (remaining.length === 0) {
    refreshCurrentFolder(); // ✅ triggers correct fallback logic
  }
}

function removeChildrenFromUI(parentId) {
  const container = document.getElementById('file-list');
  if (!container) return;

  const rows = container.querySelectorAll(`[data-parent-id="${parentId}"]`);
  rows.forEach(row => row.remove());
}

/*Extract fallback into a reusable helper*/
export function renderEmptyState(container, view = 'default') {
  let title = 'This folder is empty';
  let subtitle = 'Upload a file or create a folder to get started.';
  let iconSrc = '/assets/img/empty-folder.png';
  let iconAlt = 'Empty folder';

  if (view === 'trash') {
    title = 'Trash is empty';
    subtitle = 'Deleted files and folders will appear here.';
    iconSrc = '/assets/img/empty-trash.png'; // ✅ use your trash icon here
    iconAlt = 'Empty trash';
  }

  container.innerHTML = `
    <div class="flex flex-col items-center justify-center py-12 text-gray-500">
      <img src="${iconSrc}" alt="${iconAlt}" class="w-16 h-16 mb-4 opacity-60" />
      <p class="text-lg font-medium">${title}</p>
      <p class="text-sm text-gray-400 mt-1">${subtitle}</p>
    </div>
  `;
}

export function renderTrashFallbackState(container, folderName = 'This folder', folderId) {
  container.innerHTML = `
    <div class="text-center text-gray-500 py-12">
      <p class="text-lg font-medium mb-2">${folderName} is in the trash.</p>
      <p class="text-sm text-gray-400">Its contents are also deleted.</p>
      <p class="text-sm text-emerald-600 font-medium mt-2">Restore the folder first to view its contents.</p>
      <button id="restore-folder-btn" class="mt-4 px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition">
        Restore Folder
      </button>
    </div>
  `;

  const restoreBtn = document.getElementById('restore-folder-btn');
  if (restoreBtn) {
    restoreBtn.addEventListener('click', () => {
      showRestoreModal(folderId);
    });
  }
}