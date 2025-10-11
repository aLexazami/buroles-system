export function renderFlash(type, message) {
  const styleMap = {
    success: {
      class: 'bg-green-100 border-green-300 text-green-800',
      button: 'text-green-700 hover:text-green-900',
      icon: '/assets/img/success-icon.png'
    },
    error: {
      class: 'bg-red-100 border-red-300 text-red-800',
      button: 'text-red-700 hover:text-red-900',
      icon: '/assets/img/error-icon.png'
    },
    warning: {
      class: 'bg-yellow-100 border-yellow-300 text-yellow-800',
      button: 'text-yellow-700 hover:text-yellow-900',
      icon: '/assets/img/warning-icon.png'
    }
  };

  const style = styleMap[type] || {
    class: 'bg-gray-100 border-gray-300 text-gray-800',
    button: 'text-gray-700 hover:text-gray-900',
    icon: '/assets/img/info-icon.png'
  };

  const wrapper = document.createElement('div');
  wrapper.setAttribute('role', 'alert');
  wrapper.setAttribute('aria-live', 'assertive');
  wrapper.setAttribute('data-alert', '');
  wrapper.className = `fixed top-4 sm:top-15 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-sm sm:max-w-md px-4 py-3 border-2 rounded shadow-lg ${style.class} flex items-center justify-between transition-all duration-300`;

  const content = document.createElement('div');
  content.className = 'flex items-center space-x-3';

  const icon = document.createElement('img');
  icon.src = style.icon;
  icon.alt = `${type} icon`;
  icon.className = 'w-4 sm:w-5 h-4 sm:h-5';

  const text = document.createElement('span');
  text.className = 'text-xs sm:text-sm font-medium text-gray-800';
  text.textContent = message;

  content.appendChild(icon);
  content.appendChild(text);
  wrapper.appendChild(content);

  document.body.appendChild(wrapper);

  setTimeout(() => {
    wrapper.classList.add('opacity-0');
    setTimeout(() => wrapper.remove(), 300);
  }, 3000);
}