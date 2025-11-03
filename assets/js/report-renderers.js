// report-renderers.js

export const ageLabels = {
  '19_or_lower': '19 or Lower',
  '20_34': '20–34',
  '35_49': '35–49',
  '50_64': '50–64',
  '65_or_higher': '65 or Higher'
};

export const sqdLabels = [
  'SQD1 – Responsiveness',
  'SQD2 – Reliability',
  'SQD3 – Access and Facilities',
  'SQD4 – Communication',
  'SQD5 – Costs',
  'SQD6 – Integrity',
  'SQD7 – Assurance',
  'SQD8 – Outcome'
];

export const sqdScores = ['5', '4', '3', '2', '1', 'na'];

export function renderRespondents(data, yearRange) {
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

export function renderDemographics(data) {
  return `
    <div class="px-2 py-1">
      <h2 class="text-base text-center text-emerald-800 font-semibold">Demographic Profile</h2>
      <div class="mt-1 grid grid-cols-3 gap-2">
        ${renderDemographicGroup('A. Age', data.age)}
        ${renderDemographicGroup('B. Sex', { Female: data.female, Male: data.male })}
        ${renderDemographicGroup('C. Customer Type', data.customer_types)}
      </div>
    </div>
  `;
}

function renderDemographicGroup(title, values) {
  return `
    <div class="bg-gray-50 px-2 py-1 rounded shadow-inner">
      <h3 class="text-center text-emerald-700 font-semibold mb-1 text-sm">${title}</h3>
      <div class="divide-y divide-gray-100">
        ${Object.entries(values).map(([label, count]) => `
          <div class="grid grid-cols-2 py-1">
            <span class="text-xs">${ageLabels[label] || label}</span>
            <span class="text-red-500 font-bold text-right text-xs">${count}</span>
          </div>
        `).join('')}
      </div>
    </div>
  `;
}

const charterDescriptions = {
  CC1: [
    'I know what a Citizen’s Charter is and I saw this office’s Citizen’s Charter.',
    'I know what a Citizen’s Charter is but I did not see this office’s Citizen’s Charter.',
    'I learned of the Citizen’s Charter only when I saw this office’s Citizen’s Charter.',
    'I do not know what a Citizen’s Charter is and I did not see one in this office.'
  ],
  CC2: [
    'Easy to see',
    'Somewhat easy to see',
    'Difficult to see',
    'Not visible at all',
    'N/A'
  ],
  CC3: [
    'Helped very much',
    'Somewhat helped',
    'Did not help',
    'N/A'
  ]
};

export function renderCharter(data) {
  return `
    <div class="px-2 py-1">
      <h2 class="text-base text-center text-emerald-800 font-semibold">Citizen's Charter Responses</h2>
      <div class="mt-1 grid grid-cols-3 gap-2">
        ${renderCharterGroup('CC1', data.charter.cc1)}
        ${renderCharterGroup('CC2', data.charter.cc2)}
        ${renderCharterGroup('CC3', data.charter.cc3)}
      </div>
    </div>
  `;
}

export function renderCharterGroup(title, values) {
  const descriptions = charterDescriptions[title] || [];

  return `
    <div class="divide-y divide-gray-100">
      <h3 class="text-center font-semibold text-emerald-700 mb-1">${title}</h3>
      ${Object.entries(values).map(([score, count]) => {
        const index = parseInt(score, 10) - 1;
        const label = descriptions[index] || 'N/A';
        return `
          <div class="grid grid-cols-2 py-1">
            <span class="text-xs">${score}. ${label}</span>
            <span class="text-red-400 font-bold text-right text-xs">${count}</span>
          </div>
        `;
      }).join('')}
    </div>
  `;
}

export function renderCharterSummary(data) {
  // CC1 Awareness Summary
  const cc1 = data.charter?.cc1 || {};
  const cc1_1 = cc1[1] || 0;
  const cc1_2 = cc1[2] || 0;
  const cc1_3 = cc1[3] || 0;
  const cc1_4 = cc1[4] || 0;

  const cc1Total = cc1_1 + cc1_2 + cc1_3 + cc1_4;
  const cc1Aware = cc1_1 + cc1_2 + cc1_3;

  const cc1AwarePct = cc1Total ? ((cc1Aware / cc1Total) * 100).toFixed(1) : '—';
  const cc1FullyAwarePct = cc1Total ? ((cc1_1 / cc1Total) * 100).toFixed(1) : '—';
  const cc1UnawarePct = cc1Total ? ((cc1_4 / cc1Total) * 100).toFixed(1) : '—';

  // CC2 Visibility Weighted Mean
  const cc2 = data.charter?.cc2 || {};
  const cc2Weights = { '1': 4, '2': 3, '3': 2, '4': 1 };
  let cc2Total = 0, cc2ValidTotal = 0, cc2Weighted = 0;

  Object.entries(cc2).forEach(([key, count]) => {
    const value = parseInt(count, 10) || 0;
    cc2Total += value;
    if (cc2Weights[key]) {
      cc2ValidTotal += value;
      cc2Weighted += value * cc2Weights[key];
    }
  });

  const cc2Mean = cc2ValidTotal ? (cc2Weighted / cc2ValidTotal).toFixed(2) : '—';
  const cc2Interpretation = cc2ValidTotal
    ? parseFloat(cc2Mean) >= 3.5
      ? 'Highly Visible'
      : parseFloat(cc2Mean) >= 2.5
        ? 'Moderately Visible'
        : 'Poorly Visible'
    : '—';

  // CC3 Helpfulness Weighted Mean
  const cc3 = data.charter?.cc3 || {};
  const cc3Weights = { '1': 3, '2': 2, '3': 1 };
  let cc3Total = 0, cc3ValidTotal = 0, cc3Weighted = 0;

  Object.entries(cc3).forEach(([key, count]) => {
    const value = parseInt(count, 10) || 0;
    cc3Total += value;
    if (cc3Weights[key]) {
      cc3ValidTotal += value;
      cc3Weighted += value * cc3Weights[key];
    }
  });

  const cc3Mean = cc3ValidTotal ? (cc3Weighted / cc3ValidTotal).toFixed(2) : '—';
  const cc3Interpretation = cc3ValidTotal
    ? parseFloat(cc3Mean) >= 2.5
      ? 'Very Helpful'
      : parseFloat(cc3Mean) >= 1.5
        ? 'Somewhat Helpful'
        : 'Not Helpful'
    : '—';

  // Final HTML block
  return `
    <div class="px-2 py-1">
      <h2 class="text-base font-semibold text-emerald-800 text-center mb-2">
        Citizen Charter Summary & Interpretation
      </h2>

      <div class="mb-4 text-xs text-gray-700 bg-gray-50 rounded px-2 py-1">
        <h3 class="text-center text-emerald-700 font-semibold mb-1">CC1 Awareness</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li>${cc1AwarePct}% aware of the Citizen’s Charter (responses 1–3)</li>
          <li>${cc1FullyAwarePct}% fully aware and have seen it (response 1)</li>
          <li>${cc1UnawarePct}% not aware at all (response 4)</li>
        </ul>
      </div>

      <div class="mb-4 text-xs overflow-x-auto">
        <h3 class="text-center text-emerald-700 font-semibold mb-1">CC2 Visibility</h3>
        <table class="min-w-full table-auto text-xs">
          <thead class="bg-emerald-100 text-emerald-800">
            <tr>
              <th class="px-2 py-1 text-left">Metric</th>
              <th class="px-2 py-1 text-center">Mean</th>
              <th class="px-2 py-1 text-center">Interpretation</th>
              <th class="px-2 py-1 text-center">Responses</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr class="${cc2Interpretation === 'Highly Visible' ? 'bg-green-50' : cc2Interpretation === 'Moderately Visible' ? 'bg-yellow-50' : cc2Interpretation === 'Poorly Visible' ? 'bg-red-50' : ''}">
              <td class="px-2 py-1">CC2 – Visibility</td>
              <td class="text-center font-bold text-emerald-700">${cc2Mean}</td>
              <td class="text-center italic text-gray-700">${cc2Interpretation}</td>
              <td class="text-center text-gray-800 font-semibold">${cc2Total}</td>
            </tr>
          </tbody>
        </table>
        <p class="text-center mt-2 text-gray-600">
          <strong>Total:</strong> ${cc2Total} | <strong>Valid:</strong> ${cc2ValidTotal}
        </p>
      </div>

      <div class="text-xs overflow-x-auto">
        <h3 class="text-center text-emerald-700 font-semibold mb-1">CC3 Helpfulness</h3>
        <table class="min-w-full table-auto text-xs">
          <thead class="bg-emerald-100 text-emerald-800">
            <tr>
              <th class="px-2 py-1 text-left">Metric</th>
              <th class="px-2 py-1 text-center">Mean</th>
              <th class="px-2 py-1 text-center">Interpretation</th>
              <th class="px-2 py-1 text-center">Responses</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr class="${cc3Interpretation === 'Very Helpful' ? 'bg-green-50' : cc3Interpretation === 'Somewhat Helpful' ? 'bg-yellow-50' : cc3Interpretation === 'Not Helpful' ? 'bg-red-50' : ''}">
              <td class="px-2 py-1">CC3 – Helpfulness</td>
              <td class="text-center font-bold text-emerald-700">${cc3Mean}</td>
              <td class="text-center italic text-gray-700">${cc3Interpretation}</td>
              <td class="text-center text-gray-800 font-semibold">${cc3Total}</td>
            </tr>
          </tbody>
        </table>
        <p class="text-center mt-2 text-gray-600">
          <strong>Total:</strong> ${cc3Total} | <strong>Valid:</strong> ${cc3ValidTotal}
        </p>
      </div>
    </div>
  `;
}

export function renderSQDMatrix(data) {
  const breakdownRows = sqdLabels.map((label, index) => {
    const key = `sqd${index + 1}`;
    const breakdown = data.sqd_breakdowns?.[key] || {};

    const cells = sqdScores.map(score => {
      const count = breakdown[score] ?? 0;
      return `<td class="text-center text-red-500 font-bold text-xs">${count}</td>`;
    }).join('');

    return `<tr>
      <td class="px-2 py-1 text-xs font-medium">${label}</td>
      ${cells}
    </tr>`;
  }).join('');

  return `
    <div class="px-2 py-1">
      <h2 class="text-base text-center text-emerald-800 font-semibold mb-2">Client Satisfaction Matrix</h2>
      <div class="overflow-x-auto bg-white rounded shadow px-2 py-1">
        <table class="min-w-full table-auto border-collapse text-xs">
          <thead class="bg-emerald-100 text-emerald-800">
            <tr>
              <th class="px-2 py-1 text-left">SQD Item</th>
              <th class="px-2 py-1 text-center">5</th>
              <th class="px-2 py-1 text-center">4</th>
              <th class="px-2 py-1 text-center">3</th>
              <th class="px-2 py-1 text-center">2</th>
              <th class="px-2 py-1 text-center">1</th>
              <th class="px-2 py-1 text-center">N/A</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            ${breakdownRows}
          </tbody>
        </table>
      </div>
    </div>
  `;
}

export function flattenSQDBreakdowns(nested) {
  const flat = {};
  Object.entries(nested || {}).forEach(([key, scores]) => {
    Object.entries(scores).forEach(([score, count]) => {
      flat[`${key}-${score}`] = count;
    });
  });
  return flat;
}

export function interpretMean(mean) {
  const m = parseFloat(mean);
  if (m >= 4.21) return 'Very Satisfied';
  if (m >= 3.41) return 'Satisfied';
  if (m >= 2.61) return 'Neutral';
  if (m >= 1.81) return 'Dissatisfied';
  if (m >= 1.0) return 'Very Dissatisfied';
  return '—';
}

export function getSQDRowColor(mean) {
  const m = parseFloat(mean);
  if (m >= 4.21) return 'bg-green-50';
  if (m >= 3.41) return 'bg-lime-50';
  if (m >= 2.61) return 'bg-yellow-50';
  if (m >= 1.81) return 'bg-orange-50';
  return 'bg-red-50';
}

export function renderSQDWeightedSummaryFromFlat(data) {
  const weights = { '5': 5, '4': 4, '3': 3, '2': 2, '1': 1 };
  let totalWeightedSum = 0;
  let totalValidResponses = 0;
  let totalResponsesIncludingNA = 0;

  const rows = [];

  for (let i = 1; i <= 8; i++) {
    const label = sqdLabels[i - 1];
    let itemTotal = 0, validTotal = 0, weightedSum = 0;

    for (const score of ['5', '4', '3', '2', '1', 'na']) {
      const key = `sqd${i}-${score}`;
      const count = parseInt(data[key], 10) || 0;
      itemTotal += count;
      if (weights[score]) {
        validTotal += count;
        weightedSum += count * weights[score];
      }
    }

    const mean = validTotal ? (weightedSum / validTotal).toFixed(2) : '—';
    const interpretation = validTotal ? interpretMean(mean) : '—';

    totalWeightedSum += weightedSum;
    totalValidResponses += validTotal;
    totalResponsesIncludingNA += itemTotal;

    rows.push(`
      <tr class="${mean !== '—' ? getSQDRowColor(mean) : ''}">
        <td class="px-2 py-1 text-xs">${label}</td>
        <td class="text-center font-bold text-emerald-700 text-xs">${mean}</td>
        <td class="text-center italic text-gray-700 text-xs">${interpretation}</td>
        <td class="text-center text-gray-800 font-semibold text-xs">${itemTotal}</td>
      </tr>
    `);
  }

  const overallMean = totalValidResponses ? (totalWeightedSum / totalValidResponses).toFixed(2) : '—';
  const overallInterpretation = totalValidResponses ? interpretMean(overallMean) : '—';

  return `
    <div class="px-2 py-1">
      <h2 class="text-base font-semibold text-emerald-800 text-center mb-2">SQD Weighted Mean & Interpretation</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full table-auto text-xs">
          <thead class="bg-emerald-100 text-emerald-800">
            <tr>
              <th class="px-2 py-1 text-left">SQD Item</th>
              <th class="px-2 py-1 text-center">Mean</th>
              <th class="px-2 py-1 text-center">Interpretation</th>
              <th class="px-2 py-1 text-center">Responses</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            ${rows.join('')}
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td class="px-2 py-1 font-semibold text-right">Overall Average</td>
              <td class="text-center font-bold text-emerald-700">${overallMean}</td>
              <td class="text-center italic text-gray-700">${overallInterpretation}</td>
              <td class="text-center text-gray-800 font-semibold">${totalResponsesIncludingNA}</td>
            </tr>
            <tr>
              <td class="px-2 py-1 font-semibold text-right">Overall Valid Responses</td>
              <td colspan="3" class="text-center text-gray-800 font-semibold">${totalValidResponses}</td>
            </tr>
            <tr>
              <td class="px-2 py-y font-semibold text-right">Overall Total Responses (incl. N/A)</td>
              <td colspan="3" class="text-center text-gray-800 font-semibold">${totalResponsesIncludingNA}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  `;
}