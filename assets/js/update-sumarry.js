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

        // CC1 – Awareness Summary (Nominal)
        const cc1_1 = data['cc1-1'] || 0;
        const cc1_2 = data['cc1-2'] || 0;
        const cc1_3 = data['cc1-3'] || 0;
        const cc1_4 = data['cc1-4'] || 0;

        const cc1Total = cc1_1 + cc1_2 + cc1_3 + cc1_4;
        const cc1Aware = cc1_1 + cc1_2 + cc1_3;

        const cc1AwarePct = cc1Total > 0 ? ((cc1Aware / cc1Total) * 100).toFixed(1) : '—';
        const cc1FullyAwarePct = cc1Total > 0 ? ((cc1_1 / cc1Total) * 100).toFixed(1) : '—';
        const cc1UnawarePct = cc1Total > 0 ? ((cc1_4 / cc1Total) * 100).toFixed(1) : '—';

        safeUpdate('cc1-unaware-pct', `${cc1UnawarePct}%`);
        safeUpdate('cc1-awareness-pct', `${cc1AwarePct}%`);
        safeUpdate('cc1-fully-aware-pct', `${cc1FullyAwarePct}%`);

        // CC2 – Visibility
        const cc2Weights = { '1': 4, '2': 3, '3': 2, '4': 1 };
        let cc2Total = 0;           // includes N/A
        let cc2ValidTotal = 0;      // excludes N/A
        let cc2Weighted = 0;

        ['1', '2', '3', '4', 'na'].forEach(key => {
          const count = data[`cc2-${key}`] || 0;
          cc2Total += count;
          if (key !== 'na') { // exclude N/A
            cc2ValidTotal += count;
            cc2Weighted += count * cc2Weights[key];
          }
        });

        const cc2Mean = cc2ValidTotal > 0 ? (cc2Weighted / cc2ValidTotal).toFixed(2) : '—';
        let cc2Interpretation = '—';
        if (cc2ValidTotal > 0) {
          const mean = parseFloat(cc2Mean);
          if (mean >= 3.5) cc2Interpretation = 'Highly Visible';
          else if (mean >= 2.5) cc2Interpretation = 'Moderately Visible';
          else cc2Interpretation = 'Poorly Visible';
        }

        const cc2Row = document.getElementById('cc2-row');
        if (cc2Row) {
          cc2Row.classList.remove('bg-green-50', 'bg-yellow-50', 'bg-red-50');
          if (cc2Interpretation === 'Highly Visible') cc2Row.classList.add('bg-green-50');
          else if (cc2Interpretation === 'Moderately Visible') cc2Row.classList.add('bg-yellow-50');
          else if (cc2Interpretation === 'Poorly Visible') cc2Row.classList.add('bg-red-50');
        }

        safeUpdate('cc2-mean', cc2Mean);
        safeUpdate('cc2-interpretation', cc2Interpretation);
        safeUpdate('cc2-total', cc2Total);
        safeUpdate('cc2-total-paragraph', cc2Total);
        safeUpdate('cc2-valid-total', cc2ValidTotal);

        // CC3 – Helpfulness
        const cc3Weights = { '1': 3, '2': 2, '3': 1 };
        let cc3Total = 0;           // includes N/A
        let cc3ValidTotal = 0;      // excludes N/A
        let cc3Weighted = 0;

        ['1', '2', '3', 'na'].forEach(key => {
          const count = data[`cc3-${key}`] || 0;
          cc3Total += count;
          if (key !== 'na') {
            cc3ValidTotal += count;
            cc3Weighted += count * cc3Weights[key];
          }
        });

        const cc3Mean = cc3ValidTotal > 0 ? (cc3Weighted / cc3ValidTotal).toFixed(2) : '—';
        let cc3Interpretation = '—';
        if (cc3ValidTotal > 0) {
          const mean = parseFloat(cc3Mean);
          if (mean >= 2.5) cc3Interpretation = 'Very Helpful';
          else if (mean >= 1.5) cc3Interpretation = 'Somewhat Helpful';
          else cc3Interpretation = 'Not Helpful';
        }

        // Apply row color
        const cc3Row = document.getElementById('cc3-row');
        if (cc3Row) {
          cc3Row.classList.remove('bg-green-50', 'bg-yellow-50', 'bg-red-50');
          if (cc3Interpretation === 'Very Helpful') cc3Row.classList.add('bg-green-50');
          else if (cc3Interpretation === 'Somewhat Helpful') cc3Row.classList.add('bg-yellow-50');
          else if (cc3Interpretation === 'Not Helpful') cc3Row.classList.add('bg-red-50');
        }

        // Push values to UI
        safeUpdate('cc3-mean', cc3Mean);
        safeUpdate('cc3-interpretation', cc3Interpretation);
        safeUpdate('cc3-total', cc3Total);               // table cell
        safeUpdate('cc3-total-paragraph', cc3Total);     // paragraph (if added)
        safeUpdate('cc3-valid-total', cc3ValidTotal);    // paragraph (if added)

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