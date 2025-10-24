let currentFetchId = 0;

export function updateServiceOptions(customerType) {
  const serviceAvailed = document.getElementById("service_availed");
  if (!serviceAvailed) return;

  // Clear previous selection
  sessionStorage.removeItem("feedback_service_availed");

  // Reset dropdown
  serviceAvailed.innerHTML = '<option value="" disabled selected>Service Availed</option>';

  // Track fetch instance to prevent race conditions
  const fetchId = ++currentFetchId;

  fetch(`/controllers/get-services.php?type=${encodeURIComponent(customerType)}`)
    .then(response => response.json())
    .then(data => {
      if (fetchId !== currentFetchId) return; // Ignore outdated fetches

      if (Array.isArray(data)) {
        const savedServiceId = sessionStorage.getItem("feedback_service_availed");

        data.forEach(service => {
          const option = document.createElement("option");
          option.value = service.id;
          option.textContent = service.name;

          if (savedServiceId && service.id === savedServiceId) {
            option.selected = true;
          }

          serviceAvailed.appendChild(option);
        });
      } else {
        console.warn("No services returned:", data);
      }
    })
    .catch(error => {
      console.error("Failed to load services:", error);
    });
}