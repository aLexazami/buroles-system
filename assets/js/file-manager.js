// file-manager.js
import { initCommentButtons, openFileInfoModal, showDeleteModal, showRestoreModal, showPermanentDeleteModal, setupEmptyTrashModal, showRenameModal, openCommentModal, openShareModal, initShareHandler, openManageAccessModal, showDeleteCommentModal } from './modal.js';
import { openFilePreview } from './carousel-preview.js';
import { fileRoutes } from './endpoints/fileRoutes.js';
import { setItems, getItems, insertItemSorted } from './stores/fileStore.js';
import { renderFlash } from './flash.js';

export function refreshCurrentFolder() {
  const currentView = document.body.dataset.view || 'my-files';
  const folderId = getCurrentFolderId();

  switch (currentView) {
    case 'trash':
      loadTrashView(folderId);
      break;
    case 'shared-with-me':
      loadSharedWithMe(folderId);
      break;
    case 'shared-by-me':
      loadSharedByMe(folderId);
      break;
    default:
      loadFolder(folderId);
      break;
  }
}

export function getCurrentFolderId() {
  return document.body.dataset.folderId || null;
}

async function fetchContents(view, folderId) {
  try {
    const url = new URL('/controllers/file-manager/getFolderContents.php', window.location.origin);
    url.searchParams.set('view', view);
    url.searchParams.set('folder_id', folderId);

    const res = await fetch(url.toString());
    const data = await res.json();

    setItems(data.items);
    renderItems(getItems());
    initCommentButtons();
    initShareHandler()
  } catch (err) {
    console.error('📁 Failed to load folder contents:', err);
    const fileList = document.getElementById('file-list');
    if (fileList) {
      fileList.innerHTML = `<div class="text-center text-red-500 py-12">Failed to load folder contents.</div>`;
    }
  }
}

async function fetchBreadcrumb(folderId) {
  try {
    const view = document.body.dataset.view || 'my-files';
    const url = new URL('/controllers/file-manager/getBreadcrumbTrail.php', window.location.origin);
    url.searchParams.set('folder_id', folderId);
    url.searchParams.set('view', view); // ✅ Pass view context

    const res = await fetch(url.toString());
    const trail = await res.json();

    renderBreadcrumb(trail);
  } catch (err) {
    console.error('🧭 Failed to load breadcrumb trail:', err);
  }
}

export async function loadFolder(folderId = null) {
  const currentView = document.body.dataset.view || 'my-files';
  const normalizedFolderId = typeof folderId === 'string' ? folderId : '';

  // 🧠 Sync folder state to <body>
  document.body.dataset.folderId = normalizedFolderId;

  // 🌐 Update browser URL for deep linking (preserve current pathname)
  const basePath = window.location.pathname;
  const queryParams = new URLSearchParams();
  queryParams.set('view', currentView);
  if (normalizedFolderId) {
    queryParams.set('folder', normalizedFolderId);
  }

  const newUrl = `${basePath}?${queryParams.toString()}`;
  window.history.pushState({}, '', newUrl);

  // 🧠 Load folder contents and breadcrumb trail
  await Promise.all([
    fetchContents(currentView, normalizedFolderId),
    fetchBreadcrumb(normalizedFolderId)
  ]);
}

export async function loadTrashView(folderId = null) {
  const normalizedFolderId = typeof folderId === 'string' ? folderId : '';
  document.body.dataset.folderId = normalizedFolderId;
  document.body.dataset.view = 'trash';

  const basePath = window.location.pathname;
  const queryParams = new URLSearchParams();
  queryParams.set('view', 'trash');
  if (normalizedFolderId) {
    queryParams.set('folder', normalizedFolderId);
  }

  const newUrl = `${basePath}?${queryParams.toString()}`;
  window.history.pushState({}, '', newUrl);

  await Promise.all([
    fetchTrashContents(normalizedFolderId),
    fetchTrashBreadcrumb(normalizedFolderId)
  ]);
}

export async function loadSharedWithMe(folderId = null) {
  const normalizedFolderId = typeof folderId === 'string' ? folderId : '';
  document.body.dataset.folderId = normalizedFolderId;
  document.body.dataset.view = 'shared-with-me';

  const basePath = window.location.pathname;
  const queryParams = new URLSearchParams();
  queryParams.set('view', 'shared-with-me');
  if (normalizedFolderId) {
    queryParams.set('folder', normalizedFolderId);
  }

  const newUrl = `${basePath}?${queryParams.toString()}`;
  window.history.pushState({}, '', newUrl);

  await Promise.all([
    fetchSharedWithMeContents(normalizedFolderId),
    fetchSharedWithMeBreadcrumb(normalizedFolderId)
  ]);
}

