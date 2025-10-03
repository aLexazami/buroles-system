function updateServiceOptions() {
  const customerType = document.getElementById('customer_type').value;
  const serviceAvailed = document.getElementById('service_availed');

  // Clear previous selection from sessionStorage
  sessionStorage.removeItem('feedback_service_availed');

  // Reset dropdown options
  while (serviceAvailed.options.length > 0) {
    serviceAvailed.remove(0);
  }

  // Add default placeholder
  const defaultOption = document.createElement('option');
  defaultOption.value = '';
  defaultOption.disabled = true;
  defaultOption.selected = true;
  defaultOption.textContent = 'Service Availed';
  serviceAvailed.appendChild(defaultOption);

  // Fetch new service options
  fetch(`/controllers/get-services.php?type=${encodeURIComponent(customerType)}`)
    .then(response => response.json())
    .then(data => {
      console.log('Fetched services:', data);
      if (Array.isArray(data)) {
        const savedServiceId = sessionStorage.getItem('feedback_service_availed');
        data.forEach(service => {
          const option = document.createElement('option');
          option.value = service.id;
          option.textContent = service.name;

          if (savedServiceId && service.id === savedServiceId) {
            option.selected = true;
          }

          serviceAvailed.appendChild(option);
        });
      } else {
        console.warn('No services returned:', data);
      }
    })
    .catch(error => {
      console.error('Failed to load services:', error);
    });
}