// 🔄 Carousel state module

let previewItems = [];
let currentPreviewIndex = -1;

// 📦 Set preview state
export function setPreviewState(items, currentItemId) {
  previewItems = Array.isArray(items) ? items : [];
  currentPreviewIndex = previewItems.findIndex(i => i.id === currentItemId);
}

// 📍 Get current item
export function getCurrentItem() {
  return previewItems[currentPreviewIndex] || null;
}

// 🔢 Get current index
export function getCurrentIndex() {
  return currentPreviewIndex;
}

// 🔁 Move to next item
export function moveToNext() {
  if (currentPreviewIndex < previewItems.length - 1) {
    currentPreviewIndex++;
    return getCurrentItem();
  }
  return null;
}

// 🔁 Move to previous item
export function moveToPrevious() {
  if (currentPreviewIndex > 0) {
    currentPreviewIndex--;
    return getCurrentItem();
  }
  return null;
}

// 🧹 Reset state
export function resetPreviewState() {
  previewItems = [];
  currentPreviewIndex = -1;
}

// 🔍 Check if there's a next item
export function hasNext() {
  return currentPreviewIndex < previewItems.length - 1;
}

// 🔍 Check if there's a previous item
export function hasPrevious() {
  return currentPreviewIndex > 0;
}

export function moveToNextPreviewable(dryRun = false) {
  let tempIndex = currentPreviewIndex;
  while (tempIndex < previewItems.length - 1) {
    tempIndex++;
    const item = previewItems[tempIndex];
    if (item && item.type !== 'folder') {
      if (dryRun) return item;
      currentPreviewIndex = tempIndex;
      return item;
    }
  }
  return null;
}

export function moveToPreviousPreviewable(dryRun = false) {
  let tempIndex = currentPreviewIndex;
  while (tempIndex > 0) {
    tempIndex--;
    const item = previewItems[tempIndex];
    if (item && item.type !== 'folder') {
      if (dryRun) return item;
      currentPreviewIndex = tempIndex;
      return item;
    }
  }
  return null;
}
