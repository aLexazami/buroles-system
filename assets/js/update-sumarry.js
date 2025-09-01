document.addEventListener('DOMContentLoaded', function () {
  function safeUpdate(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  }

  function updateCounts() {
    fetch('/controllers/get-counts.php')
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
      })
      .then(data => {
        // Customer Type Counts
        safeUpdate('count-business', data.Business || 0);
        safeUpdate('count-citizen', data.Citizen || 0);
        safeUpdate('count-government', data.Government || 0);

        // Age Group Counts
        safeUpdate('age-under-19', data['under-19'] || 0);
        safeUpdate('age-20-34', data['20-34'] || 0);
        safeUpdate('age-35-49', data['35-49'] || 0);
        safeUpdate('age-50-64', data['50-64'] || 0);
        safeUpdate('age-65-up', data['65-up'] || 0);

        //Citizen Charter Awareness Counts
        safeUpdate('awareness-yes', data.yes || 0);
        safeUpdate('awareness-no', data.no || 0);

        // CC1
        safeUpdate('cc1-1', data['cc1-1'] || 0);
        safeUpdate('cc1-2', data['cc1-2'] || 0);
        safeUpdate('cc1-3', data['cc1-3'] || 0);
        safeUpdate('cc1-4', data['cc1-4'] || 0);

        // CC2
        safeUpdate('cc2-1', data['cc2-1'] || 0);
        safeUpdate('cc2-2', data['cc2-2'] || 0);
        safeUpdate('cc2-3', data['cc2-3'] || 0);
        safeUpdate('cc2-4', data['cc2-4'] || 0);
        safeUpdate('cc2-5', data['cc2-5'] || 0);

        // CC3
        safeUpdate('cc3-1', data['cc3-1'] || 0);
        safeUpdate('cc3-2', data['cc3-2'] || 0);
        safeUpdate('cc3-3', data['cc3-3'] || 0);
        safeUpdate('cc3-4', data['cc3-4'] || 0);

        // SQD1
        safeUpdate('sqd1-5', data['sqd1-5'] || 0);
        safeUpdate('sqd1-4', data['sqd1-4'] || 0);
        safeUpdate('sqd1-3', data['sqd1-3'] || 0);
        safeUpdate('sqd1-2', data['sqd1-2'] || 0);
        safeUpdate('sqd1-1', data['sqd1-1'] || 0);
        safeUpdate('sqd1-na', data['sqd1-na'] || 0);

        // SQD2
        safeUpdate('sqd2-5', data['sqd2-5'] || 0);
        safeUpdate('sqd2-4', data['sqd2-4'] || 0);
        safeUpdate('sqd2-3', data['sqd2-3'] || 0);
        safeUpdate('sqd2-2', data['sqd2-2'] || 0);
        safeUpdate('sqd2-1', data['sqd2-1'] || 0);
        safeUpdate('sqd2-na', data['sqd2-na'] || 0);

        // SQD3
        safeUpdate('sqd3-5', data['sqd3-5'] || 0);
        safeUpdate('sqd3-4', data['sqd3-4'] || 0);
        safeUpdate('sqd3-3', data['sqd3-3'] || 0);
        safeUpdate('sqd3-2', data['sqd3-2'] || 0);
        safeUpdate('sqd3-1', data['sqd3-1'] || 0);
        safeUpdate('sqd3-na', data['sqd3-na'] || 0);

        // SQD4
        safeUpdate('sqd4-5', data['sqd4-5'] || 0);
        safeUpdate('sqd4-4', data['sqd4-4'] || 0);
        safeUpdate('sqd4-3', data['sqd4-3'] || 0);
        safeUpdate('sqd4-2', data['sqd4-2'] || 0);
        safeUpdate('sqd4-1', data['sqd4-1'] || 0);
        safeUpdate('sqd4-na', data['sqd4-na'] || 0);

        // SQD5
        safeUpdate('sqd5-5', data['sqd5-5'] || 0);
        safeUpdate('sqd5-4', data['sqd5-4'] || 0);
        safeUpdate('sqd5-3', data['sqd5-3'] || 0);
        safeUpdate('sqd5-2', data['sqd5-2'] || 0);
        safeUpdate('sqd5-1', data['sqd5-1'] || 0);
        safeUpdate('sqd5-na', data['sqd5-na'] || 0);

        // SQD6
        safeUpdate('sqd6-5', data['sqd6-5'] || 0);
        safeUpdate('sqd6-4', data['sqd6-4'] || 0);
        safeUpdate('sqd6-3', data['sqd6-3'] || 0);
        safeUpdate('sqd6-2', data['sqd6-2'] || 0);
        safeUpdate('sqd6-1', data['sqd6-1'] || 0);
        safeUpdate('sqd6-na', data['sqd6-na'] || 0);

        // SQD7
        safeUpdate('sqd7-5', data['sqd7-5'] || 0);
        safeUpdate('sqd7-4', data['sqd7-4'] || 0);
        safeUpdate('sqd7-3', data['sqd7-3'] || 0);
        safeUpdate('sqd7-2', data['sqd7-2'] || 0);
        safeUpdate('sqd7-1', data['sqd7-1'] || 0);
        safeUpdate('sqd7-na', data['sqd7-na'] || 0);

        // SQD8
        safeUpdate('sqd8-5', data['sqd8-5'] || 0);
        safeUpdate('sqd8-4', data['sqd8-4'] || 0);
        safeUpdate('sqd8-3', data['sqd8-3'] || 0);
        safeUpdate('sqd8-2', data['sqd8-2'] || 0);
        safeUpdate('sqd8-1', data['sqd8-1'] || 0);
        safeUpdate('sqd8-na', data['sqd8-na'] || 0);
        
        //Service Availed
        safeUpdate('service-1', data['service-1'] || 0);
        safeUpdate('service-2', data['service-2'] || 0);
        safeUpdate('service-3', data['service-3'] || 0);
        safeUpdate('service-4', data['service-4'] || 0);
        safeUpdate('service-5', data['service-5'] || 0);
        safeUpdate('service-6', data['service-6'] || 0);
        safeUpdate('service-7', data['service-7'] || 0);
        safeUpdate('service-8', data['service-8'] || 0);
        safeUpdate('service-9', data['service-9'] || 0);
        safeUpdate('service-10', data['service-10'] || 0);
        safeUpdate('service-11', data['service-11'] || 0);
        safeUpdate('service-12', data['service-12'] || 0);
        safeUpdate('service-13', data['service-13'] || 0);
        safeUpdate('service-14', data['service-14'] || 0);
        safeUpdate('service-15', data['service-15'] || 0);
        safeUpdate('service-16', data['service-16'] || 0);
        safeUpdate('service-17', data['service-17'] || 0);
        safeUpdate('service-18', data['service-18'] || 0);
        
      })
      .catch(error => {
        console.error('Counts fetch failed:', error);
      });
  }

  function updateServiceCounts() {
    fetch('/controllers/get-services-counts.php')
      .then(response => response.json())
      .then(data => {
        Object.entries(data).forEach(([key, value]) => {
          safeUpdate(key, value);
        });
      })
      .catch(error => {
        console.error('Failed to load service counts:', error);
      });
  }

  function updateRegionCounts() {
  fetch('/controllers/get-region-counts.php')
    .then(response => response.json())
    .then(data => {
      Object.entries(data).forEach(([slug, count]) => {
        safeUpdate(slug, count);
      });
    })
    .catch(error => {
      console.error('Failed to load region counts:', error);
    });
}


  // Initial loads
  updateCounts();
  updateServiceCounts();
  updateRegionCounts();


  // Periodic refresh
  setInterval(() => {
    updateCounts();
    updateServiceCounts();
    updateRegionCounts();
  }, 10000);

});