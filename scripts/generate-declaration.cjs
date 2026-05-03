// scripts/generate-declaration.cjs
// Usage: node generate-declaration.cjs <data_json_file> <output_path>

'use strict';

const fs   = require('fs');
const path = require('path');
const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, HeadingLevel,
    UnderlineType, ShadingType,
} = require('docx');

const dataFile = process.argv[2];
const outFile  = process.argv[3];

if (!dataFile || !outFile) {
    console.error('Usage: node generate-declaration.cjs <data_json_file> <output_path>');
    process.exit(1);
}

const d = JSON.parse(fs.readFileSync(dataFile, 'utf8'));

// ── Helpers ────────────────────────────────────────────────────────────────

const tealColor   = '0e4d8a';
const lightGray   = 'f1f5f9';

const heading = (text, level = 1) => new Paragraph({
    children: [new TextRun({ text, bold: true, color: tealColor, size: level === 1 ? 28 : 24 })],
    spacing: { before: 200, after: 100 },
    alignment: level === 1 ? AlignmentType.CENTER : AlignmentType.LEFT,
});

const bodyText = (text, opts = {}) => new Paragraph({
    children: [new TextRun({ text, size: 22, ...opts })],
    spacing: { after: 80 },
    alignment: AlignmentType.JUSTIFIED,
});

const bold = (text) => new TextRun({ text, bold: true, size: 22 });
const normal = (text) => new TextRun({ text, size: 22 });

const divider = () => new Paragraph({
    border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: tealColor } },
    spacing: { before: 120, after: 120 },
    children: [],
});

const blankLine = (n = 1) => Array.from({ length: n }, () => new Paragraph({ children: [new TextRun({ text: '' })], spacing: { after: 80 } }));

const infoRow = (label, value) => new TableRow({
    children: [
        new TableCell({
            children: [new Paragraph({ children: [bold(label)], spacing: { after: 40, before: 40 } })],
            width: { size: 35, type: WidthType.PERCENTAGE },
            shading: { type: ShadingType.CLEAR, fill: lightGray },
        }),
        new TableCell({
            children: [new Paragraph({ children: [normal(value || '—')], spacing: { after: 40, before: 40 } })],
            width: { size: 65, type: WidthType.PERCENTAGE },
        }),
    ],
});

const infoTable = (rows) => new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    rows: rows.map(([label, value]) => infoRow(label, value)),
    borders: {
        top:    { style: BorderStyle.SINGLE, size: 4, color: 'dde3ea' },
        bottom: { style: BorderStyle.SINGLE, size: 4, color: 'dde3ea' },
        left:   { style: BorderStyle.SINGLE, size: 4, color: 'dde3ea' },
        right:  { style: BorderStyle.SINGLE, size: 4, color: 'dde3ea' },
        insideH:{ style: BorderStyle.SINGLE, size: 2, color: 'e2e8f0' },
        insideV:{ style: BorderStyle.SINGLE, size: 2, color: 'e2e8f0' },
    },
});

const sigBlock = (name, title, company) => [
    new Paragraph({ children: [normal('__________________________________')], spacing: { before: 400, after: 40 } }),
    new Paragraph({ children: [bold(name || 'Name')], spacing: { after: 40 } }),
    new Paragraph({ children: [normal(title || 'Title')], spacing: { after: 40 } }),
    new Paragraph({ children: [normal(company || '')], spacing: { after: 40 } }),
    new Paragraph({ children: [normal('Date: __________________')], spacing: { after: 40 } }),
];

// ── Declaration content per type ────────────────────────────────────────────