export async function loadSharedByMe(folderId = null) {
  const normalizedFolderId = typeof folderId === 'string' ? folderId : '';
  document.body.dataset.folderId = normalizedFolderId;
  document.body.dataset.view = 'shared-by-me';

  const basePath = window.location.pathname;
  const queryParams = new URLSearchParams();
  queryParams.set('view', 'shared-by-me');
  if (normalizedFolderId) {
    queryParams.set('folder', normalizedFolderId);
  }

  const newUrl = `${basePath}?${queryParams.toString()}`;
  window.history.pushState({}, '', newUrl);

  await Promise.all([
    fetchSharedByMeContents(normalizedFolderId),
    fetchSharedByMeBreadcrumb(normalizedFolderId)
  ]);
}

export async function loadUserComments() {
  await loadComments('/controllers/file-manager/get-user-comments.php', 'comments');
}

export async function loadReceivedComments() {
  await loadComments('/controllers/file-manager/get-received-comments.php', 'comments');
}

export function toggleActive(activeBtn, inactiveBtn) {
  activeBtn.classList.add('font-semibold', 'text-emerald-700');
  activeBtn.classList.remove('text-gray-600');
  inactiveBtn.classList.remove('font-semibold', 'text-emerald-700');
  inactiveBtn.classList.add('text-gray-600');
}

let commentToDeleteElement = null;

async function loadComments(endpoint, viewKey, mode = 'user') {
  const container = document.getElementById('comment-list');
  if (!container) return;

  container.innerHTML = '<p class="text-gray-500">Loading comments...</p>';

  try {
    const res = await fetch(endpoint);
    const data = await res.json();

    if (!Array.isArray(data) || data.length === 0) {
      renderEmptyState(container, viewKey);
      return;
    }

    container.innerHTML = '';
    data.forEach(comment => {
      const isFolder = comment.type === 'folder';
      const isOwned = comment.owner_id == CURRENT_USER_ID;

      const folderId = isFolder ? comment.file_id : comment.parent_id;
      const highlightId = isFolder ? null : comment.file_id;

      const view = isOwned ? 'my-files' : 'shared-with-me';
      const link = `/pages/staff/file-manager.php?view=${view}&folder=${folderId}` +
        (highlightId ? `&highlight=${highlightId}` : '');

      const entry = document.createElement('a');
      entry.href = link;
      entry.dataset.commentId = comment.comment_id; // 🆕 for DOM tracking
      entry.className = `
        block border border-gray-200 rounded p-3 bg-gray-50 hover:bg-emerald-50
        transition-all duration-200 cursor-pointer
      `;

      const timestamp = new Date(comment.created_at).toLocaleString();
      const fileName = comment.file_name;
      const content = comment.content;
      const author = comment.first_name && comment.last_name
        ? `${comment.first_name} ${comment.last_name}`
        : 'Unknown';
      const avatar = comment.avatar_path || '/assets/img/default-avatar.png';
      const iconPath = getItemIcon(comment);
      const icon = `<img src="${iconPath}" class="w-4 h-4 mr-1" />`;

      const deleteBtn = isOwned
        ? `<button data-id="${comment.comment_id}" class="delete-comment-btn text-red-500 text-xs ml-2 hover:underline">Delete</button>`
        : '';

      entry.innerHTML = `
        <div class="flex items-start gap-3">
          <img src="${avatar}" alt="Avatar of ${author}"
               class="w-8 h-8 rounded-full object-cover mt-1" />

          <div class="flex-1">
            <div class="text-emerald-700 font-semibold mb-1 flex items-center">
              ${icon}${fileName}
            </div>
            <div class="text-xs text-gray-500 mb-1">${timestamp}</div>
            <div>${content}</div>
            <div class="text-xs text-gray-400 mt-1 italic">
              Comment by ${author}
              ${deleteBtn}
            </div>
          </div>
        </div>
      `;

      container.appendChild(entry);
    });

    // 🗑️ Attach comment delete modal trigger
    container.querySelectorAll('.delete-comment-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const commentId = btn.getAttribute('data-id');
        const entry = btn.closest('a');
        showDeleteCommentModal(commentId, entry);
      });
    });

  } catch (err) {
    container.innerHTML = '<p class="text-red-600">Failed to load comments.</p>';
    console.error(err);
  }
}


