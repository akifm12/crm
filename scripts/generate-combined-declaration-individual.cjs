// scripts/generate-combined-declaration-individual.cjs
// Individual client declaration — Gold DNFBP
// Sections: PEP, Source of Funds & Wealth, Sanctions, CAHRA
// Usage: node generate-combined-declaration-individual.cjs <data_json_file> <output_path>

'use strict';

const fs  = require('fs');
const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, ShadingType,
} = require('docx');

const dataFile = process.argv[2];
const outFile  = process.argv[3];

if (!dataFile || !outFile) {
    console.error('Usage: node generate-combined-declaration-individual.cjs <data_json_file> <output_path>');
    process.exit(1);
}

const d = JSON.parse(fs.readFileSync(dataFile, 'utf8'));

// ── Helpers ────────────────────────────────────────────────────────────────

const BLUE      = '0e4d8a';
const DARK_BLUE = '1e3a5f';
const LIGHT     = 'f1f5f9';

const run  = (text, opts = {}) => new TextRun({ text, size: 22, ...opts });
const bold = (text, opts = {}) => new TextRun({ text, size: 22, bold: true, ...opts });

const para = (children, opts = {}) => new Paragraph({
    children: Array.isArray(children) ? children : [children],
    spacing: { after: 100, ...(opts.spacing || {}) },
    alignment: opts.align || AlignmentType.JUSTIFIED,
    ...opts,
});

const blank = (n = 1) => Array.from({ length: n }, () =>
    new Paragraph({ children: [run('')], spacing: { after: 60 } })
);

const divider = (color = 'dde3ea') => new Paragraph({
    border: { bottom: { style: BorderStyle.SINGLE, size: 6, color } },
    spacing: { before: 160, after: 160 },
    children: [],
});

const sectionHeading = (num, title) => new Paragraph({
    children: [new TextRun({ text: `${num}.  ${title}`, bold: true, size: 24, color: BLUE })],
    spacing: { before: 280, after: 120 },
    shading: { type: ShadingType.CLEAR, fill: LIGHT },
});

const bulletItem = (num, text) => new Paragraph({
    children: [run(`${num}.  ${text}`)],
    spacing: { after: 100 },
    indent: { left: 440 },
    alignment: AlignmentType.JUSTIFIED,
});

const infoRow = (label, value) => new TableRow({
    children: [
        new TableCell({
            children: [para([bold(label)], { spacing: { after: 40, before: 40 } })],
            width: { size: 35, type: WidthType.PERCENTAGE },
            shading: { type: ShadingType.CLEAR, fill: LIGHT },
        }),
        new TableCell({
            children: [para([run(value || '—')], { spacing: { after: 40, before: 40 } })],
            width: { size: 65, type: WidthType.PERCENTAGE },
        }),
    ],
});

