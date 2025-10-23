document.addEventListener('DOMContentLoaded', function () {
  function safeUpdate(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  }

  function interpretMean(mean) {
    if (mean >= 4.21) return 'Very Satisfied';
    if (mean >= 3.41) return 'Satisfied';
    if (mean >= 2.61) return 'Neutral';
    if (mean >= 1.81) return 'Dissatisfied';
    if (mean >= 1.0) return 'Very Dissatisfied';
    return '—';
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

        const totalCustomers =
          (data.Business || 0) +
          (data.Citizen || 0) +
          (data.Government || 0);
        safeUpdate('count-total-customers', totalCustomers);

        // Age Group Counts
        safeUpdate('age-under-19', data['under-19'] || 0);
        safeUpdate('age-20-34', data['20-34'] || 0);
        safeUpdate('age-35-49', data['35-49'] || 0);
        safeUpdate('age-50-64', data['50-64'] || 0);
        safeUpdate('age-65-up', data['65-up'] || 0);

        // Citizen Charter Awareness Counts
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

        // SQD1–SQD8 with Totals, Weighted Mean, Interpretation, and Row Flags
        const ratingTotals = { '5': 0, '4': 0, '3': 0, '2': 0, '1': 0, 'na': 0 };

        for (let i = 1; i <= 8; i++) {
          let itemTotal = 0;
          const counts = { '5': 0, '4': 0, '3': 0, '2': 0, '1': 0 };

          ['5', '4', '3', '2', '1', 'na'].forEach(score => {
            const key = `sqd${i}-${score}`;
            const count = data[key] || 0;
            itemTotal += count;
            ratingTotals[score] += count;
            safeUpdate(key, count);
            if (score !== 'na') counts[score] = count;
          });

          safeUpdate(`sqd${i}-total`, itemTotal);

          const validTotal = Object.values(counts).reduce((sum, val) => sum + val, 0);
          const weightedSum =
            (5 * counts['5']) +
            (4 * counts['4']) +
            (3 * counts['3']) +
            (2 * counts['2']) +
            (1 * counts['1']);
          const mean = validTotal > 0 ? (weightedSum / validTotal).toFixed(2) : '—';
          const interpretation = validTotal > 0 ? interpretMean(mean) : '—';

          safeUpdate(`sqd${i}-mean`, mean);
          safeUpdate(`sqd${i}-interpretation`, interpretation);

          // Apply color-coded row flag
          const rowEl = document.getElementById(`sqd${i}-row`);
          if (rowEl) {
            rowEl.classList.remove(
              'bg-green-50',
              'bg-lime-50',
              'bg-yellow-50',
              'bg-orange-50',
              'bg-red-50'
            );
            if (mean !== '—') {
              const numericMean = parseFloat(mean);
              if (numericMean >= 4.21) rowEl.classList.add('bg-green-50');
              else if (numericMean >= 3.41) rowEl.classList.add('bg-lime-50');
              else if (numericMean >= 2.61) rowEl.classList.add('bg-yellow-50');
              else if (numericMean >= 1.81) rowEl.classList.add('bg-orange-50');
              else rowEl.classList.add('bg-red-50');
            }
          }
        }

        // Update global rating totals
        Object.entries(ratingTotals).forEach(([score, total]) => {
          safeUpdate(`sqd-total-${score}`, total);
        });

        // Compute overall weighted mean across all SQDs
        let totalWeightedSum = 0;
        let totalValidResponses = 0;

        for (let i = 1; i <= 8; i++) {
          const counts = {
            '5': data[`sqd${i}-5`] || 0,
            '4': data[`sqd${i}-4`] || 0,
            '3': data[`sqd${i}-3`] || 0,
            '2': data[`sqd${i}-2`] || 0,
            '1': data[`sqd${i}-1`] || 0
          };
          const validTotal = Object.values(counts).reduce((sum, val) => sum + val, 0);
          const weightedSum =
            (5 * counts['5']) +
            (4 * counts['4']) +
            (3 * counts['3']) +
            (2 * counts['2']) +
            (1 * counts['1']);

          totalWeightedSum += weightedSum;
          totalValidResponses += validTotal;
        }

        const overallMean = totalValidResponses > 0
          ? (totalWeightedSum / totalValidResponses).toFixed(2)
          : '—';
        const overallInterpretation = totalValidResponses > 0
          ? interpretMean(overallMean)
          : '—';

        safeUpdate('sqd-overall-mean', overallMean);
        safeUpdate('sqd-overall-interpretation', overallInterpretation);
        safeUpdate('sqd-overall-total', totalValidResponses);

        // Service Availed
        for (let i = 1; i <= 18; i++) {
          safeUpdate(`service-${i}`, data[`service-${i}`] || 0);
        }
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