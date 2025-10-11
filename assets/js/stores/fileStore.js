let currentItems = [];

export function setItems(items) {
  currentItems = items;
}

export function getItems() {
  return currentItems;
}

export function addItem(newItem) {
  currentItems.push(newItem);
}

export function insertItemSorted(newItem) {
  if (currentItems.find(item => item.id === newItem.id)) return;
  currentItems.push(newItem);
  currentItems = sortItems(currentItems);
}


export function sortItems(items) {
  return [...items].sort((a, b) => {
    if (a.type === 'folder' && b.type !== 'folder') return -1;
    if (a.type !== 'folder' && b.type === 'folder') return 1;
    return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
  });
}