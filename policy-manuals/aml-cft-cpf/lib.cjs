// Helpers for building the AML/CFT/CPF manual template as a .docx.
// Placeholders look like {{COMPANY_SHORT}} and are highlighted yellow in the template.
// Items that need checking against current MoE / FIU / EOCN texts look like [VERIFY: ...]
// and are highlighted cyan, so they are easy to find and clear later.

const {
  Paragraph, TextRun, HeadingLevel, Table, TableRow, TableCell, WidthType, ShadingType,
  BorderStyle, AlignmentType,
} = require('docx');

const WIDTH = 9400; // usable page width in DXA (A4, 2.2cm margins)
const NAVY = '1F3864';
const GREY = 'BFBFBF';

const children = [];

function runs(text, base = {}) {
  const parts = String(text).split(/(\{\{[A-Z0-9_]+\}\}|\[VERIFY:[^\]]*\]|\*\*[^*]+\*\*)/g).filter(Boolean);
  return parts.map((p) => {
    if (/^\{\{[A-Z0-9_]+\}\}$/.test(p)) return new TextRun({ ...base, text: p, highlight: 'yellow' });
    if (/^\[VERIFY:/.test(p)) return new TextRun({ ...base, text: p, highlight: 'cyan', italics: true });
    if (/^\*\*/.test(p)) return new TextRun({ ...base, text: p.slice(2, -2), bold: true });
    return new TextRun({ ...base, text: p });
  });
}

const add = (...p) => children.push(...p);

function h1(text, { pageBreak = true } = {}) {
  add(new Paragraph({ heading: HeadingLevel.HEADING_1, pageBreakBefore: pageBreak, children: runs(text) }));
}
const h2 = (text) => add(new Paragraph({ heading: HeadingLevel.HEADING_2, children: runs(text) }));
const h3 = (text) => add(new Paragraph({ heading: HeadingLevel.HEADING_3, children: runs(text) }));

function p(text, opts = {}) {
  add(new Paragraph({
    spacing: { after: 120 },
    alignment: opts.align,
    keepNext: opts.keepNext,
    children: runs(text, opts.run || {}),
  }));
}

// items: string | [string, [subitems...]]
function bullets(items, level = 0) {
  for (const it of items) {
    const [t, sub] = Array.isArray(it) ? it : [it, null];
    add(new Paragraph({ numbering: { reference: 'bullets', level }, spacing: { after: 60 }, children: runs(t) }));
    if (sub) bullets(sub, level + 1);
  }
}

function numbered(items, ref = 'steps') {
  for (const t of items) {
    add(new Paragraph({ numbering: { reference: ref, level: 0 }, spacing: { after: 60 }, children: runs(t) }));
  }
}

const border = { style: BorderStyle.SINGLE, size: 4, color: GREY };
const borders = { top: border, bottom: border, left: border, right: border };

function cell(text, width, { header = false, shade } = {}) {
  const lines = String(text).split('\n');
  return new TableCell({
    borders,
    width: { size: width, type: WidthType.DXA },
    margins: { top: 60, bottom: 60, left: 100, right: 100 },
    shading: header ? { fill: 'D9E2F3', type: ShadingType.CLEAR, color: 'auto' }
      : shade ? { fill: shade, type: ShadingType.CLEAR, color: 'auto' } : undefined,
    children: lines.map((ln) => new Paragraph({
      spacing: { after: 40 },
      children: runs(ln, { size: 19, bold: header || undefined }),
    })),
  });
}

// rows: array of arrays of strings. widths: array of DXA widths (summing to WIDTH).
function table(headers, rows, widths) {
  const total = widths.reduce((a, b) => a + b, 0);
  const trs = [];
  if (headers) trs.push(new TableRow({ tableHeader: true, children: headers.map((h, i) => cell(h, widths[i], { header: true })) }));
  for (const r of rows) trs.push(new TableRow({ cantSplit: true, children: r.map((c, i) => cell(c, widths[i], { shade: i === 0 && !headers ? 'F2F2F2' : undefined })) }));
  add(new Table({ width: { size: total, type: WidthType.DXA }, columnWidths: widths, rows: trs }));
  add(new Paragraph({ spacing: { after: 120 }, children: [] }));
}

function note(text) {
  add(new Table({
    width: { size: WIDTH, type: WidthType.DXA },
    columnWidths: [WIDTH],
    rows: [new TableRow({ children: [new TableCell({
      borders, width: { size: WIDTH, type: WidthType.DXA },
      shading: { fill: 'FFF2CC', type: ShadingType.CLEAR, color: 'auto' },
      margins: { top: 80, bottom: 80, left: 120, right: 120 },
      children: String(text).split('\n').map((ln) => new Paragraph({ spacing: { after: 40 }, children: runs(ln, { size: 19 }) })),
    })] })],
  }));
  add(new Paragraph({ spacing: { after: 120 }, children: [] }));
}

const blank = () => add(new Paragraph({ children: [] }));

module.exports = { children, add, runs, h1, h2, h3, p, bullets, numbered, table, note, blank, WIDTH, NAVY, AlignmentType };