const infoTable = (rows) => new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    rows: rows.map(([l, v]) => infoRow(l, v)),
    borders: {
        top:     { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
        bottom:  { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
        left:    { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
        right:   { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
        insideH: { style: BorderStyle.SINGLE, size: 2, color: 'e2e8f0' },
        insideV: { style: BorderStyle.SINGLE, size: 2, color: 'e2e8f0' },
    },
});

// ── Content ────────────────────────────────────────────────────────────────

const clientInfo = [
    ['Full name',             d.client_name],
    ['Passport / Emirates ID', d.trade_license || '—'],
    ['Nationality',           d.country || '—'],
    ['Declaration date',      d.date],
];

const children = [

    // ── HEADER ──────────────────────────────────────────────────────────────
    new Paragraph({
        children: [new TextRun({ text: d.entity_name, bold: true, size: 36, color: BLUE })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 40 },
    }),
    new Paragraph({
        children: [run('AML / CFT Compliance Department', { color: '64748b' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 40 },
    }),
    new Paragraph({
        children: [run(d.entity_address || '', { color: '94a3b8', size: 20 })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 200 },
    }),

    divider(BLUE),

    new Paragraph({
        children: [new TextRun({ text: 'INDIVIDUAL CLIENT DECLARATION', bold: true, size: 30, color: DARK_BLUE })],
        alignment: AlignmentType.CENTER,
        spacing: { before: 80, after: 40 },
    }),
    new Paragraph({
        children: [run('Anti-Money Laundering & Counter-Financing of Terrorism', { italics: true, color: '64748b' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 40 },
    }),
    new Paragraph({
        children: [run('Pursuant to UAE Federal Decree-Law No. 20 of 2018', { size: 20, color: '94a3b8' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 200 },
    }),

    divider(),

    // ── CLIENT DETAILS ───────────────────────────────────────────────────────
    para([bold('CLIENT DETAILS', { color: BLUE, size: 20 })], { spacing: { after: 80 } }),
    infoTable(clientInfo),

    ...blank(1),

    // ── INTRO ────────────────────────────────────────────────────────────────
    para([
        run('I, '), bold(d.client_name),
        run(`, hereby make the following declarations to ${d.entity_name} ("the Company") in connection with my purchase/sale of gold or precious metals, in compliance with UAE AML/CFT legislation and the Company's Know Your Customer (KYC) requirements:`),
    ]),

    divider(),

    // ═══════════════════════════════════════════════════════════════════════
    // SECTION A — PEP
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('A', 'Politically Exposed Person (PEP) Declaration'),

    bulletItem('1', 'I confirm that I am not a Politically Exposed Person (PEP) as defined under UAE Federal Decree-Law No. 20 of 2018 and FATF Recommendations.'),
    bulletItem('2', 'I confirm that I do not hold, and have not held within the last 12 months, any prominent public function including but not limited to: head of state, senior politician, senior government official, judicial officer, senior military official, executive of a state-owned corporation, or senior political party official, in the UAE or any other country.'),
    bulletItem('3', 'I confirm that I am not a close family member or known close associate of any such person.'),
    bulletItem('4', 'I undertake to notify the Company immediately if my PEP status changes at any time.'),

    ...blank(1),

    // ═══════════════════════════════════════════════════════════════════════
    // SECTION B — SOURCE OF FUNDS & WEALTH
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('B', 'Source of Funds & Source of Wealth Declaration'),

    bulletItem('1', 'All funds used in this transaction originate from legitimate and lawful sources.'),
    bulletItem('2', 'My source of funds for this transaction is (please specify): _______________________________________________'),
    bulletItem('3', 'My source of wealth — being the origin of the total assets I own — is from lawful activities including employment, business, savings, and/or investment.'),
    bulletItem('4', 'No funds involved in this transaction are derived from any criminal activity, tax evasion, corruption, fraud, or money laundering.'),
    bulletItem('5', 'I undertake to provide supporting documentation for my source of funds and wealth upon request by the Company or any regulatory authority.'),

    ...blank(1),

    // ═══════════════════════════════════════════════════════════════════════
    // SECTION C — SANCTIONS COMPLIANCE
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('C', 'Sanctions Compliance Declaration'),

    bulletItem('1', 'I confirm that I do not appear on any UAE, UN Security Council, OFAC, EU Consolidated, or UK HM Treasury financial sanctions list.'),
    bulletItem('2', 'I confirm that I am not subject to any Targeted Financial Sanctions (TFS) under UAE Cabinet Resolution No. 74 of 2020 or any UN Security Council resolution.'),
    bulletItem('3', 'I confirm that the funds used in this transaction are not connected to any sanctioned person, entity, or jurisdiction.'),
    bulletItem('4', 'I undertake to notify the Company immediately if my sanctions status changes at any time.'),

    ...blank(1),

    // ═══════════════════════════════════════════════════════════════════════
    // SECTION D — CAHRA
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('D', 'Conflict-Affected & High-Risk Areas (CAHRA) Declaration'),

    bulletItem('1', 'I confirm that any gold or precious metals involved in this transaction do not originate from conflict-affected and high-risk areas (CAHRA) as defined by the OECD Due Diligence Guidance.'),
    bulletItem('2', 'I confirm that this transaction does not involve proceeds from the illegal exploitation of natural resources in any conflict zone.'),
    bulletItem('3', 'I acknowledge that trading in minerals from conflict zones is a criminal offence under UAE law and international conventions.'),

    divider(),

    // ── AFFIRMATION ──────────────────────────────────────────────────────────
    new Paragraph({
        children: [new TextRun({ text: 'AFFIRMATION', bold: true, size: 26, color: DARK_BLUE })],
        spacing: { before: 80, after: 120 },
    }),

    para([run('By signing below, I, '), bold(d.client_name), run(', confirm that:')]),

    ...['A — Politically Exposed Person (PEP)',
        'B — Source of Funds & Source of Wealth',
        'C — Sanctions Compliance',
        'D — Conflict-Affected & High-Risk Areas (CAHRA)',
    ].map(s => new Paragraph({
        children: [
            new TextRun({ text: '☐  ', size: 22 }),
            run(`I have read, understood, and agree to the declarations in Section `),
            bold(s), run('.'),
        ],
        spacing: { after: 100 },
        indent: { left: 300 },
    })),

    ...blank(1),

    para([
        run('I confirm that all declarations made herein are '),
        bold('true, accurate, and complete'),
        run(' to the best of my knowledge. I understand that providing false or misleading information is a '),
        bold('criminal offence'),
        run(' under UAE Federal Decree-Law No. 20 of 2018 and may result in criminal prosecution and regulatory action.'),
    ]),

    divider(),

    // ── SIGNATURE ────────────────────────────────────────────────────────────
    new Paragraph({
        children: [new TextRun({ text: 'SIGNATURE', bold: true, size: 24, color: DARK_BLUE })],
        spacing: { before: 80, after: 200 },
    }),

    new Table({
        width: { size: 100, type: WidthType.PERCENTAGE },
        borders: {
            top: { style: BorderStyle.NONE }, bottom: { style: BorderStyle.NONE },
            left: { style: BorderStyle.NONE }, right: { style: BorderStyle.NONE },
            insideH: { style: BorderStyle.NONE }, insideV: { style: BorderStyle.NONE },
        },
        rows: [new TableRow({
            children: [
                new TableCell({
                    width: { size: 48, type: WidthType.PERCENTAGE },
                    children: [
                        new Paragraph({ children: [run('Signature:  ___________________________')], spacing: { after: 160 } }),
                        new Paragraph({ children: [run('Full name:  '), bold(d.client_name)], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Passport / EID:  '), run(d.trade_license || '_______________')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Date:  ___________________________')], spacing: { after: 100 } }),
                    ],
                }),
                new TableCell({
                    width: { size: 4, type: WidthType.PERCENTAGE },
                    children: [new Paragraph({ children: [] })],
                }),
                new TableCell({
                    width: { size: 48, type: WidthType.PERCENTAGE },
                    children: [
                        new Paragraph({ children: [run('Received & verified by:')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Signature:  ___________________________')], spacing: { after: 160 } }),
                        new Paragraph({ children: [run('Name:  '), bold(d.mlro_name || '___________________________')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Title:  '), bold('MLRO / Compliance Officer')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Date:  ___________________________')], spacing: { after: 100 } }),
                    ],
                }),
            ],
        })],
    }),

    divider(),

    new Paragraph({
        children: [new TextRun({
            text: `CONFIDENTIAL — This declaration is made pursuant to UAE Federal Decree-Law No. 20 of 2018 on Anti-Money Laundering, Combating the Financing of Terrorism and Financing of Illegal Organisations. To be retained by ${d.entity_name} for a minimum of five (5) years.`,
            size: 18, color: '94a3b8', italics: true,
        })],
        alignment: AlignmentType.JUSTIFIED,
        spacing: { before: 100 },
    }),
];

const doc = new Document({
    sections: [{
        properties: {
            page: { margin: { top: 1080, bottom: 1080, left: 1080, right: 1080 } },
        },
        children,
    }],
});

Packer.toBuffer(doc).then(buffer => {
    fs.writeFileSync(outFile, buffer);
    console.log('OK: ' + outFile);
}).catch(err => {
    console.error('ERROR: ' + err.message);
    process.exit(1);
});