function getDeclarationContent(d) {
    const clientInfo = [
        ['Entity / Individual name', d.client_name],
        ['Trade licence / ID', d.trade_license],
        ['Country', d.country],
        ['Declaration date', d.date],
    ];

    switch (d.type) {
        case 'pep':
            return {
                title: 'Declaration 1 — Politically Exposed Person (PEP)',
                intro: `I / We, the undersigned, acting on behalf of ${d.client_name} ("the Entity"), hereby declare the following in connection with our business relationship with ${d.entity_name}:`,
                items: [
                    'I/We confirm that neither the Entity nor any of its directors, shareholders, beneficial owners, or authorised signatories is a Politically Exposed Person (PEP) as defined under UAE Federal Decree-Law No. 20 of 2018.',
                    'I/We confirm that no person associated with the Entity holds or has held a prominent public function in the UAE or any foreign country within the last 12 months.',
                    'I/We acknowledge that "Politically Exposed Person" includes heads of state, senior politicians, senior government officials, judicial officers, senior military officials, senior executives of state-owned corporations, and senior political party officials.',
                    'I/We undertake to notify ' + d.entity_name + ' immediately if the PEP status of any associated person changes.',
                    'I/We understand that providing false information is a criminal offence under UAE law.',
                ],
                undertaking: `I/We acknowledge that ${d.entity_name} is required to conduct enhanced due diligence on PEPs and their close associates under the UAE AML/CFT regulatory framework.`,
                clientInfo,
            };

        case 'supply_chain':
            return {
                title: 'Declaration 2 — Gold Supply Chain',
                intro: `I / We, the undersigned, acting on behalf of ${d.client_name} ("the Entity"), hereby declare the following regarding the sourcing of gold and precious metals:`,
                items: [
                    'I/We confirm that all gold and precious metals supplied by or to the Entity are sourced exclusively from licensed and regulated suppliers.',
                    'I/We confirm that all gold has been legally extracted, processed, and transported in compliance with applicable laws.',
                    'I/We confirm that the Entity does not knowingly source gold or precious metals from conflict-affected or high-risk areas (CAHRA) as defined by the OECD Due Diligence Guidance.',
                    'I/We confirm that the Entity maintains records of its supply chain that can be made available to regulators upon request.',
                    'I/We confirm that no proceeds from illegal mining activities are involved in any transaction.',
                    'I/We undertake to conduct appropriate due diligence on all upstream suppliers and to maintain documentation of such due diligence.',
                ],
                undertaking: 'I/We acknowledge that failure to maintain a transparent and compliant supply chain may constitute a violation of UAE AML/CFT regulations and CBUAE guidelines for DNFBPs.',
                clientInfo,
            };

        case 'cahra':
            return {
                title: 'Declaration 3 — Conflict-Affected & High-Risk Areas (CAHRA)',
                intro: `I / We, the undersigned, acting on behalf of ${d.client_name} ("the Entity"), hereby declare the following regarding CAHRA sourcing:`,
                items: [
                    'I/We confirm that the Entity does not source, trade in, or deal with gold or precious metals originating from conflict-affected and high-risk areas (CAHRA).',
                    'I/We confirm that no transactions involve proceeds from the illegal exploitation of natural resources in conflict zones.',
                    'I/We confirm that the Entity is aware of and complies with the OECD Due Diligence Guidance for Responsible Supply Chains of Minerals from Conflict-Affected and High-Risk Areas.',
                    'I/We confirm that appropriate screening is conducted to identify any potential exposure to CAHRA-sourced minerals.',
                    'I/We undertake to immediately cease any transaction that is identified as having CAHRA exposure and to report the same to the MLRO.',
                ],
                undertaking: 'I/We acknowledge that trading in CAHRA-sourced minerals may constitute a violation of UAE Federal Law No. 7 of 2014 on Combating Terrorism and applicable AML legislation.',
                clientInfo,
            };

        case 'source_of_funds':
            return {
                title: 'Declaration 4 — Source of Funds & Source of Wealth',
                intro: `I / We, the undersigned, acting on behalf of ${d.client_name} ("the Entity"), hereby declare the following regarding the source of funds and wealth:`,
                items: [
                    'I/We confirm that all funds used in transactions with ' + d.entity_name + ' originate from legitimate and lawful sources.',
                    'I/We confirm that the source of wealth of the Entity and its beneficial owners is from lawful business activities, employment, or investment.',
                    'I/We confirm that no funds involved in transactions are derived from any criminal activity, tax evasion, corruption, or money laundering.',
                    'I/We acknowledge that the expected source of funds for our business relationship is: ' + (d.client_type !== 'individual' ? 'business trading revenues and commercial operations' : 'personal savings, salary, and/or investment returns') + '.',
                    'I/We undertake to notify ' + d.entity_name + ' immediately if there is any change in the source of funds or wealth.',
                ],
                undertaking: 'I/We acknowledge that ' + d.entity_name + ' is legally required to verify and record information on the source of funds and wealth as part of its Customer Due Diligence obligations under UAE AML/CFT law.',
                clientInfo,
            };

        case 'sanctions':
            return {
                title: 'Declaration 5 — Sanctions Compliance',
                intro: `I / We, the undersigned, acting on behalf of ${d.client_name} ("the Entity"), hereby declare the following regarding sanctions compliance:`,
                items: [
                    'I/We confirm that neither the Entity nor any of its directors, shareholders, beneficial owners, or authorised signatories appears on any UAE, UN, OFAC, EU, or UK sanctions list.',
                    'I/We confirm that the Entity does not conduct business with any sanctioned person, entity, or jurisdiction.',
                    'I/We confirm that all transactions comply with UAE Cabinet Resolution No. 74 of 2020 on Terrorist Lists and the implementation of UN Security Council resolutions.',
                    'I/We confirm that the Entity is aware of and complies with Targeted Financial Sanctions (TFS) requirements under UAE law.',
                    'I/We undertake to screen all counterparties against applicable sanctions lists and to immediately report any sanctions match to the MLRO.',
                    'I/We undertake to notify ' + d.entity_name + ' immediately if the sanctions status of the Entity or any associated person changes.',
                ],
                undertaking: 'I/We acknowledge that breach of sanctions obligations is a serious criminal offence under UAE Federal Decree-Law No. 20 of 2018 and may result in criminal prosecution.',
                clientInfo,
            };

        case 'ubo':
            const uboList = d.ubos && d.ubos.length > 0
                ? d.ubos.map(u => `${u.name} (${u.nationality}, ${u.ownership}% ownership)`).join('; ')
                : 'As declared separately';
            return {
                title: 'Declaration 6 — Ultimate Beneficial Ownership (UBO)',
                intro: `I / We, the undersigned, acting on behalf of ${d.client_name} ("the Entity"), hereby declare the following regarding the beneficial ownership structure:`,
                items: [
                    'I/We confirm that the following individuals are the Ultimate Beneficial Owners (UBOs) of the Entity, being persons who directly or indirectly own or control 25% or more of the Entity: ' + uboList + '.',
                    'I/We confirm that the ownership and control structure disclosed is complete, accurate, and up to date.',
                    'I/We confirm that there are no other persons who exercise ultimate effective control over the Entity.',
                    'I/We confirm that the UBO information provided complies with UAE Federal Decree-Law No. 32 of 2021 on Commercial Companies and Cabinet Decision No. 58 of 2020 regarding Beneficial Owner Procedures.',
                    'I/We undertake to notify ' + d.entity_name + ' immediately of any change in the beneficial ownership structure.',
                ],
                undertaking: 'I/We acknowledge that failure to disclose accurate UBO information is a criminal offence under UAE law and may result in regulatory sanctions.',
                clientInfo,
            };

        default:
            return { title: d.title, intro: '', items: [], undertaking: '', clientInfo };
    }
}

