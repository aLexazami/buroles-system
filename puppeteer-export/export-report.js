import puppeteer from 'puppeteer';
import { renderDemographics, renderCharter, renderCharterSummary, renderSQDMatrix, renderSQDWeightedSummaryFromFlat, flattenSQDBreakdowns } from '../assets/js/report-renderers.js';

export async function generatePDFBuffer(serviceName, from, to, report) {
  const flatSQD = flattenSQDBreakdowns(report.sqd_breakdowns);

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
      ${renderDemographics(report)}
      ${renderCharter(report)}
      ${renderCharterSummary(report)}
      <div style="page-break-before: always;">
        ${renderSQDMatrix(report)}
        ${renderSQDWeightedSummaryFromFlat(flatSQD)}
      </div>
    </body>
    </html>
  `;

  const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.setContent(html, { waitUntil: 'networkidle0' });
  const pdfBuffer = await page.pdf({
    format: 'A4',
    printBackground: true,
    margin: { top: '1cm', bottom: '1cm' }
  });
  await browser.close();

  return pdfBuffer;
}