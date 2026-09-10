'use strict';

// Usage: node generate-training-log.cjs <data.json> <out.docx>
//
// Generates a Training Attendance Log with the real BAMC letterhead (logo +
// address block in the header, licensing line in the footer — reproduced from
// "BAMC empty Letterhead with WM.docx") baked into every page, rather than a
// blank file the user pastes their own letterhead onto. The faint background
// watermark from that letterhead is NOT reproduced — it's a legacy VML
// "Insert Watermark" shape with brightness/contrast washout applied by Word
// at render time, which the docx-generation library used here has no
// equivalent for; embedding the source image directly would render solid
// instead of faint, so it's left out rather than looking wrong.

const fs = require('fs');
const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, ShadingType, VerticalAlignTable,
    Header, Footer, ImageRun, ExternalHyperlink,
} = require('docx');

const dataFile = process.argv[2];
const outFile  = process.argv[3];
if (!dataFile || !outFile) { console.error('Usage: generate-training-log.cjs <data.json> <out.docx>'); process.exit(1); }

const d   = JSON.parse(fs.readFileSync(dataFile, 'utf8'));
const val = v => (v !== null && v !== undefined && String(v).trim()) ? String(v).trim() : '—';

// ── Units — US Letter, matching the source letterhead's own page setup ────────
const PAGE_W = 12240, PAGE_H = 15840;
const MARGIN_TOP = 1440, MARGIN_BOTTOM = 1440, MARGIN_LEFT = 1134, MARGIN_RIGHT = 1080;
const HEADER_DIST = 270, FOOTER_DIST = 720;
const FULL = PAGE_W - MARGIN_LEFT - MARGIN_RIGHT;
const pt = n => n * 20;
const hp = n => n * 2;

const NAVY = '1A1A2E', GOLD = 'B8963E';
const NONE  = { style: BorderStyle.NONE,   size: 0, color: 'FFFFFF' };
const LIGHT = { style: BorderStyle.SINGLE, size: 4, color: 'D0D0D0' };
const DARK  = { style: BorderStyle.SINGLE, size: 6, color: NAVY };
const allBorders = (b) => ({ top: b, bottom: b, left: b, right: b });

const run = (text, opts = {}) => new TextRun({ text: String(text ?? ''), size: hp(10.5), font: 'Calibri', ...opts });
const para = (children, opts = {}) => new Paragraph({ children: Array.isArray(children) ? children : [children], spacing: { after: pt(4), ...opts.spacing }, ...opts });

// ── Letterhead logo ─────────────────────────────────────────────────────────
let logo = null;
if (d.logo_path) {
    try {
        const buf = fs.readFileSync(d.logo_path);
        // JPEG dimension scan (the source logo is a .jpeg) — same approach as
        // generate-kyc.cjs's reader, kept local since this is the only image type
        // this letterhead ships.
        let i = 2, dims = null;
        while (i < buf.length - 8) {
            if (buf[i] !== 0xFF) { i++; continue; }
            const marker = buf[i + 1];
            if (marker >= 0xC0 && marker <= 0xCF && marker !== 0xC4 && marker !== 0xC8 && marker !== 0xCC) {
                dims = { height: buf.readUInt16BE(i + 5), width: buf.readUInt16BE(i + 7) };
                break;
            }
            i += 2 + buf.readUInt16BE(i + 2);
        }
        if (dims && dims.width > 0 && dims.height > 0) {
            const height = 74;
            const width = Math.round(height * (dims.width / dims.height));
            logo = { data: buf, width, height };
        }
    } catch (e) { /* fall back to text-only header */ }
}

