import express from 'express';
import { generatePDFBuffer } from './export-report.js';

const app = express();
app.use(express.json({ limit: '2mb' }));

app.post('/generate-pdf', async (req, res) => {
  const { serviceName, from, to, reportData } = req.body;

  if (!serviceName || !from || !to || !reportData) {
    return res.status(400).send('Missing parameters');
  }

  try {
    const pdfBuffer = await generatePDFBuffer(serviceName, from, to, reportData);
    res.set({
      'Content-Type': 'application/pdf',
      'Content-Disposition': `attachment; filename="${serviceName}-report.pdf"`
    });
    res.send(pdfBuffer);
  } catch (err) {
    console.error('PDF generation error:', err);
    res.status(500).send('PDF generation failed');
  }
});

app.listen(process.env.PORT || 3000, () => {
  console.log('PDF export service running');
});