function updateServiceOptions() {
  const customerType = document.getElementById('customer_type').value;
  const serviceAvailed = document.getElementById('service_availed');
  serviceAvailed.innerHTML = '<option value="" disabled selected>Service Availed</option>';

  fetch(`/controllers/get-services.php?type=${encodeURIComponent(customerType)}`)
    .then(response => response.json())
    .then(data => {
      if (Array.isArray(data)) {
        data.forEach(service => {
          const option = document.createElement('option');
          option.value = service.id; // This is the service ID from your database
          option.textContent = service.name; // This is the readable name like "Enrollment (Online)"
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