// ── Header (every page) — logo left, address block right, matching the source
//    letterhead's two-column table layout. ─────────────────────────────────
const LOGO_COL = 2300;
const headerRightChildren = [
    new Paragraph({
        alignment: AlignmentType.RIGHT,
        spacing: { after: 0 },
        children: [run('Blue Arrow Management Consultants FZC', { bold: true, italics: true, size: hp(10) })],
    }),
    new Paragraph({
        alignment: AlignmentType.RIGHT,
        spacing: { after: 0 },
        children: [run('B1602, SRTIP Building', { size: hp(10) })],
    }),
    new Paragraph({
        alignment: AlignmentType.RIGHT,
        spacing: { after: 0 },
        children: [run('Sharjah, UAE', { size: hp(10) })],
    }),
    new Paragraph({
        alignment: AlignmentType.RIGHT,
        spacing: { after: 0 },
        children: [new ExternalHyperlink({
            link: 'mailto:info@bluearrow.ae',
            children: [run('info@bluearrow.ae', { size: hp(10), color: '0563C1', underline: {} })],
        })],
    }),
    new Paragraph({
        alignment: AlignmentType.RIGHT,
        spacing: { after: 0 },
        children: [run('Tel: 050-8474481', { size: hp(10) })],
    }),
];

const headerChildren = logo
    ? [new Table({
        width: { size: FULL, type: WidthType.DXA },
        columnWidths: [LOGO_COL, FULL - LOGO_COL],
        borders: { ...allBorders(NONE), insideH: NONE, insideV: NONE },
        rows: [new TableRow({ children: [
            new TableCell({
                width: { size: LOGO_COL, type: WidthType.DXA },
                verticalAlign: VerticalAlignTable.CENTER,
                borders: allBorders(NONE),
                children: [new Paragraph({
                    alignment: AlignmentType.LEFT,
                    spacing: { after: 0 },
                    children: [new ImageRun({ type: 'jpg', data: logo.data, transformation: { width: logo.width, height: logo.height } })],
                })],
            }),
            new TableCell({
                width: { size: FULL - LOGO_COL, type: WidthType.DXA },
                verticalAlign: VerticalAlignTable.CENTER,
                borders: allBorders(NONE),
                children: headerRightChildren,
            }),
        ]})],
    })]
    : headerRightChildren;

// ════════════════════════════════════════════════════════════════════════════════
// BODY
// ════════════════════════════════════════════════════════════════════════════════
const body = [];

body.push(
    new Paragraph({
        children: [run('TRAINING ATTENDANCE LOG', { bold: true, size: hp(16), color: NAVY })],
        alignment: AlignmentType.CENTER,
        spacing: { after: pt(3) },
        border: { bottom: { style: BorderStyle.SINGLE, size: 8, color: GOLD, space: 4 } },
    }),
    para(run(''), { spacing: { after: pt(8) } }),
);

// Session info strip
const infoCell = (label, value, width) => new TableCell({
    width: { size: width, type: WidthType.DXA },
    borders: allBorders(NONE),
    shading: { fill: 'F8F9FC', type: ShadingType.CLEAR },
    margins: { top: pt(4), bottom: pt(4), left: pt(6), right: pt(6) },
    children: [
        para(run(label.toUpperCase(), { size: hp(7.5), color: '888888' }), { spacing: { after: pt(1) } }),
        para(run(val(value), { bold: true, size: hp(11), color: NAVY }), { spacing: { after: 0 } }),
    ],
});
const infoWidths = [Math.round(FULL * 0.42), Math.round(FULL * 0.24), Math.round(FULL * 0.17), Math.round(FULL * 0.17)];
body.push(
    new Table({
        width: { size: FULL, type: WidthType.DXA },
        columnWidths: infoWidths,
        borders: { ...allBorders(LIGHT), insideH: NONE, insideV: NONE },
        rows: [new TableRow({ children: [
            infoCell('Training / Programme', d.training_type, infoWidths[0]),
            infoCell('Date', d.date_formatted, infoWidths[1]),
            infoCell('Trainer', d.trainer, infoWidths[2]),
            infoCell('Total Attendees', String(d.attendees.length), infoWidths[3]),
        ]})],
    }),
    para(run(''), { spacing: { after: pt(10) } }),
);

// Attendee table — #, Name, ID Number, Company, Signature
const colWidths = [
    Math.round(FULL * 0.06),
    Math.round(FULL * 0.28),
    Math.round(FULL * 0.16),
    Math.round(FULL * 0.24),
];
colWidths.push(FULL - colWidths.reduce((a, b) => a + b, 0));

