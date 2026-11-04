// Builds the AML/CFT/CPF manual template:  node build.cjs  ->  AML-CFT-CPF-Manual-Template-v2.2.docx
const fs = require('fs');
const path = require('path');
const {
  Document, Packer, Paragraph, TextRun, Header, Footer, PageNumber, AlignmentType, LevelFormat,
} = require('docx');

const L = require('./lib.cjs');
require('./part1.cjs')(L);
require('./part2.cjs')(L);
require('./part3.cjs')(L);
require('./part4.cjs')(L);

const FONT = 'Calibri';
const NAVY = L.NAVY;

const doc = new Document({
  creator: 'Blue Arrow Management Consultants',
  title: 'AML / CFT / CPF and Sanctions Policy and Procedures Manual — Template v2.2',
  features: { updateFields: true },
  styles: {
    default: { document: { run: { font: FONT, size: 21 } } },
    paragraphStyles: [
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 30, bold: true, font: FONT, color: NAVY },
        paragraph: { spacing: { before: 240, after: 200 }, outlineLevel: 0, keepNext: true } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 25, bold: true, font: FONT, color: NAVY },
        paragraph: { spacing: { before: 220, after: 100 }, outlineLevel: 1, keepNext: true } },
      { id: 'Heading3', name: 'Heading 3', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 22, bold: true, font: FONT, color: '2F5496' },
        paragraph: { spacing: { before: 160, after: 80 }, outlineLevel: 2, keepNext: true } },
    ],
  },
  numbering: {
    config: [
      { reference: 'bullets', levels: [
        { level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 540, hanging: 270 } } } },
        { level: 1, format: LevelFormat.BULLET, text: '–', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 1000, hanging: 270 } } } },
      ] },
      { reference: 'steps', levels: [
        { level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 540, hanging: 360 } } } },
      ] },
    ],
  },
  sections: [{
    properties: { page: { size: { width: 11906, height: 16838 }, margin: { top: 1300, bottom: 1200, left: 1253, right: 1253 } } },
    headers: { default: new Header({ children: [new Paragraph({
      alignment: AlignmentType.RIGHT,
      children: L.runs('{{COMPANY_NAME}}  —  AML / CFT / CPF and Sanctions Manual', { size: 16, color: '7F7F7F' }),
    })] }) },
    footers: { default: new Footer({ children: [new Paragraph({
      alignment: AlignmentType.CENTER,
      children: [
        ...L.runs('Version {{VERSION}}  |  Confidential  |  Page ', { size: 16, color: '7F7F7F' }),
        new TextRun({ children: [PageNumber.CURRENT], size: 16, color: '7F7F7F' }),
        new TextRun({ text: ' of ', size: 16, color: '7F7F7F' }),
        new TextRun({ children: [PageNumber.TOTAL_PAGES], size: 16, color: '7F7F7F' }),
      ],
    })] }) },
    children: L.children,
  }],
});

Packer.toBuffer(doc).then((buf) => {
  const out = path.join(__dirname, 'AML-CFT-CPF-Manual-Template-v2.2.docx');
  fs.writeFileSync(out, buf);
  console.log('wrote', out, buf.length, 'bytes');
});
