// scripts/generate-combined-declaration.cjs
// Usage: node generate-combined-declaration.cjs <data_json_file> <output_path>

'use strict';

const fs   = require('fs');
const path = require('path');
const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, HeadingLevel,
    ShadingType, PageBreak,
} = require('docx');

const dataFile = process.argv[2];
const outFile  = process.argv[3];

if (!dataFile || !outFile) {
    console.error('Usage: node generate-combined-declaration.cjs <data_json_file> <output_path>');
    process.exit(1);
}

const d = JSON.parse(fs.readFileSync(dataFile, 'utf8'));

// ── Helpers ────────────────────────────────────────────────────────────────

const BLUE      = '0e4d8a';
const LIGHT     = 'f1f5f9';
const DARK_BLUE = '1e3a5f';

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
    children: [
        new TextRun({ text: `${num}.  ${title}`, bold: true, size: 24, color: BLUE }),
    ],
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

// ── UBO table ──────────────────────────────────────────────────────────────

const uboTable = (ubos) => {
    if (!ubos || ubos.length === 0) return null;

    const headerRow = new TableRow({
        children: ['Full name', 'Nationality', 'Ownership %'].map(h =>
            new TableCell({
                children: [new Paragraph({ children: [bold(h, { color: 'ffffff' })], spacing: { after: 40, before: 40 } })],
                shading: { type: ShadingType.CLEAR, fill: DARK_BLUE },
            })
        ),
    });

    const dataRows = ubos.map(u => new TableRow({
        children: [u.name || '—', u.nationality || '—', u.ownership ? `${u.ownership}%` : '—'].map(cell =>
            new TableCell({
                children: [new Paragraph({ children: [run(cell)], spacing: { after: 40, before: 40 } })],
            })
        ),
    }));

    return new Table({
        width: { size: 100, type: WidthType.PERCENTAGE },
        rows: [headerRow, ...dataRows],
        borders: {
            top:     { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
            bottom:  { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
            left:    { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
            right:   { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
            insideH: { style: BorderStyle.SINGLE, size: 2, color: 'e2e8f0' },
            insideV: { style: BorderStyle.SINGLE, size: 2, color: 'e2e8f0' },
        },
    });
};

// ── Build document ──────────────────────────────────────────────────────────

const clientInfo = [
    ['Entity / Individual name', d.client_name],
    ['Trade licence / Passport', d.trade_license || '—'],
    ['Country',                  d.country || '—'],
    ['Authorised signatory',     d.signatory_name || '—'],
    ['Declaration date',         d.date],
];

const uboList = d.ubos && d.ubos.length > 0
    ? d.ubos.map(u => `${u.name} (${u.nationality}, ${u.ownership}%)`)
    : null;

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
        children: [new TextRun({ text: 'COMBINED CLIENT DECLARATION', bold: true, size: 30, color: DARK_BLUE })],
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
        run('I / We, '), bold(d.signatory_name || '___________'), run(', acting as authorised signatory on behalf of '),
        bold(d.client_name), run(` ("the Client"), hereby make the following declarations to ${d.entity_name} ("the Company") in connection with our business relationship and in compliance with UAE AML/CFT legislation:`),
    ], { spacing: { after: 80 } }),

    divider(),

    // ═══════════════════════════════════════════════════════════════════════
    // DECLARATION 1 — PEP
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('A', 'Politically Exposed Person (PEP) Declaration'),

    bulletItem('1', 'Neither the Client nor any of its directors, shareholders, beneficial owners, or authorised signatories is a Politically Exposed Person (PEP) as defined under UAE Federal Decree-Law No. 20 of 2018 and FATF Recommendations.'),
    bulletItem('2', 'No person associated with the Client holds or has held a prominent public function — including but not limited to heads of state, senior politicians, senior government officials, judicial officers, senior military officials, senior executives of state-owned corporations, or senior political party officials — in the UAE or any foreign country within the last 12 months.'),
    bulletItem('3', 'I / We undertake to notify the Company immediately if the PEP status of any associated person changes at any time during the course of our business relationship.'),

    ...blank(1),

    // ═══════════════════════════════════════════════════════════════════════
    // DECLARATION 2 — SUPPLY CHAIN
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('B', 'Gold Supply Chain Declaration'),

    bulletItem('1', 'All gold and precious metals supplied by or to the Client are sourced exclusively from licensed, regulated, and legally compliant suppliers.'),
    bulletItem('2', 'All gold has been legally extracted, processed, and transported in compliance with all applicable national and international laws and regulations.'),
    bulletItem('3', 'The Client maintains adequate records of its supply chain that can be made available to regulators or the Company upon request.'),
    bulletItem('4', 'No proceeds from illegal or unlicensed mining activities are involved in any transaction with the Company.'),
    bulletItem('5', 'Appropriate due diligence is conducted on all upstream suppliers, and documentation of such diligence is maintained and available for inspection.'),

    ...blank(1),

    // ═══════════════════════════════════════════════════════════════════════
    // DECLARATION 3 — CAHRA
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('C', 'Conflict-Affected & High-Risk Areas (CAHRA) Declaration'),

    bulletItem('1', 'The Client does not source, trade in, or deal with gold or precious metals originating from conflict-affected and high-risk areas (CAHRA) as defined by the OECD Due Diligence Guidance for Responsible Supply Chains of Minerals from Conflict-Affected and High-Risk Areas.'),
    bulletItem('2', 'No transactions involve proceeds from the illegal exploitation of natural resources in conflict zones.'),
    bulletItem('3', 'Appropriate screening is conducted to identify any potential exposure to CAHRA-sourced minerals before any transaction.'),
    bulletItem('4', 'I / We undertake to immediately cease any transaction identified as having CAHRA exposure and to report the same to the Company\'s MLRO.'),

    ...blank(1),

    // ═══════════════════════════════════════════════════════════════════════
    // DECLARATION 4 — SOURCE OF FUNDS & WEALTH
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('D', 'Source of Funds & Source of Wealth Declaration'),

    bulletItem('1', 'All funds used in transactions with the Company originate from legitimate and lawful sources.'),
    bulletItem('2', 'The source of wealth of the Client and its beneficial owners is from lawful business activities, employment, or investment.'),
    bulletItem('3', 'No funds involved in any transaction are derived from any criminal activity, tax evasion, corruption, bribery, or money laundering.'),
    bulletItem('4', d.client_type !== 'individual'
        ? 'The expected source of funds for this business relationship is: trading revenues and commercial business operations.'
        : 'The expected source of funds for this business relationship is: personal savings, salary, and/or lawful investment returns.'),
    bulletItem('5', 'I / We undertake to notify the Company immediately of any material change in the source of funds or wealth during the course of this business relationship.'),

    ...blank(1),

    // ═══════════════════════════════════════════════════════════════════════
    // DECLARATION 5 — SANCTIONS
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('E', 'Sanctions Compliance Declaration'),

    bulletItem('1', 'Neither the Client nor any of its directors, shareholders, beneficial owners, or authorised signatories appears on any UAE, UN Security Council, OFAC, EU Consolidated, or UK HM Treasury financial sanctions list.'),
    bulletItem('2', 'The Client does not conduct, and has not conducted, business with any sanctioned person, entity, or jurisdiction.'),
    bulletItem('3', 'All transactions comply with UAE Cabinet Resolution No. 74 of 2020 on Terrorist Lists and all applicable UN Security Council resolutions on Targeted Financial Sanctions (TFS).'),
    bulletItem('4', 'Appropriate screening of counterparties against applicable sanctions lists is conducted before entering into any transaction.'),
    bulletItem('5', 'I / We undertake to immediately notify the Company\'s MLRO if any sanctions match is identified in relation to the Client or any associated person.'),

    ...blank(1),

    // ═══════════════════════════════════════════════════════════════════════
    // DECLARATION 6 — UBO
    // ═══════════════════════════════════════════════════════════════════════
    sectionHeading('F', 'Ultimate Beneficial Ownership (UBO) Declaration'),

    bulletItem('1', 'The ownership and control structure of the Client is fully and accurately disclosed to the Company.'),
    bulletItem('2', uboList
        ? `The following individuals are the Ultimate Beneficial Owners (UBOs) of the Client, being persons who directly or indirectly own or control 25% or more: ${uboList.join('; ')}.`
        : 'Full UBO details have been provided to the Company separately as part of the KYC documentation.'),
    bulletItem('3', 'There are no other persons who exercise ultimate effective control over the Client that have not been disclosed.'),
    bulletItem('4', 'This disclosure complies with UAE Federal Decree-Law No. 32 of 2021 on Commercial Companies and Cabinet Decision No. 58 of 2020 regarding Beneficial Owner Procedures.'),
    bulletItem('5', 'I / We undertake to notify the Company immediately of any change in the beneficial ownership or control structure of the Client.'),

    // UBO table if available
    ...(d.ubos && d.ubos.length > 0 ? [...blank(1), uboTable(d.ubos)] : []),

    divider(),

    // ── AFFIRMATION ──────────────────────────────────────────────────────────
    new Paragraph({
        children: [new TextRun({ text: 'AFFIRMATION', bold: true, size: 26, color: DARK_BLUE })],
        spacing: { before: 80, after: 120 },
    }),

    para([
        run('By signing below, I / We, '), bold(d.signatory_name || '___________'),
        run(', on behalf of '), bold(d.client_name),
        run(', hereby confirm and affirm that:'),
    ]),

    ...['A — Politically Exposed Person (PEP)',
        'B — Gold Supply Chain',
        'C — Conflict-Affected & High-Risk Areas (CAHRA)',
        'D — Source of Funds & Source of Wealth',
        'E — Sanctions Compliance',
        'F — Ultimate Beneficial Ownership (UBO)',
    ].map((s, i) => new Paragraph({
        children: [
            new TextRun({ text: '☐  ', size: 22 }),
            run(`I / We have read, understood, and agree to the declarations in Section `),
            bold(s),
            run(' set out above.'),
        ],
        spacing: { after: 100 },
        indent: { left: 300 },
    })),

    ...blank(1),

    para([
        run('I / We acknowledge that all declarations made herein are true and accurate to the best of my / our knowledge. I / We understand that providing false or misleading information is a '),
        bold('criminal offence'),
        run(' under UAE Federal Decree-Law No. 20 of 2018 and may result in criminal prosecution, regulatory sanctions, and immediate termination of the business relationship with the Company.'),
    ]),

    ...blank(1),

    para([
        run('I / We further acknowledge that '), bold(d.entity_name),
        run(' is legally required under UAE AML/CFT regulations to collect, verify, and retain this declaration as part of its Customer Due Diligence (CDD) obligations, and that this declaration may be disclosed to regulatory authorities when required by law.'),
    ]),

    divider(),

    // ── SIGNATURE BLOCK ──────────────────────────────────────────────────────
    new Paragraph({
        children: [new TextRun({ text: 'AUTHORISED SIGNATURE', bold: true, size: 24, color: DARK_BLUE })],
        spacing: { before: 80, after: 200 },
    }),

    // Signature table — client on left, MLRO on right
    new Table({
        width: { size: 100, type: WidthType.PERCENTAGE },
        borders: {
            top:     { style: BorderStyle.NONE },
            bottom:  { style: BorderStyle.NONE },
            left:    { style: BorderStyle.NONE },
            right:   { style: BorderStyle.NONE },
            insideH: { style: BorderStyle.NONE },
            insideV: { style: BorderStyle.NONE },
        },
        rows: [new TableRow({
            children: [
                // Client signature
                new TableCell({
                    width: { size: 48, type: WidthType.PERCENTAGE },
                    children: [
                        new Paragraph({ children: [run('Signature:  ___________________________')], spacing: { after: 160 } }),
                        new Paragraph({ children: [run('Name:  '), bold(d.signatory_name || '___________________________')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Title:  '), bold(d.signatory_title || 'Authorised Signatory')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Entity:  '), bold(d.client_name)], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Date:  ___________________________')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Stamp:')], spacing: { after: 400 } }),
                        new Paragraph({ children: [run('(Company stamp if applicable)')], spacing: { after: 60 } }),
                    ],
                }),
                // Spacer
                new TableCell({
                    width: { size: 4, type: WidthType.PERCENTAGE },
                    children: [new Paragraph({ children: [] })],
                }),
                // MLRO signature
                new TableCell({
                    width: { size: 48, type: WidthType.PERCENTAGE },
                    children: [
                        new Paragraph({ children: [run('Received & verified by MLRO:')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Signature:  ___________________________')], spacing: { after: 160 } }),
                        new Paragraph({ children: [run('Name:  '), bold(d.mlro_name || '___________________________')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Title:  '), bold('Money Laundering Reporting Officer')], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Entity:  '), bold(d.entity_name)], spacing: { after: 100 } }),
                        new Paragraph({ children: [run('Date:  ___________________________')], spacing: { after: 100 } }),
                    ],
                }),
            ],
        })],
    }),

    divider(),

    // ── FOOTER NOTE ──────────────────────────────────────────────────────────
    new Paragraph({
        children: [new TextRun({
            text: `CONFIDENTIAL — This declaration is made pursuant to UAE Federal Decree-Law No. 20 of 2018 on Anti-Money Laundering, Combating the Financing of Terrorism and Financing of Illegal Organisations, and the associated Cabinet Decisions. This document is to be retained by ${d.entity_name} for a minimum of five (5) years in accordance with Article 26 of the said Law.`,
            size: 18, color: '94a3b8', italics: true,
        })],
        alignment: AlignmentType.JUSTIFIED,
        spacing: { before: 100 },
    }),
];

// Filter out any nulls (e.g. uboTable when no ubos)
const filteredChildren = children.filter(Boolean);

const doc = new Document({
    sections: [{
        properties: {
            page: { margin: { top: 1080, bottom: 1080, left: 1080, right: 1080 } },
        },
        children: filteredChildren,
    }],
});

Packer.toBuffer(doc).then(buffer => {
    fs.writeFileSync(outFile, buffer);
    console.log('OK: ' + outFile);
}).catch(err => {
    console.error('ERROR: ' + err.message);
    process.exit(1);
});