// ── Build document ──────────────────────────────────────────────────────────

const content = getDeclarationContent(d);

const children = [
    // Header
    new Paragraph({
        children: [new TextRun({ text: d.entity_name, bold: true, size: 32, color: tealColor })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 60 },
    }),
    new Paragraph({
        children: [new TextRun({ text: 'AML / CFT Compliance — Client Declaration', size: 22, color: '64748b' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 200 },
    }),

    divider(),

    // Title
    heading(content.title, 1),
    divider(),

    ...blankLine(1),

    // Client info table
    infoTable(content.clientInfo),

    ...blankLine(1),

    // Intro
    bodyText(content.intro, { italics: true }),

    ...blankLine(1),

    // Declarations
    new Paragraph({
        children: [bold('I / We hereby declare and confirm that:')],
        spacing: { after: 80 },
    }),

    ...content.items.map((item, i) => new Paragraph({
        children: [normal(`${i + 1}. ${item}`)],
        spacing: { after: 120 },
        indent: { left: 360 },
        alignment: AlignmentType.JUSTIFIED,
    })),

    ...blankLine(1),

    // Undertaking
    new Paragraph({
        children: [bold('Undertaking: '), normal(content.undertaking)],
        spacing: { after: 120 },
        alignment: AlignmentType.JUSTIFIED,
    }),

    divider(),

    // Signature section
    new Paragraph({
        children: [bold('Authorised Signatory — ' + d.client_name)],
        spacing: { before: 200, after: 80 },
    }),

    ...sigBlock(d.signatory_name, d.signatory_title, d.client_name),

    ...blankLine(2),

    new Paragraph({
        children: [bold('Acknowledged by MLRO — ' + d.entity_name)],
        spacing: { before: 200, after: 80 },
    }),

    ...sigBlock(d.mlro_name, d.mlro_title, d.entity_name),

    divider(),

    // Legal notice
    new Paragraph({
        children: [new TextRun({ text: 'CONFIDENTIAL — This declaration is made pursuant to UAE Federal Decree-Law No. 20 of 2018 on Anti-Money Laundering, Combating Financing of Terrorism and Financing of Illegal Organisations and related Cabinet Decisions. Submission of false information constitutes a criminal offence.', size: 18, color: '64748b', italics: true })],
        alignment: AlignmentType.JUSTIFIED,
        spacing: { before: 120, after: 80 },
    }),
];

const doc = new Document({
    sections: [{
        properties: {
            page: {
                margin: { top: 1134, bottom: 1134, left: 1134, right: 1134 },
            },
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
