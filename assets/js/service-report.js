const toggleServiceDropdownBtn = document.getElementById('toggleServiceDropdownBtn');
const renderedServiceList = document.getElementById('renderedServiceList');
const container = document.getElementById('service-report-container');
const fromDate = document.getElementById('fromDate');
const toDate = document.getElementById('toDate');

let selectedServiceId = null;
let selectedServiceName = null;

const defaultServiceId = '5';
const defaultYear = new Date().getFullYear();
fromDate.value = `${defaultYear}-01-01`;
toDate.value = `${defaultYear}-12-31`;

// ✅ Toggle dropdown visibility
toggleServiceDropdownBtn.addEventListener('click', () => {
  const isHidden = renderedServiceList.classList.contains('hidden');
  renderedServiceList.classList.toggle('hidden', !isHidden);
  renderedServiceList.classList.toggle('block', isHidden);

  if (isHidden && renderedServiceList.children.length === 0) {
    renderServiceList();
  }
});

// ✅ Render service list and preload default
function renderServiceList() {
  fetch('/controllers/get-services-report.php')
    .then(res => res.json())
    .then(data => {
      if (!data.options || data.options.length === 0) {
        renderedServiceList.innerHTML = '<div class="p-4 text-gray-500 italic">No services available.</div>';
        return;
      }

      renderedServiceList.innerHTML = ''; // Clear previous items

      const defaultService = data.options.find(opt => opt.id == defaultServiceId);
      selectedServiceId = defaultServiceId;
      selectedServiceName = defaultService?.name || 'Default Service';
      loadReport();

      data.options.forEach(option => {
        const item = document.createElement('div');
        item.textContent = option.name;
        item.className = 'px-4 py-2 hover:bg-emerald-100 cursor-pointer border-b border-gray-100';
        item.dataset.id = option.id;

        if (option.id === defaultServiceId) {
          item.classList.add('bg-emerald-100');
        }

        item.addEventListener('click', () => {
          selectedServiceId = option.id;
          selectedServiceName = option.name;
          renderedServiceList.classList.add('hidden');
          renderedServiceList.classList.remove('block');
          loadReport();
        });

        renderedServiceList.appendChild(item);
      });
    });
}

// ✅ Load report
function loadReport() {
  const serviceId = selectedServiceId;
  const from = fromDate.value;
  const to = toDate.value;

  if (!serviceId || !from || !to) return;

  document.getElementById('reportServiceName').textContent = 'Loading...';
  document.getElementById('reportYearRange').textContent = '';
  document.getElementById('respondentSection').innerHTML = '';
  document.getElementById('demographicsSection').innerHTML = '';
  document.getElementById('charterSection').innerHTML = '';
  document.getElementById('sqdSection').innerHTML = '';

  fetch(`/controllers/get-feedback-data.php?service_id=${serviceId}&from=${from}&to=${to}`)
    .then(res => res.json())
    .then(data => {
      updateReportData(data, selectedServiceName, `${from} to ${to}`);
    })
    .catch(() => {
      document.getElementById('reportServiceName').textContent = 'Failed to load report';
      document.getElementById('reportYearRange').textContent = '';
    });
}

// ✅ Export to PDF
function setupExportButton() {
  const exportBtn = document.getElementById('exportPdfBtn');
  if (!exportBtn) return;

  exportBtn.addEventListener('click', () => {
    if (!selectedServiceId || !fromDate.value) {
      alert('Please select a service and date range before exporting.');
      return;
    }

    const year = new Date(fromDate.value).getFullYear();
    const exportUrl = `/controllers/export-pdf.php?service_id=${selectedServiceId}&from=${fromDate.value}&to=${toDate.value}`;
    window.open(exportUrl, '_blank');
  });
}

// ✅ Update report content
function updateReportData(data, serviceName, yearRange) {
  const flatSQD = flattenSQDBreakdowns(data.sqd_breakdowns);

  document.getElementById('reportServiceName').textContent = serviceName;
  document.getElementById('reportYearRange').textContent = yearRange;

  document.getElementById('respondentSection').innerHTML = renderRespondents(data, yearRange);
  document.getElementById('demographicsSection').innerHTML = renderDemographics(data);
  document.getElementById('charterSection').innerHTML = renderCharter(data);
  document.getElementById('charterSummarySection').innerHTML = renderCharterSummary(data);
  document.getElementById('sqdSection').innerHTML = `
  <div class="p-4 shadow-lg col-span-3 bg-white rounded-lg mt-4">
    ${renderSQDMatrix(data)}
    ${renderSQDWeightedSummaryFromFlat(flatSQD)}
  </div>
`;
}