const headerRow = new TableRow({
    tableHeader: true,
    children: ['#', 'Employee Name', 'ID Number', 'Company', 'Signature'].map((h, i) => new TableCell({
        width: { size: colWidths[i], type: WidthType.DXA },
        shading: { fill: NAVY, type: ShadingType.CLEAR },
        borders: allBorders(NONE),
        margins: { top: pt(3), bottom: pt(3), left: pt(6), right: pt(6) },
        children: [para(run(h, { bold: true, color: 'FFFFFF', size: hp(9.5) }), { spacing: { after: 0 } })],
    })),
});

const attendeeRows = d.attendees.map((a, i) => new TableRow({
    children: [
        String(i + 1), a.employee_name, val(a.employee_id_number), val(a.company_name), '',
    ].map((cell, ci) => new TableCell({
        width: { size: colWidths[ci], type: WidthType.DXA },
        shading: { fill: i % 2 === 1 ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
        borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: NONE },
        margins: { top: pt(5), bottom: pt(5), left: pt(6), right: pt(6) },
        children: [para(run(cell, { size: hp(9.5) }), { spacing: { after: 0 } })],
    })),
}));

body.push(
    new Table({
        width: { size: FULL, type: WidthType.DXA },
        columnWidths: colWidths,
        borders: { top: DARK, bottom: DARK, left: DARK, right: DARK, insideH: LIGHT, insideV: NONE },
        rows: [headerRow, ...attendeeRows],
    }),
);

// Trainer sign-off — a printed name/date line plus a blank signature line,
// mirroring how the KYC pack signs off a signatory block.
const sigCol = (label, value, width) => new TableCell({
    width: { size: width, type: WidthType.DXA },
    borders: allBorders(NONE),
    margins: { top: pt(2), bottom: 0, left: pt(4), right: pt(4) },
    children: [
        para(run(''), { spacing: { before: pt(50), after: 0 }, border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: '000000' } } }),
        para(run(val(value) === '—' ? ' ' : value, { bold: true, size: hp(10) }), { spacing: { after: pt(1) } }),
        para(run(label, { size: hp(9), color: '6B7280' }), { spacing: { after: 0 } }),
    ],
});
const sigWidths = [Math.round(FULL / 3), Math.round(FULL / 3)];
sigWidths.push(FULL - sigWidths[0] - sigWidths[1]);

body.push(
    para(run(''), { spacing: { after: pt(24) } }),
    new Table({
        width: { size: FULL, type: WidthType.DXA },
        columnWidths: sigWidths,
        borders: { ...allBorders(NONE), insideH: NONE, insideV: NONE },
        rows: [new TableRow({ children: [
            sigCol('Trainer / Facilitator', d.trainer, sigWidths[0]),
            sigCol('Signature', '', sigWidths[1]),
            sigCol('Date', '', sigWidths[2]),
        ]})],
    }),
);

// ════════════════════════════════════════════════════════════════════════════════
// ASSEMBLE
// ════════════════════════════════════════════════════════════════════════════════
const doc = new Document({
    sections: [{
        properties: {
            page: {
                size: { width: PAGE_W, height: PAGE_H },
                margin: { top: MARGIN_TOP, bottom: MARGIN_BOTTOM, left: MARGIN_LEFT, right: MARGIN_RIGHT, header: HEADER_DIST, footer: FOOTER_DIST },
            },
        },
        headers: { default: new Header({ children: headerChildren }) },
        footers: {
            default: new Footer({ children: [
                new Paragraph({
                    alignment: AlignmentType.CENTER,
                    spacing: { after: 0 },
                    children: [run('Blue Arrow Management Consultants FZC is licensed under Sharjah Research Technology & Innovation Park', { size: hp(8), color: '555555' })],
                }),
            ]}),
        },
        children: body,
    }],
});

Packer.toBuffer(doc).then(buf => {
    fs.writeFileSync(outFile, buf);
    console.log('ok');
}).catch(err => {
    console.error(err.message);
    process.exit(1);
});
