export function initDeleteButtons(currentPath) {
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.dataset.name;
      const type = btn.dataset.type;
      if (!confirm(`Are you sure you want to delete this ${type}?`)) return;

      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/controllers/delete-item.php';

      ['type', 'name', 'path'].forEach(field => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = field;
        input.value = field === 'type' ? type : field === 'name' ? name : currentPath;
        form.appendChild(input);
      });

      document.body.appendChild(form);
      form.submit();
    });
  });
}