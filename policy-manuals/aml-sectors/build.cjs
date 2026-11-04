// Builds a sector manual:  node build.cjs real-estate   |   node build.cjs csp
const fs = require('fs');
const path = require('path');
const {
  Document, Packer, Paragraph, TextRun, Header, Footer, PageNumber, AlignmentType, LevelFormat,
} = require('docx');
const { transform } = require('./engine.cjs');
const makeOps = require('./common.cjs');

const key = process.argv[2];
if (!['real-estate', 'csp'].includes(key)) { console.error('usage: node build.cjs real-estate|csp'); process.exit(1); }
const S = require('./' + key + '.cjs');

const dir = transform(key, makeOps(S, S.section7, S.appendixB));
const L = require(path.join(dir, 'lib.cjs'));
for (const f of ['part1.cjs', 'part2.cjs', 'part3.cjs', 'part4.cjs']) require(path.join(dir, f))(L);

const FONT = 'Arial';
const NAVY = L.NAVY;

const doc = new Document({
  creator: 'Blue Arrow Management Consultants',
  title: S.docTitle,
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
        { level: 1, format: LevelFormat.BULLET, text: '-', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 1000, hanging: 270 } } } },
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
      children: L.runs('{{COMPANY_NAME}}  |  ' + S.headerTitle, { size: 16, color: '7F7F7F' }),
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
  const out = path.join(__dirname, S.outFile);
  fs.writeFileSync(out, buf);
  console.log('wrote', out, buf.length, 'bytes');
});
