import { getPreviewHTML } from './preview-renderer.js';
import { renderPDFPreview } from './pdf-preview.js';
import { setupSwipeNavigation, setupMobileActionMenu } from './preview-interactions.js';
import {
  moveToNext,
  moveToPrevious,
  hasNext,
  hasPrevious
} from './carousel-state.js';

// 🎬 Render preview overlay
export function renderPreviewOverlay(item) {
  const overlay = document.getElementById('preview-overlay');
  const title = overlay?.querySelector('.preview-title');
  const preview = overlay?.querySelector('.preview-content');
  if (!overlay || !title || !preview) return;

  title.textContent = item.name || 'Preview';
  preview.innerHTML = item.type === 'folder'
    ? `<div class="text-sm text-gray-500">Preview not available for folders.</div>`
    : getPreviewHTML(item);

  if (item.mime_type === 'application/pdf') {
    renderPDFPreview(item.id);
  }

  overlay.classList.remove('hidden');
  document.body.classList.add('overflow-hidden');

  setupSwipeNavigation(renderPreviewOverlay);
  setupMobileActionMenu();
  setupPreviewActions(item, overlay, preview);
  setupPreviewNavigation(renderPreviewOverlay);
}

// 🧩 Setup action buttons
function setupPreviewActions(item, overlay, preview) {
  const closeBtn = document.getElementById('closePreview');
  const downloadBtn = document.getElementById('downloadPreview');
  const shareBtn = document.getElementById('sharePreview');
  const commentBtn = document.getElementById('commentPreview');

  closeBtn?.addEventListener('click', () => {
    overlay?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    if (preview) preview.innerHTML = ''; // ✅ Clear content
  });

  downloadBtn?.addEventListener('click', () => {
    window.open(`preview.php?id=${item.id}`, '_blank');
  });

  shareBtn?.addEventListener('click', () => openShareModal(item.id));
  commentBtn?.addEventListener('click', () => openCommentModal(item.id));
}

// 🔁 Setup prev/next buttons
function setupPreviewNavigation(renderFn) {
  const prevBtn = document.getElementById('prevPreview');
  const nextBtn = document.getElementById('nextPreview');

  if (prevBtn) {
    prevBtn.disabled = !hasPrevious();
    prevBtn.onclick = () => {
      const item = moveToPrevious();
      if (item && item.type !== 'folder') renderFn(item);
    };
  }

  if (nextBtn) {
    nextBtn.disabled = !hasNext();
    nextBtn.onclick = () => {
      const item = moveToNext();
      if (item && item.type !== 'folder') renderFn(item);
    };
  }
}