export function removeItemRow(itemId) {
  const row = document.querySelector(`[data-item-id="${itemId}"]`);
  if (!row) return;

  // 🎬 Animate fade-out
  row.classList.add('opacity-0', 'transition-opacity', 'duration-300');

  // 🧹 Remove after transition
  row.addEventListener('transitionend', () => {
    row.remove();

    const container = document.getElementById('file-list');
    const remaining = container.querySelectorAll('[data-item-id]');
    if (remaining.length === 0) {
      const currentView = document.body.dataset.view || 'default';
      renderEmptyState(container, currentView);
    }
  }, { once: true }); // Ensure it runs only once
}

async function fetchTrashContents(folderId) {
  try {
    const url = new URL('/controllers/file-manager/getFolderContents.php', window.location.origin);
    url.searchParams.set('view', 'trash');
    url.searchParams.set('folder_id', folderId);

    const res = await fetch(url.toString());
    const data = await res.json();

    document.body.dataset.folderIsDeleted = data.folder_is_deleted ? 'true' : 'false';

    setItems(data.items);
    renderItems(getItems());
    initCommentButtons();
    initShareHandler()
    setupEmptyTrashModal();
  } catch (err) {
    console.error('🗑️ Failed to load trash contents:', err);
    const fileList = document.getElementById('file-list');
    if (fileList) {
      fileList.innerHTML = `<div class="text-center text-red-500 py-12">Failed to load trash contents.</div>`;
    }
  }
}

async function fetchSharedWithMeContents(folderId) {
  try {
    const url = new URL('/controllers/file-manager/getFolderContents.php', window.location.origin);
    url.searchParams.set('view', 'shared-with-me');
    url.searchParams.set('folder_id', folderId);

    const res = await fetch(url.toString());
    const data = await res.json();

    setItems(data.items);
    renderItems(getItems());
    initCommentButtons();
    initShareHandler();
  } catch (err) {
    console.error('📤 Failed to load shared-with-me contents:', err);
    const fileList = document.getElementById('file-list');
    if (fileList) {
      fileList.innerHTML = `<div class="text-center text-red-500 py-12">Failed to load shared files.</div>`;
    }
  }
}

async function fetchSharedByMeContents(folderId) {
  try {
    const url = new URL('/controllers/file-manager/getFolderContents.php', window.location.origin);
    url.searchParams.set('view', 'shared-by-me');
    url.searchParams.set('folder_id', folderId);

    const res = await fetch(url.toString());
    const data = await res.json();

    setItems(data.items);
    renderItems(getItems());
    initCommentButtons();
    initShareHandler();
  } catch (err) {
    console.error('📤 Failed to load shared-by-me contents:', err);
    const fileList = document.getElementById('file-list');
    if (fileList) {
      fileList.innerHTML = `<div class="text-center text-red-500 py-12">Failed to load shared-by-me files.</div>`;
    }
  }
}

async function fetchSharedByMeBreadcrumb(folderId) {
  try {
    const url = new URL('/controllers/file-manager/getBreadcrumbTrail.php', window.location.origin);
    url.searchParams.set('folder_id', folderId);
    url.searchParams.set('view', 'shared-by-me');

    const res = await fetch(url.toString());
    const trail = await res.json();

    renderBreadcrumb(trail);
  } catch (err) {
    console.error('🧭 Failed to load shared-by-me breadcrumb trail:', err);
  }
}

async function fetchTrashBreadcrumb(folderId) {
  try {
    const url = new URL('/controllers/file-manager/getBreadcrumbTrail.php', window.location.origin);
    url.searchParams.set('folder_id', folderId);
    url.searchParams.set('view', 'trash');

    const res = await fetch(url.toString());
    const trail = await res.json();

    renderBreadcrumb(trail);
  } catch (err) {
    console.error('🧭 Failed to load trash breadcrumb trail:', err);
  }
}

async function fetchSharedWithMeBreadcrumb(folderId) {
  try {
    const url = new URL('/controllers/file-manager/getBreadcrumbTrail.php', window.location.origin);
    url.searchParams.set('folder_id', folderId);
    url.searchParams.set('view', 'shared-with-me');

    const res = await fetch(url.toString());
    const trail = await res.json();

    renderBreadcrumb(trail);
  } catch (err) {
    console.error('🧭 Failed to load shared-with-me breadcrumb trail:', err);
  }
}

