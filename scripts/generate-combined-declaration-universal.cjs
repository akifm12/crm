// scripts/generate-combined-declaration-universal.cjs
// Universal combined declaration for all DNFBP sectors
// Usage: node generate-combined-declaration-universal.cjs <data_json_file> <output_path>

'use strict';

const fs  = require('fs');
const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, ShadingType,
} = require('docx');

const dataFile = process.argv[2];
const outFile  = process.argv[3];

if (!dataFile || !outFile) {
    console.error('Usage: node generate-combined-declaration-universal.cjs <data_json_file> <output_path>');
    process.exit(1);
}

const d = JSON.parse(fs.readFileSync(dataFile, 'utf8'));

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

const sectionHeading = (letter, title) => new Paragraph({
    children: [new TextRun({ text: `${letter}.  ${title}`, bold: true, size: 24, color: BLUE })],
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
            top: { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
            bottom: { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
            left: { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
            right: { style: BorderStyle.SINGLE, size: 4, color: 'cbd5e1' },
            insideH: { style: BorderStyle.SINGLE, size: 2, color: 'e2e8f0' },
            insideV: { style: BorderStyle.SINGLE, size: 2, color: 'e2e8f0' },
        },
    });
};

// ── Section definitions ──────────────────────────────────────────────────────

const sections = {

    pep: (letter, isIndividual) => [
        sectionHeading(letter, 'Politically Exposed Person (PEP) Declaration'),
        isIndividual ? [
            bulletItem('1', 'I confirm that I am not a Politically Exposed Person (PEP) as defined under UAE Federal Decree-Law No. 20 of 2018 and FATF Recommendations.'),
            bulletItem('2', 'I confirm that I do not hold, and have not held within the last 12 months, any prominent public function including but not limited to: head of state, senior politician, senior government official, judicial officer, senior military official, executive of a state-owned corporation, or senior political party official, in the UAE or any other country.'),
            bulletItem('3', 'I confirm that I am not a close family member or known close associate of any such person.'),
            bulletItem('4', 'I undertake to notify the Company immediately if my PEP status changes at any time.'),
        ] : [
            bulletItem('1', 'Neither the Client nor any of its directors, shareholders, beneficial owners, or authorised signatories is a Politically Exposed Person (PEP) as defined under UAE Federal Decree-Law No. 20 of 2018 and FATF Recommendations.'),
            bulletItem('2', 'No person associated with the Client holds or has held, within the last 12 months, any prominent public function in the UAE or any foreign country.'),
            bulletItem('3', 'I / We undertake to notify the Company immediately if the PEP status of any associated person changes at any time during the course of our business relationship.'),
        ],
        ...blank(1),
    ].flat(),

    source_of_funds: (letter, isIndividual) => [
        sectionHeading(letter, 'Source of Funds & Source of Wealth Declaration'),
        isIndividual ? [
            bulletItem('1', 'All funds used in this transaction originate from legitimate and lawful sources.'),
            bulletItem('2', 'My source of funds for this transaction is (please specify): _______________________________________________'),
            bulletItem('3', 'My source of wealth — being the origin of the total assets I own — is from lawful activities including employment, business, savings, and/or investment.'),
            bulletItem('4', 'No funds involved in this transaction are derived from any criminal activity, tax evasion, corruption, fraud, or money laundering.'),
            bulletItem('5', 'I undertake to provide supporting documentation for my source of funds and wealth upon request by the Company or any regulatory authority.'),
        ] : [
            bulletItem('1', 'All funds used in transactions with the Company originate from legitimate and lawful sources.'),
            bulletItem('2', 'The source of wealth of the Client and its beneficial owners is from lawful business activities, employment, or investment.'),
            bulletItem('3', 'No funds involved in any transaction are derived from any criminal activity, tax evasion, corruption, bribery, or money laundering.'),
            bulletItem('4', 'The expected source of funds for this business relationship is: trading revenues and commercial business operations.'),
            bulletItem('5', 'I / We undertake to notify the Company immediately of any material change in the source of funds or wealth during the course of this business relationship.'),
        ],
        ...blank(1),
    ].flat(),

    sanctions: (letter, isIndividual) => [
        sectionHeading(letter, 'Sanctions Compliance Declaration'),
        isIndividual ? [
            bulletItem('1', 'I confirm that I do not appear on any UAE, UN Security Council, OFAC, EU Consolidated, or UK HM Treasury financial sanctions list.'),
            bulletItem('2', 'I confirm that I am not subject to any Targeted Financial Sanctions (TFS) under UAE Cabinet Resolution No. 74 of 2020 or any UN Security Council resolution.'),
            bulletItem('3', 'I confirm that the funds used in this transaction are not connected to any sanctioned person, entity, or jurisdiction.'),
            bulletItem('4', 'I undertake to notify the Company immediately if my sanctions status changes at any time.'),
        ] : [
            bulletItem('1', 'Neither the Client nor any of its directors, shareholders, beneficial owners, or authorised signatories appears on any UAE, UN Security Council, OFAC, EU Consolidated, or UK HM Treasury financial sanctions list.'),
            bulletItem('2', 'The Client does not conduct, and has not conducted, business with any sanctioned person, entity, or jurisdiction.'),
            bulletItem('3', 'All transactions comply with UAE Cabinet Resolution No. 74 of 2020 on Terrorist Lists and all applicable UN Security Council resolutions on Targeted Financial Sanctions (TFS).'),
            bulletItem('4', 'I / We undertake to immediately notify the Company\'s MLRO if any sanctions match is identified in relation to the Client or any associated person.'),
        ],
        ...blank(1),
    ].flat(),

    ubo: (letter, isIndividual, data) => [
        sectionHeading(letter, 'Ultimate Beneficial Ownership (UBO) Declaration'),
        bulletItem('1', 'The ownership and control structure of the Client is fully and accurately disclosed to the Company.'),
        bulletItem('2', data.ubos && data.ubos.length > 0
            ? `The following individuals are the Ultimate Beneficial Owners (UBOs) of the Client: ${data.ubos.map(u => `${u.name} (${u.nationality}, ${u.ownership}%)`).join('; ')}.`
            : 'Full UBO details have been provided to the Company separately as part of the KYC documentation.'),
        bulletItem('3', 'There are no other persons who exercise ultimate effective control over the Client that have not been disclosed.'),
        bulletItem('4', 'This disclosure complies with UAE Federal Decree-Law No. 32 of 2021 on Commercial Companies and Cabinet Decision No. 58 of 2020 regarding Beneficial Owner Procedures.'),
        bulletItem('5', 'I / We undertake to notify the Company immediately of any change in the beneficial ownership or control structure of the Client.'),
        ...(data.ubos && data.ubos.length > 0 ? [...blank(1), uboTable(data.ubos)] : []),
        ...blank(1),
    ].flat().filter(Boolean),

    supply_chain: (letter) => [
        sectionHeading(letter, 'Gold Supply Chain Declaration'),
        bulletItem('1', 'All gold and precious metals supplied by or to the Client are sourced exclusively from licensed, regulated, and legally compliant suppliers.'),
        bulletItem('2', 'All gold has been legally extracted, processed, and transported in compliance with all applicable national and international laws and regulations.'),
        bulletItem('3', 'The Client maintains adequate records of its supply chain that can be made available to regulators or the Company upon request.'),
        bulletItem('4', 'No proceeds from illegal or unlicensed mining activities are involved in any transaction with the Company.'),
        bulletItem('5', 'Appropriate due diligence is conducted on all upstream suppliers, and documentation of such diligence is maintained and available for inspection.'),
        ...blank(1),
    ],

    cahra: (letter, isIndividual) => [
        sectionHeading(letter, 'Conflict-Affected & High-Risk Areas (CAHRA) Declaration'),
        isIndividual ? [
            bulletItem('1', 'I confirm that any gold or precious metals involved in this transaction do not originate from conflict-affected and high-risk areas (CAHRA) as defined by the OECD Due Diligence Guidance.'),
            bulletItem('2', 'I confirm that this transaction does not involve proceeds from the illegal exploitation of natural resources in any conflict zone.'),
            bulletItem('3', 'I acknowledge that trading in minerals from conflict zones is a criminal offence under UAE law and international conventions.'),
        ] : [
            bulletItem('1', 'The Client does not source, trade in, or deal with gold or precious metals originating from conflict-affected and high-risk areas (CAHRA) as defined by the OECD Due Diligence Guidance for Responsible Supply Chains of Minerals from Conflict-Affected and High-Risk Areas.'),
            bulletItem('2', 'No transactions involve proceeds from the illegal exploitation of natural resources in conflict zones.'),
            bulletItem('3', 'Appropriate screening is conducted to identify any potential exposure to CAHRA-sourced minerals before any transaction.'),
            bulletItem('4', 'I / We undertake to immediately cease any transaction identified as having CAHRA exposure and to report the same to the Company\'s MLRO.'),
        ],
        ...blank(1),
    ].flat(),

    property: (letter, isIndividual) => [
        sectionHeading(letter, 'Real Estate Transaction Declaration'),
        isIndividual ? [
            bulletItem('1', 'I confirm that the purpose of this real estate transaction is as declared to the Company and is for lawful purposes only.'),
            bulletItem('2', 'I confirm that the funds used for this transaction originate from legitimate and lawful sources and are not proceeds of any criminal activity.'),
            bulletItem('3', 'I confirm that I am the beneficial owner of the funds being used in this transaction, or I have clearly disclosed the source and owner of the funds to the Company.'),
            bulletItem('4', 'I confirm that the property being purchased/sold is not intended to be used for any unlawful purpose including money laundering or terrorist financing.'),
            bulletItem('5', 'I undertake to notify the Company immediately of any change in the purpose of the transaction or the source of funds.'),
        ] : [
            bulletItem('1', 'The Client confirms that the purpose of this real estate transaction is as declared to the Company and is for lawful purposes only.'),
            bulletItem('2', 'The Client confirms that all funds used for this transaction originate from legitimate and lawful sources and are not proceeds of any criminal activity.'),
            bulletItem('3', 'The Client confirms that it is the beneficial owner of the funds being used, or has clearly disclosed the true source and beneficial owner of the funds to the Company.'),
            bulletItem('4', 'The Client confirms that the property is not intended to be used for any unlawful purpose.'),
            bulletItem('5', 'The Client confirms compliance with all applicable UAE real estate regulations including RERA requirements and Law No. 7 of 2006 concerning Real Property Registration in the Emirate of Dubai.'),
            bulletItem('6', 'I / We undertake to notify the Company\'s MLRO immediately of any change in transaction purpose, ownership, or funding source.'),
        ],
        ...blank(1),
    ].flat(),

    beneficial_ownership: (letter) => [
        sectionHeading(letter, 'Beneficial Ownership Structure Declaration'),
        bulletItem('1', 'The Client confirms that the full ownership and control structure has been accurately disclosed to the Company, including all direct and indirect shareholders.'),
        bulletItem('2', 'The Client confirms that no nominee arrangements exist that have not been disclosed to the Company.'),
        bulletItem('3', 'The Client confirms that the company is not being used as a vehicle for concealing the identity of any beneficial owner.'),
        bulletItem('4', 'The Client confirms that all services requested from the Company are for legitimate business purposes only and not to facilitate the concealment of beneficial ownership.'),
        bulletItem('5', 'The Client complies with UAE Federal Decree-Law No. 32 of 2021 on Commercial Companies regarding beneficial owner disclosure requirements.'),
        bulletItem('6', 'I / We undertake to notify the Company immediately of any change in the beneficial ownership or control structure.'),
        ...blank(1),
    ],

    client_funds: (letter) => [
        sectionHeading(letter, 'Client Funds Handling Declaration'),
        bulletItem('1', 'The Client confirms that any funds handled on behalf of third parties are held in segregated client accounts and are clearly distinguishable from the Client\'s own funds.'),
        bulletItem('2', 'The Client confirms that all client monies are handled in accordance with applicable UAE professional and regulatory standards.'),
        bulletItem('3', 'The Client confirms that it does not commingle client funds with its own operating funds.'),
        bulletItem('4', 'The Client confirms that all transactions involving client funds are properly documented and can be accounted for at any time.'),
        bulletItem('5', 'The Client confirms that it does not accept or hold funds from clients where the source of funds cannot be verified or appears suspicious.'),
        bulletItem('6', 'I / We undertake to notify the Company\'s MLRO immediately if any suspicion arises in relation to client funds held or managed.'),
        ...blank(1),
    ],
};

// ── Sector/type to declaration list mapping ──────────────────────────────────

const declarationMap = {
    gold: {
        corporate: ['pep','supply_chain','cahra','source_of_funds','sanctions','ubo'],
        individual: ['pep','source_of_funds','sanctions','cahra'],
    },
    real_estate: {
        corporate: ['pep','source_of_funds','sanctions','ubo','property'],
        individual: ['pep','source_of_funds','sanctions','property'],
    },
    company_services: {
        corporate: ['pep','source_of_funds','sanctions','ubo','beneficial_ownership'],
        individual: ['pep','source_of_funds','sanctions'],
    },
    accounting: {
        corporate: ['pep','source_of_funds','sanctions','ubo','client_funds'],
        individual: ['pep','source_of_funds','sanctions'],
    },
    other: {
        corporate: ['pep','source_of_funds','sanctions','ubo'],
        individual: ['pep','source_of_funds','sanctions'],
    },
};

const sectionLabels = {
    pep:                 'Politically Exposed Person (PEP)',
    supply_chain:        'Gold Supply Chain',
    cahra:               'Conflict-Affected & High-Risk Areas (CAHRA)',
    source_of_funds:     'Source of Funds & Source of Wealth',
    sanctions:           'Sanctions Compliance',
    ubo:                 'Ultimate Beneficial Ownership (UBO)',
    property:            'Real Estate Transaction',
    beneficial_ownership:'Beneficial Ownership Structure',
    client_funds:        'Client Funds Handling',
};

const letters = ['A','B','C','D','E','F','G','H','I'];

// ── Build document ────────────────────────────────────────────────────────────

const sector      = d.sector || 'gold';
const isIndividual = d.client_type === 'individual';
const typeKey      = isIndividual ? 'individual' : 'corporate';
const declList     = declarationMap[sector]?.[typeKey] || declarationMap.other[typeKey];

const clientInfo = isIndividual ? [
    ['Full name',              d.client_name],
    ['Passport / Emirates ID', d.trade_license || '—'],
    ['Nationality',            d.country || '—'],
    ['Declaration date',       d.date],
] : [
    ['Entity name',            d.client_name],
    ['Trade licence / Reg. no.',d.trade_license || '—'],
    ['Country',                d.country || '—'],
    ['Authorised signatory',   d.signatory_name || '—'],
    ['Declaration date',       d.date],
];

const docTitle = isIndividual ? 'INDIVIDUAL CLIENT DECLARATION' : 'COMBINED CLIENT DECLARATION';
const sectorLabels = {
    gold: 'Precious Metals & Stones',
    real_estate: 'Real Estate',
    company_services: 'Company Service Providers',
    accounting: 'Accountants & Auditors',
    other: 'DNFBP',
};

// Build section content
const sectionContent = [];
declList.forEach((declKey, idx) => {
    const letter = letters[idx];
    const fn = sections[declKey];
    if (fn) {
        const content = fn(letter, isIndividual, d);
        sectionContent.push(...(Array.isArray(content) ? content : [content]));
    }
});

// Build affirmation checkboxes
const affirmationItems = declList.map((declKey, idx) =>
    new Paragraph({
        children: [
            new TextRun({ text: '☐  ', size: 22 }),
            run(`I / We have read, understood, and agree to the declarations in Section `),
            bold(`${letters[idx]} — ${sectionLabels[declKey] || declKey}`),
            run('.'),
        ],
        spacing: { after: 100 },
        indent: { left: 300 },
    })
);

const children = [
    // Header
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
        children: [new TextRun({ text: docTitle, bold: true, size: 30, color: DARK_BLUE })],
        alignment: AlignmentType.CENTER,
        spacing: { before: 80, after: 40 },
    }),
    new Paragraph({
        children: [run(`${sectorLabels[sector] || 'DNFBP'} — Anti-Money Laundering & Counter-Financing of Terrorism`, { italics: true, color: '64748b' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 40 },
    }),
    new Paragraph({
        children: [run('Pursuant to UAE Federal Decree-Law No. 20 of 2018', { size: 20, color: '94a3b8' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 200 },
    }),

    divider(),

    para([bold('CLIENT DETAILS', { color: BLUE, size: 20 })], { spacing: { after: 80 } }),
    infoTable(clientInfo),
    ...blank(1),

    para(isIndividual ? [
        run('I, '), bold(d.client_name),
        run(`, hereby make the following declarations to ${d.entity_name} ("the Company") in connection with my transaction, in compliance with UAE AML/CFT legislation:`),
    ] : [
        run('I / We, '), bold(d.signatory_name || '___________'),
        run(', acting as authorised signatory on behalf of '), bold(d.client_name),
        run(`, ("the Client"), hereby make the following declarations to ${d.entity_name} ("the Company") in compliance with UAE AML/CFT legislation:`),
    ]),

    divider(),

    // All sections
    ...sectionContent,

    divider(),

    // Affirmation
    new Paragraph({
        children: [new TextRun({ text: 'AFFIRMATION', bold: true, size: 26, color: DARK_BLUE })],
        spacing: { before: 80, after: 120 },
    }),

    para([run(isIndividual
        ? 'By signing below, I confirm that:'
        : 'By signing below, I / We confirm that:'
    )]),

    ...affirmationItems,
    ...blank(1),

    para([
        run(isIndividual ? 'I confirm' : 'I / We confirm'),
        run(' that all declarations made herein are '),
        bold('true, accurate, and complete'),
        run(' to the best of my / our knowledge. I / We understand that providing false or misleading information is a '),
        bold('criminal offence'),
        run(' under UAE Federal Decree-Law No. 20 of 2018 and may result in criminal prosecution, regulatory sanctions, and immediate termination of the business relationship with the Company.'),
    ]),

    ...blank(1),

    para([
        run('I / We further acknowledge that '), bold(d.entity_name),
        run(' is legally required under UAE AML/CFT regulations to collect, verify, and retain this declaration as part of its Customer Due Diligence (CDD) obligations.'),
    ]),

    divider(),

    // Signature block
    new Paragraph({
        children: [new TextRun({ text: 'AUTHORISED SIGNATURE', bold: true, size: 24, color: DARK_BLUE })],
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
                        new Paragraph({ children: [run('Name:  '), bold(d.signatory_name || d.client_name || '___________________________')], spacing: { after: 100 } }),
                        ...(isIndividual ? [
                            new Paragraph({ children: [run('Passport / EID:  '), run(d.trade_license || '_______________')], spacing: { after: 100 } }),
                        ] : [
                            new Paragraph({ children: [run('Title:  '), bold(d.signatory_title || 'Authorised Signatory')], spacing: { after: 100 } }),
                            new Paragraph({ children: [run('Entity:  '), bold(d.client_name)], spacing: { after: 100 } }),
                        ]),
                        new Paragraph({ children: [run('Date:  ___________________________')], spacing: { after: 100 } }),
                        ...(!isIndividual ? [new Paragraph({ children: [run('Stamp:')], spacing: { after: 400 } })] : []),
                    ],
                }),
                new TableCell({
                    width: { size: 4, type: WidthType.PERCENTAGE },
                    children: [new Paragraph({ children: [] })],
                }),
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

    new Paragraph({
        children: [new TextRun({
            text: `CONFIDENTIAL — This declaration is made pursuant to UAE Federal Decree-Law No. 20 of 2018 on Anti-Money Laundering, Combating the Financing of Terrorism and Financing of Illegal Organisations, and the associated Cabinet Decisions. This document is to be retained by ${d.entity_name} for a minimum of five (5) years in accordance with Article 26 of the said Law.`,
            size: 18, color: '94a3b8', italics: true,
        })],
        alignment: AlignmentType.JUSTIFIED,
        spacing: { before: 100 },
    }),
].filter(Boolean);

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