function renderRespondents(data, year) {
  return `
    <div class="p-4 shadow-lg bg-white rounded-lg">
      <h1 class="text-lg text-center text-emerald-800 font-bold">Respondents Total</h1>
      <div class="mt-6 divide-y divide-gray-200">
        <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
          <span class="font-medium">Total Respondents:</span>
          <span class="text-red-500 font-bold text-right">${data.respondents}</span>
        </div>
      </div>
    </div>
  `;
}

function renderDemographics(data) {
  return `
    <div class="p-4 shadow-lg bg-white rounded-lg">
      <h1 class="text-lg text-center text-emerald-800 font-bold">Demographic Profile</h1>
      <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        ${renderDemographicGroup('A. Age', data.age)}
        ${renderDemographicGroup('B. Sex', { Female: data.female, Male: data.male })}
        ${renderDemographicGroup('C. Customer Type', data.customer_types)}
      </div>
    </div>
  `;
}

const ageLabels = {
  '19_or_lower': '19 or Lower',
  '20_34': '20–34',
  '35_49': '35–49',
  '50_64': '50–64',
  '65_or_higher': '65 or Higher'
};

// ✅ Render helpers
function renderDemographicGroup(title, values) {
  return `
    <div class="bg-gray-50 p-4 rounded-lg shadow-inner">
      <h2 class="text-center text-emerald-700 font-semibold mb-2">${title}</h2>
      <div class="divide-y divide-gray-100">
      ${Object.entries(values).map(([label, count]) => `
      <div class="grid grid-cols-2 py-2 hover:bg-gray-50">
        <span>${ageLabels[label] || label}</span>
        <span class="text-red-500 font-bold text-right">${count}</span>
      </div>
    `).join('')}
      </div>
    </div>
  `;
}

const charterLegends = {
  cc1: {
    title: 'CC1',
    description: 'Which of the following best describes your awareness of a Citizen’s Charter?',
    items: [
      'I know what a Citizen’s Charter is and I saw this office’s Citizen’s Charter.',
      'I know what a Citizen’s Charter is but I did not see this office’s Citizen’s Charter.',
      'I learned of the Citizen’s Charter only when I saw this office’s Citizen’s Charter.',
      'I do not know what a Citizen’s Charter is and I did not see one in this office.'
    ]
  },
  cc2: {
    title: 'CC2',
    description: 'If aware of Citizen’s Charter (answered 1–3 in CC1), would you say that the CC of this office was …?',
    items: [
      'Easy to see',
      'Somewhat easy to see',
      'Difficult to see',
      'Not visible at all',
      'N/A'
    ]
  },
  cc3: {
    title: 'CC3',
    description: 'If aware of Citizen’s Charter (answered 1–3 in CC1), how much did the CC help you in your transaction?',
    items: [
      'Helped very much',
      'Somewhat helped',
      'Did not help',
      'N/A'
    ]
  }
};

function renderCharter(data) {
  return `
    <div class="p-4 shadow-lg bg-white rounded-lg">
      <h1 class="text-lg text-center text-emerald-800 font-bold">Citizen's Charter Responses</h1>
      <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        ${renderCharterGroup('cc1', data.charter.cc1)}
        ${renderCharterGroup('cc2', data.charter.cc2)}
        ${renderCharterGroup('cc3', data.charter.cc3)}
      </div>
    </div>
  `;
}

function renderCharterGroup(key, values) {
  const legend = charterLegends[key];
  const legendId = `${key}-legend`;

  return `
    <div class="divide-y divide-gray-200">
      <!-- Header with Toggle -->
      <div class="text-center font-bold pb-3 cursor-pointer" data-toggle-legend="${legendId}" aria-expanded="false">
        <h1 class="text-emerald-800 hover:underline">${legend.title}</h1>
      </div>

      <!-- Legend -->
      <div id="${legendId}" class="bg-gray-100 rounded-lg text-sm text-left overflow-hidden transition-all duration-300 ease-in-out opacity-0 max-h-0 pointer-events-none">
        <p><strong>${legend.title}: ${legend.description}</strong></p>
        <ul class="list-disc pl-5 mt-2 space-y-1">
          ${legend.items.map((item, i) => `<li><strong>${i + 1}</strong> – ${item}</li>`).join('')}
        </ul>
      </div>

      <!-- Breakdown -->
      ${Object.entries(values).map(([score, count]) => `
        <div class="grid grid-cols-2 py-2 hover:bg-gray-100">
          <span class="font-medium">${score}. ${legend.items[score - 1] || 'N/A'}:</span>
          <span class="text-red-400 font-bold text-right">${count}</span>
        </div>
      `).join('')}
    </div>
  `;
}