export function insertItem(newItem, options = {}) {
  const container = document.getElementById('file-list');
  if (!container) return;

  // 🧠 Update store and sort
  insertItemSorted(newItem);
  const sortedItems = getItems();

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
    container.appendChild(row);
  } else {
    container.insertBefore(row, existingRows[newIndex]);
  }

  // 🎬 Animate appearance + highlight
  animateRowInsertion(row);

  // 🎯 Optional scroll
  if (options.scrollIntoView) {
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

function animateRowInsertion(row) {
  requestAnimationFrame(() => {
    row.classList.remove('opacity-0');
    row.classList.add('bg-emerald-50');

    setTimeout(() => {
      row.classList.remove('bg-emerald-50');
    }, 1000);
  });
}

function positionDropdown(menuButton, menu, containerSelector = '#file-list') {
  const buttonRect = menuButton.getBoundingClientRect();
  const menuHeight = menu.offsetHeight || 160;
  const container = document.querySelector(containerSelector);
  const containerRect = container?.getBoundingClientRect();

  if (!containerRect) return;

  const spaceBelow = containerRect.bottom - buttonRect.bottom;
  const spaceAbove = buttonRect.top - containerRect.top;

  menu.classList.remove('top-full', 'mt-2', 'bottom-full', 'mb-2');

  if (spaceBelow < menuHeight && spaceAbove > menuHeight) {
    menu.classList.add('bottom-full', 'mb-2');
  } else {
    menu.classList.add('top-full', 'mt-2');
  }
}

const permissionMap = {
  create: ['write', 'owner'],
  read: ['read', 'write', 'delete', 'owner'],
  update: ['write', 'owner'],
  delete: ['write', 'delete', 'owner'],
  rename: ['write', 'delete', 'owner'],
  move: ['write', 'owner'],
  share: ['share', 'owner'],
  revoke: ['share', 'owner'],
  upload: ['write', 'owner'],
  download: ['read', 'write', 'delete', 'owner'],
  restore: ['write', 'delete', 'owner'],
  'delete-permanent': ['delete', 'owner'],
  emptyTrash: ['delete', 'owner'],
  manageAccess: ['share', 'owner'],
};


export function createFileRow(item, isTrashView = false, currentUserId = null, deletionBadge = '') {
  const currentView = document.body.dataset.view || 'my-files';
  const currentFolder = document.body.dataset.folderId || '';
  const permissions = Array.isArray(item.permissions) ? item.permissions : [];

  if (String(item.owner_id) === String(currentUserId) && !permissions.includes('owner')) {
    permissions.push('owner');
  }

  const normalizedPermissions = permissions.map(p => p === 'view' ? 'read' : p);

  function canPerform(action) {
    const allowed = permissionMap[action] || [];
    return allowed.some(p => normalizedPermissions.includes(p));
  }

  const row = document.createElement('div');
  row.className = 'flex items-center justify-between p-2 hover:bg-emerald-50 cursor-pointer';
  row.dataset.itemId = item.id;
  row.dataset.parentId = item.parent_id || '';
  row.dataset.type = item.type;
  row.dataset.view = currentView;
  row.dataset.name = item.name.toLowerCase();
  row.setAttribute('role', 'listitem');

  row.addEventListener('click', () => {
    if (item.type === 'folder') {
      currentView === 'trash' ? loadTrashView(item.id) : loadFolder(item.id);
    } else {
      const allItems = getItems();
      openFilePreview(item, allItems);
    }
  });

  const icon = document.createElement('img');
  icon.src = getItemIcon(item);
  icon.alt = item.type;
  icon.className = 'w-5 h-5';

  const label = document.createElement('span');
  label.textContent = item.name;
  label.className = 'text-gray-800 font-medium truncate';

  const labelStack = document.createElement('div');
  labelStack.className = 'flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2';
  labelStack.appendChild(label);
  // 🚫 Badge intentionally omitted

  const labelWrapper = document.createElement('div');
  labelWrapper.className = 'flex items-center gap-3 min-w-0';
  labelWrapper.appendChild(icon);
  labelWrapper.appendChild(labelStack);

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
  menu.className = 'file-list-menu absolute right-8 sm:right-10 bg-white rounded shadow-lg hidden text-sm w-40 sm:w-48 transition ease-out duration-150 font-semibold';
  menu.setAttribute('role', 'menu');

  menuButton.addEventListener('click', (e) => {
    e.stopPropagation();
    const isHidden = menu.classList.contains('hidden');
    document.querySelectorAll('.file-list-menu').forEach(m => m.classList.add('hidden'));
    if (isHidden) {
      positionDropdown(menuButton, menu);
      menu.classList.remove('hidden');
    } else {
      menu.classList.add('hidden');
    }
  });

  menu.addEventListener('click', (e) => e.stopPropagation());
  document.addEventListener('click', (e) => {
    const isClickInside = menu.contains(e.target) || menuButton.contains(e.target);
    if (!isClickInside) menu.classList.add('hidden');
  });

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

  if (canPerform('manageAccess')) {
    const manageBtn = createMenuItem('Manage Access', '/assets/img/lock-icon.png', 'cursor-pointer', () => openManageAccessModal(item.id));
    manageBtn.classList.add('manage-access-btn');
    manageBtn.dataset.fileId = item.id;
    menu.appendChild(manageBtn);
  }

  if (isTrashView) {
    menu.appendChild(createMenuItem('Info', '/assets/img/info-icon.png', 'cursor-pointer', () => openFileInfoModal(item)));
    if (canPerform('restore')) {
      menu.appendChild(createMenuItem('Restore', '/assets/img/restore-icon.png', 'cursor-pointer', () => showRestoreModal(item.id)));
    }
    if (canPerform('delete-permanent')) {
      menu.appendChild(createMenuItem('Delete Permanently', '/assets/img/delete-perma.png', 'text-red-700 cursor-pointer', () => showPermanentDeleteModal(item.id)));
    }
  } else {
    if (canPerform('download')) {
      const downloadUrl = item.type === 'folder'
        ? `/pages/staff/download-folder.php?id=${item.id}&view=${currentView}&folder=${currentFolder}`
        : `/pages/staff/download.php?id=${item.id}&view=${currentView}&folder=${currentFolder}`;
      menu.appendChild(createMenuItem('Download', '/assets/img/download-icon.png', 'cursor-pointer', null, true, downloadUrl));
    }

    if (canPerform('rename')) {
      menu.appendChild(createMenuItem('Rename', '/assets/img/edit-icon.png', 'cursor-pointer', () => showRenameModal(item.id, item.name)));
    }

    if (canPerform('delete')) {
      menu.appendChild(createMenuItem('Move to Trash', '/assets/img/delete-icon.png', 'cursor-pointer', () => showDeleteModal(item.id, item.name)));
    }

    if (canPerform('share')) {
      const shareBtn = createMenuItem('Share', '/assets/img/share-icon.png', 'cursor-pointer', () => openShareModal(item.id));
      shareBtn.classList.add('share-btn');
      shareBtn.dataset.fileId = item.id;
      menu.appendChild(shareBtn);
    }

    menu.appendChild(createMenuItem('Info', '/assets/img/info-icon.png', 'cursor-pointer', () => openFileInfoModal(item)));
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

  const view = document.body.dataset.view || 'default';
  const isTrashView = view === 'trash';
  const folderIsDeleted = document.body.dataset.folderIsDeleted === 'true';
  const parentId = document.body.dataset.folderId;
  const currentUserId = document.body.dataset.userId || null;

  const allItemsAreDeleted = items.every(item =>
    item.is_deleted === true || item.is_deleted === '1'
  );

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
    renderEmptyState(container, view);
    return;
  }

  // ✅ Sort and render items
  const sortedItems = [...items].sort((a, b) => {
    if (a.type === 'folder' && b.type !== 'folder') return -1;
    if (a.type !== 'folder' && b.type === 'folder') return 1;
    return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
  });

  sortedItems.forEach(item => {
    let badge = '';

if (isTrashView && item.is_deleted) {
  if (item.deleted_by_user_id === currentUserId) {
    badge = 'Deleted by you';
  } else if (item.deleted_by_user_id && item.deleted_by_user_id !== item.owner_id) {
    badge = 'Deleted by recipient';
  } else {
    badge = 'Deleted by owner';
  }
}

    const row = createFileRow(item, isTrashView, currentUserId, badge);
    container.appendChild(row);
  });
}

export function renderSharedWithMe(items) {
  const container = document.getElementById('file-list');
  container.innerHTML = '';

  if (!items || items.length === 0) {
    container.innerHTML = `<div class="text-center text-gray-500 py-12">No files shared with you yet.</div>`;
    return;
  }

  items.forEach(item => {
    const row = createFileRow(item); // reuse your existing renderer
    container.appendChild(row);
  });
}

export function renderSharedByMe(items) {
  const container = document.getElementById('file-list');
  container.innerHTML = '';

  if (!items || items.length === 0) {
    renderEmptyState(container, 'shared-by-me');
    return;
  }

  items.forEach(item => {
    const row = createFileRow(item);
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

export function getExtension(filename) {
  const parts = filename.split('.');
  return parts.length > 1 ? parts.pop().toLowerCase() : '';
}

export function generateUniqueFolderName(baseName = 'New Folder') {
  const existingItems = getItems(); // assumes your store is up to date
  const existingNames = new Set(
    existingItems
      .filter(item => item.type === 'folder' && !item.is_deleted)
      .map(item => item.name.toLowerCase())
  );

  if (!existingNames.has(baseName.toLowerCase())) return baseName;

  let counter = 1;
  let candidate;
  do {
    candidate = `${baseName} (${counter})`;
    counter++;
  } while (existingNames.has(candidate.toLowerCase()));

  return candidate;
}

export function isValidFileName(input, originalExtension) {
  if (!input || !isFolderNameValid(input)) return false;

  const ext = getExtension(input);
  const base = input.slice(0, input.length - ext.length - 1);

  const isExact = input === `${base}.${originalExtension}`;
  const isMissing = ext === ''; // user forgot extension
  const isClean = (input.match(/\./g) || []).length <= 1;

  return (isExact || isMissing) && isClean;
}

export function isFolderNameValid(name) {
  return !/[\\/:*?"<>|]/.test(name);
}

export function normalizeFileNameInput(input, originalName) {
  const originalExt = getExtension(originalName);
  const ext = getExtension(input);

  if (!ext) {
    const base = input.replace(/\.[^/.]+$/, '');
    return `${base}.${originalExt}`;
  }

  return input;
}

export function renderBreadcrumb(trail) {
  const container = document.getElementById('breadcrumb');
  if (!container) return;

  container.innerHTML = '';

  trail.forEach((folder, index) => {
    const link = document.createElement('a');
    link.textContent = folder.name;
    link.href = '#';
    link.dataset.folderId = folder.id;
    link.classList.add('breadcrumb-link');

    link.addEventListener('click', (e) => {
      e.preventDefault();
      const view = document.body.dataset.view;
      const folderId = link.dataset.folderId;

      const isRoot = index === 0;

      if (view === 'trash') {
        isRoot ? loadTrashView(null) : loadTrashView(folderId);
      } else if (view === 'shared-with-me') {
        isRoot ? loadSharedWithMe(null) : loadSharedWithMe(folderId);
      } else if (view === 'shared-by-me') {
        isRoot ? loadSharedByMe(null) : loadSharedByMe(folderId);
      } else {
        isRoot ? loadFolder(null) : loadFolder(folderId);
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
  const config = {
    default: {
      title: 'This folder is empty',
      subtitle: 'Upload a file or create a folder to get started.',
      iconSrc: '/assets/img/empty-folder.png',
      iconAlt: 'Empty folder'
    },
    trash: {
      title: 'Trash is empty',
      subtitle: 'Deleted files and folders will appear here.',
      iconSrc: '/assets/img/empty-trash.png',
      iconAlt: 'Empty trash'
    },
    'shared-with-me': {
      title: 'No shared files yet',
      subtitle: 'Files shared with you will appear here.',
      iconSrc: '/assets/img/shared.png',
      iconAlt: 'Empty shared with me'
    },
    'shared-by-me': {
      title: 'You haven’t shared anything yet',
      subtitle: 'Files you share with others will appear here.',
      iconSrc: '/assets/img/shared.png',
      iconAlt: 'Empty shared by me'
    },
    comments: {
      title: 'No comments yet',
      subtitle: 'Comments you make will appear here.',
      iconSrc: '/assets/img/no-comment.png',
      iconAlt: 'Empty comments'
    }
  };

  const { title, subtitle, iconSrc, iconAlt } = config[view] || config.default;

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