import { fileURLToPath } from 'node:url';
import path from 'node:path';
import fs from 'node:fs';
import puppeteer from 'puppeteer';
import { renderDemographics,renderCharter,renderCharterSummary,renderSQDMatrix,
  renderSQDWeightedSummaryFromFlat,flattenSQDBreakdowns
 } from '../assets/js/report-renderers.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Parse CLI arguments
const [serviceName, from, to, outputFile, jsonPath] = process.argv.slice(2);

// Read and parse JSON
let report;
try {
  const rawData = fs.readFileSync(jsonPath, 'utf-8');
  report = JSON.parse(rawData);
} catch (err) {
  console.error('❌ Failed to parse report JSON:', err);
  process.exit(1);
}

// Generate HTML
const demographicsHTML = renderDemographics(report);
const charterHTML = renderCharter(report);
const charterSummaryHTML = renderCharterSummary(report);
const flatSQD = flattenSQDBreakdowns(report.sqd_breakdowns);
const sqdSummaryHTML = renderSQDWeightedSummaryFromFlat(flatSQD);


// Define export folder and output path
const exportDir = path.resolve(__dirname, '../exports');
const outputPath = path.join(exportDir, outputFile);

// Ensure export folder exists
if (!fs.existsSync(exportDir)) {
  fs.mkdirSync(exportDir, { recursive: true });
}

// Create HTML content
const html = `
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-xs text-gray-800 leading-tight">
  <div class="flex flex-col px-2 py-1">
    <h1 class="text-base font-semibold text-emerald-800">${serviceName}</h1>
    <p class="text-xs text-gray-500">${from} to ${to}</p>
  </div>

  <div class="px-2 py-1">
    <h2 class="text-base text-center text-emerald-800 font-semibold">Respondents Total</h2>
    <div class="mt-1 divide-y divide-gray-200">
      <div class="grid grid-cols-2 py-1">
        <span class="font-medium">Total Respondents:</span>
        <span class="text-red-500 font-bold text-right">${report.respondents}</span>
      </div>
    </div>
  </div>

  ${demographicsHTML}
  ${charterHTML}
  ${charterSummaryHTML}

 <div style="page-break-before: always;">
  ${renderSQDMatrix(report)}
  ${sqdSummaryHTML}
</div>
</body>
</html>
`;

// Write HTML to temp file
const templatePath = path.resolve(__dirname, 'report-template.html');
fs.writeFileSync(templatePath, html);

// Generate PDF
(async () => {
  try {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();

    await page.goto(`file://${templatePath}`, { waitUntil: 'networkidle0' });
    await new Promise(resolve => setTimeout(resolve, 1000));

    await page.pdf({
      path: outputPath,
      format: 'A4',
      printBackground: true,
      margin: { top: '1cm', bottom: '1cm' }
    });

    await browser.close();
  } catch (err) {
    console.error('❌ Puppeteer error:', err);
    process.exit(1);
  }
})();