document.addEventListener('click', (e) => {
  const toggleTarget = e.target.closest('[data-toggle-legend]');
  if (!toggleTarget) return;

  const legendId = toggleTarget.getAttribute('data-toggle-legend');
  const el = document.getElementById(legendId);
  if (!el) return;

  const isOpen = el.classList.contains('max-h-0');

  // Toggle animation classes
  if (isOpen) {
    el.classList.remove('max-h-0', 'opacity-0', 'pointer-events-none');
    el.classList.add('max-h-[500px]', 'opacity-100', 'pointer-events-auto');
  } else {
    el.classList.add('max-h-0', 'opacity-0', 'pointer-events-none');
    el.classList.remove('max-h-[500px]', 'opacity-100', 'pointer-events-auto');
  }

  // ✅ Corrected aria-expanded to reflect new state
  toggleTarget.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
});

function renderCharterSummary(data) {
  // CC1 Awareness Summary
  const cc1_1 = data.charter?.cc1?.[1] || 0;
  const cc1_2 = data.charter?.cc1?.[2] || 0;
  const cc1_3 = data.charter?.cc1?.[3] || 0;
  const cc1_4 = data.charter?.cc1?.[4] || 0;

  const cc1Total = cc1_1 + cc1_2 + cc1_3 + cc1_4;
  const cc1Aware = cc1_1 + cc1_2 + cc1_3;

  const cc1AwarePct = cc1Total > 0 ? ((cc1Aware / cc1Total) * 100).toFixed(1) : '—';
  const cc1FullyAwarePct = cc1Total > 0 ? ((cc1_1 / cc1Total) * 100).toFixed(1) : '—';
  const cc1UnawarePct = cc1Total > 0 ? ((cc1_4 / cc1Total) * 100).toFixed(1) : '—';

  // CC2 Visibility Weighted Mean
  const cc2Weights = { '1': 4, '2': 3, '3': 2, '4': 1 };
  let cc2Total = 0, cc2ValidTotal = 0, cc2Weighted = 0;

  ['1', '2', '3', '4', '5'].forEach(key => {
    const count = parseInt(data.charter?.cc2?.[key], 10) || 0;
    cc2Total += count;
    if (cc2Weights[key]) {
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

  // CC3 Helpfulness Weighted Mean
  const cc3Weights = { '1': 3, '2': 2, '3': 1 };
  let cc3Total = 0, cc3ValidTotal = 0, cc3Weighted = 0;

  ['1', '2', '3', '4'].forEach(key => {
    const count = parseInt(data.charter?.cc3?.[key], 10) || 0;
    cc3Total += count;
    if (cc3Weights[key]) {
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

  // ✅ Now inject all computed values into your HTML template
  return `
    <div class="bg-white rounded-lg p-4 shadow mt-6">
      <h2 class="text-lg font-bold text-emerald-800 text-center mb-4">
        Citizen Charter Awareness Weighted Mean and Interpretation
      </h2>

      <div class="mb-8 text-sm text-gray-700 bg-gray-50 rounded-lg p-4">
        <h3 class="text-center text-emerald-700 font-semibold mb-2">CC1 Awareness Summary</h3>
        <ul class="list-disc ml-6">
          <li>${cc1AwarePct}% of respondents are aware of the Citizen’s Charter (responses 1–3).</li>
          <li>${cc1FullyAwarePct}% are fully aware and have seen it (response 1).</li>
          <li>${cc1UnawarePct}% are not aware at all (response 4).</li>
        </ul>
      </div>

      <div class="mt-4 text-sm text-gray-600 bg-gray-50 rounded-lg p-4">
        <details>
          <summary class="cursor-pointer font-semibold text-emerald-700">How CC1 Awareness Is Interpreted</summary>
          <div class="mt-2">
                <p>
                  CC1 (Awareness) is a nominal metric based on four response categories:
                </p>
                <ul class="list-disc ml-6 mt-2">
                  <li><strong>Response 1:</strong> Fully aware and have seen the Citizen’s Charter</li>
                  <li><strong>Response 2:</strong> Aware but have not seen it</li>
                  <li><strong>Response 3:</strong> Heard of it but unsure</li>
                  <li><strong>Response 4:</strong> Not aware at all</li>
                </ul>
                <p class="mt-2">
                  The following percentages are computed based on total responses:
                </p>
                <ul class="list-disc ml-6 mt-2">
                  <li><strong>Awareness Rate:</strong> Responses 1–3 combined</li>
                  <li><strong>Fully Aware:</strong> Response 1 only</li>
                  <li><strong>Unaware:</strong> Response 4 only</li>
                </ul>
                <p class="mt-2">
                  These percentages help identify how familiar respondents are with the Citizen’s Charter, but no weighted scoring is applied.
                </p>
              </div>
        </details>
      </div>

      <div class="mt-8 mb-6 text-sm overflow-x-auto pb-5">
        <h3 class="text-center text-emerald-700 font-semibold mb-2">CC2 Visibility Summary</h3>
        <table class="min-w-full table-auto text-sm">
          <thead class="bg-emerald-100 text-emerald-800">
            <tr>
              <th class="px-4 py-2 text-left">Metric</th>
              <th class="px-4 py-2 text-center">Weighted Mean</th>
              <th class="px-4 py-2 text-center">Interpretation</th>
              <th class="px-4 py-2 text-center">Total Responses</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr class="${cc2Interpretation === 'Highly Visible' ? 'bg-green-50' : cc2Interpretation === 'Moderately Visible' ? 'bg-yellow-50' : cc2Interpretation === 'Poorly Visible' ? 'bg-red-50' : ''}">
              <td class="px-4 py-2">CC2 – Visibility</td>
              <td class="text-center font-bold text-emerald-700">${cc2Mean}</td>
              <td class="text-center italic text-gray-700">
                <span class="px-2 py-1 rounded font-semibold">${cc2Interpretation}</span>
              </td>
              <td class="text-center text-gray-800 font-semibold">${cc2Total}</td>
            </tr>
          </tbody>
        </table>
        <p class="text-sm text-gray-600 mt-2 text-center">
          <strong>Total responses:</strong> ${cc2Total}<br>
          <strong>Valid responses (scored):</strong> ${cc2ValidTotal}
        </p>
        <div class="mt-4 text-sm text-gray-600 bg-gray-50 rounded-lg p-4">
          <details>
            <summary class="cursor-pointer font-semibold text-emerald-700">How CC2 Weighted Mean Is Calculated</summary>
            <div class="mt-2">
              <p>For CC2 (Visibility), the weighted mean is computed using the formula:</p>
              <p class="font-mono bg-white p-2 rounded border mt-2">
                Weighted Mean = (4×Highly Visible + 3×Moderately Visible + 2×Slightly Visible + 1×Not Visible) ÷ Total Valid Responses
              </p>
              <p class="mt-2">N/A responses are excluded from the calculation. The interpretation is based on the following scale:</p>
              <ul class="list-disc ml-6 mt-2">
                <li>3.50 – 4.00: <strong>Highly Visible</strong></li>
                <li>2.50 – 3.49: <strong>Moderately Visible</strong></li>
                <li>1.00 – 2.49: <strong>Poorly Visible</strong></li>
              </ul>
            </div>
          </details>
        </div>
      </div>

      <div class="mt-8 text-sm overflow-x-auto pb-5">
        <h3 class="text-center text-emerald-700 font-semibold mb-2">CC3 Helpfulness Summary</h3>
        <table class="min-w-full table-auto text-sm">
          <thead class="bg-emerald-100 text-emerald-800">
            <tr>
              <th class="px-4 py-2 text-left">Metric</th>
              <th class="px-4 py-2 text-center">Weighted Mean</th>
              <th class="px-4 py-2 text-center">Interpretation</th>
              <th class="px-4 py-2 text-center">Total Responses</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr class="${cc3Interpretation === 'Very Helpful' ? 'bg-green-50' : cc3Interpretation === 'Somewhat Helpful' ? 'bg-yellow-50' : cc3Interpretation === 'Not Helpful' ? 'bg-red-50' : ''}">
              <td class="px-4 py-2">CC3 – Helpfulness</td>
              <td class="text-center font-bold text-emerald-700">${cc3Mean}</td>
              <td class="text-center italic text-gray-700">
                <span class="px-2 py-1 rounded font-semibold">${cc3Interpretation}</span>
              </td>
              <td class="text-center text-gray-800 font-semibold">${cc3Total}</td>
            </tr>
          </tbody>
        </table>
        <p class="text-sm text-gray-600 mt-2 text-center">
          <strong>Total responses:</strong> ${cc3Total}<br>
          <strong>Valid responses (scored):</strong> ${cc3ValidTotal}
        </p>
         <div class="mt-4 text-sm text-gray-600 bg-gray-50 rounded-lg p-4">
            <details>
              <summary class="cursor-pointer font-semibold text-emerald-700">How CC3 Weighted Mean Is Calculated</summary>
              <div class="mt-2">
                <p>
                  For CC3 (Helpfulness), the weighted mean is computed using the formula:
                </p>
                <p class="font-mono bg-white p-2 rounded border mt-2">
                  Weighted Mean = (3×Very Helpful + 2×Somewhat Helpful + 1×Not Helpful) ÷ Total Valid Responses
                </p>
                <p class="mt-2">
                  N/A responses are excluded from the calculation. The interpretation is based on the following scale:
                </p>
                <ul class="list-disc ml-6 mt-2">
                  <li>2.50 – 3.00: <strong>Very Helpful</strong></li>
                  <li>1.50 – 2.49: <strong>Somewhat Helpful</strong></li>
                  <li>1.00 – 1.49: <strong>Not Helpful</strong></li>
                </ul>
              </div>
            </details>
          </div>
      </div>
    </div>
  `;
}

const sqdLabels = [
  'SQD1 – Responsiveness',
  'SQD2 – Reliability',
  'SQD3 – Access and Facilities',
  'SQD4 – Communication',
  'SQD5 – Costs',
  'SQD6 – Integrity',
  'SQD7 – Assurance',
  'SQD8 – Outcome'
];

const sqdScores = ['5', '4', '3', '2', '1', 'na'];

function renderSQDMatrix(data) {
  const breakdownRows = sqdLabels.map((label, index) => {
    const key = `sqd${index + 1}`;
    const breakdown = data.sqd_breakdowns?.[key] || {};

    const cells = sqdScores.map(score => {
      const count = breakdown[score] ?? 0;
      return `<td class="text-center text-red-500 font-bold">${count}</td>`;
    }).join('');

    return `<tr>
      <td class="px-4 py-2 font-medium">${label}</td>
      ${cells}
    </tr>`;
  }).join('');

  return `
    <h1 class="text-lg text-center text-emerald-800 font-bold">Client Satisfaction Matrix</h1>
    <div class="overflow-x-auto bg-white rounded-lg shadow-lg p-4 mt-4">
      <table class="min-w-full table-auto border-collapse text-xs sm:text-sm">
        <thead class="bg-emerald-100 text-emerald-800">
          <tr>
            <th class="px-4 py-2 text-left">SQD Item</th>
            <th class="px-4 py-2 text-center">5<br>(Strongly Agree)</th>
            <th class="px-4 py-2 text-center">4<br>(Agree)</th>
            <th class="px-4 py-2 text-center">3<br>(Neutral)</th>
            <th class="px-4 py-2 text-center">2<br>(Disagree)</th>
            <th class="px-4 py-2 text-center">1<br>(Strongly Disagree)</th>
            <th class="px-4 py-2 text-center">N/A</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          ${breakdownRows}
        </tbody>
      </table>
    </div>
  `;
}

function interpretMean(mean) {
  const m = parseFloat(mean);
  if (m >= 4.21) return 'Very Satisfied';
  if (m >= 3.41) return 'Satisfied';
  if (m >= 2.61) return 'Neutral';
  if (m >= 1.81) return 'Dissatisfied';
  if (m >= 1.0) return 'Very Dissatisfied';
  return '—';
}

function getSQDRowColor(mean) {
  const m = parseFloat(mean);
  if (m >= 4.21) return 'bg-green-50';
  if (m >= 3.41) return 'bg-lime-50';
  if (m >= 2.61) return 'bg-yellow-50';
  if (m >= 1.81) return 'bg-orange-50';
  return 'bg-red-50';
}

function flattenSQDBreakdowns(nested) {
  const flat = {};
  Object.entries(nested || {}).forEach(([key, scores]) => {
    Object.entries(scores).forEach(([score, count]) => {
      flat[`${key}-${score}`] = count;
    });
  });
  return flat;
}

function renderSQDWeightedSummaryFromFlat(data) {
  const weights = { '5': 5, '4': 4, '3': 3, '2': 2, '1': 1 };
  let totalWeightedSum = 0;
  let totalValidResponses = 0;
  let totalResponsesIncludingNA = 0;

  const rows = [];

  for (let i = 1; i <= 8; i++) {
    const label = sqdLabels[i - 1];
    let itemTotal = 0;
    let validTotal = 0;
    let weightedSum = 0;

    for (const score of ['5', '4', '3', '2', '1', 'na']) {
      const key = `sqd${i}-${score}`;
      const count = parseInt(data[key], 10) || 0;
      itemTotal += count;
      if (weights[score]) {
        validTotal += count;
        weightedSum += count * weights[score];
      }
    }

    const mean = validTotal > 0 ? (weightedSum / validTotal).toFixed(2) : '—';
    const interpretation = validTotal > 0 ? interpretMean(mean) : '—';

    totalWeightedSum += weightedSum;
    totalValidResponses += validTotal;
    totalResponsesIncludingNA += itemTotal;

    rows.push(`
      <tr id="sqd${i}-row" class="${mean !== '—' ? getSQDRowColor(mean) : ''}">
        <td class="px-4 py-2">${label}</td>
        <td class="text-center font-bold text-emerald-700">${mean}</td>
        <td class="text-center italic text-gray-700">${interpretation}</td>
        <td class="text-center text-gray-800 font-semibold">${itemTotal}</td>
      </tr>
    `);
  }

  const overallMean = totalValidResponses > 0 ? (totalWeightedSum / totalValidResponses).toFixed(2) : '—';
  const overallInterpretation = totalValidResponses > 0 ? interpretMean(overallMean) : '—';

  return `
    <div class="bg-white rounded-lg p-4 shadow mt-6">
      <h2 class="text-lg font-bold text-emerald-800 text-center">SQD Weighted Mean & Interpretation</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full table-auto text-sm mt-4">
          <thead class="bg-emerald-100 text-emerald-800">
            <tr>
              <th class="px-4 py-2 text-left">SQD Item</th>
              <th class="px-4 py-2 text-center">Weighted Mean</th>
              <th class="px-4 py-2 text-center">Interpretation</th>
              <th class="px-4 py-2 text-center">Total Responses</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            ${rows.join('')}
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td class="px-4 py-5 font-semibold text-right">Overall Average</td>
              <td class="text-center font-bold text-emerald-700">${overallMean}</td>
              <td class="text-center italic text-gray-700">${overallInterpretation}</td>
              <td class="text-center text-gray-800 font-semibold">${totalResponsesIncludingNA}</td>
            </tr>
            <tr>
              <td class="px-4 py-5 font-semibold text-right">Overall Valid Responses</td>
              <td colspan="3" class="text-center text-gray-800 font-semibold">${totalValidResponses}</td>
            </tr>
            <tr>
              <td class="px-4 py-2 font-semibold text-right">Overall Total Responses (incl. N/A)</td>
              <td colspan="3" class="text-center text-gray-800 font-semibold">${totalResponsesIncludingNA}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="mt-4 text-sm text-gray-600 bg-gray-50 rounded-lg p-4">
        <details>
          <summary class="cursor-pointer font-semibold text-emerald-700">How Weighted Mean Is Calculated</summary>
          <div class="mt-2">
              <p>
                For each SQD item, the weighted mean is computed using the formula:
              </p>
              <p class="font-mono bg-white p-2 rounded border mt-2">
                Weighted Mean = (5×Strongly Agree + 4×Agree + 3×Neutral + 2×Disagree + 1×Strongly Disagree) ÷ Total Valid Responses
              </p>
              <p class="mt-2">
                N/A responses are excluded from the calculation. The interpretation is based on the following scale:
              </p>
              <ul class="list-disc ml-6 mt-2">
                <li>4.21 – 5.00: Very Satisfied</li>
                <li>3.41 – 4.20: Satisfied</li>
                <li>2.61 – 3.40: Neutral</li>
                <li>1.81 – 2.60: Dissatisfied</li>
                <li>1.00 – 1.80: Very Dissatisfied</li>
              </ul>
            </div>
        </details>
      </div>
    </div>
  `;
}

const labels = {
  sqd1: 'SQD1 (Responsiveness)',
  sqd2: 'SQD2 (Reliability)',
  sqd3: 'SQD3 (Access & Facility)',
  sqd4: 'SQD4 (Communication)',
  sqd5: 'SQD5 (Costs)',
  sqd6: 'SQD6 (Integrity)',
  sqd7: 'SQD7 (Assurance)',
  sqd8: 'SQD8 (Outcome)'
};

// ✅ Initialize only after DOM is ready
window.addEventListener('DOMContentLoaded', () => {
  setupExportButton();
  renderServiceList(); // ← Safe to call now
});