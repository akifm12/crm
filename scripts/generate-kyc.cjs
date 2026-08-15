'use strict';

// Usage: node generate-kyc.cjs <data.json> <out.docx>

const fs = require('fs');
const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, ShadingType,
    Header, Footer, PageNumber, PageBreak,
} = require('docx');

const dataFile = process.argv[2];
const outFile  = process.argv[3];
if (!dataFile || !outFile) { console.error('Usage: generate-kyc.cjs <data.json> <out.docx>'); process.exit(1); }

const d   = JSON.parse(fs.readFileSync(dataFile, 'utf8'));
const val = v => (v !== null && v !== undefined && String(v).trim()) ? String(v).trim() : '—';

// ── Units ─────────────────────────────────────────────────────────────────────
const FULL = 9026;   // A4 text width: 11906 DXA − 2×1440 DXA (1-inch margins)
const pt   = n => n * 20;
const hp   = n => n * 2;   // half-points for font size

// ── Borders ───────────────────────────────────────────────────────────────────
const NONE  = { style: BorderStyle.NONE,   size: 0, color: 'FFFFFF' };
const LIGHT = { style: BorderStyle.SINGLE, size: 4, color: 'D0D0D0' };
const DARK  = { style: BorderStyle.SINGLE, size: 6, color: '2E4A7A' };
const allBorders = (b) => ({ top: b, bottom: b, left: b, right: b });

// ── Typography ────────────────────────────────────────────────────────────────
const run = (text, opts = {}) =>
    new TextRun({ text: String(text ?? ''), size: hp(10.5), font: 'Calibri', ...opts });

const para = (children, opts = {}) =>
    new Paragraph({
        children: Array.isArray(children) ? children : [children],
        spacing: { after: pt(4), ...opts.spacing },
        ...opts,
    });

const gap = (pts = 8) => para(run(''), { spacing: { after: pt(pts) } });

// ── Section heading (single-cell table = exact same width as all data tables) ─
let _sectionNum = 0;
const sectionHeading = (title, skipNum = false) => {
    if (!skipNum) _sectionNum++;
    const label = skipNum ? title : `${_sectionNum}. ${title}`;
    return [
        gap(6),
        new Table({
            width: { size: FULL, type: WidthType.DXA },
            columnWidths: [FULL],
            borders: { ...allBorders(NONE), insideH: NONE, insideV: NONE },
            rows: [new TableRow({ children: [new TableCell({
                width: { size: FULL, type: WidthType.DXA },
                shading: { fill: '2E4A7A', type: ShadingType.CLEAR },
                borders: allBorders(NONE),
                margins: { top: pt(4), bottom: pt(4), left: pt(8), right: pt(8) },
                children: [para(
                    run(label.toUpperCase(), { bold: true, color: 'FFFFFF', size: hp(10) }),
                    { spacing: { after: 0 } }
                )],
            })]})],
        }),
    ];
};

// sub-heading (lighter, used inside a section)
const subHeading = (text) => [
    gap(4),
    new Paragraph({
        children: [run(text, { bold: true, size: hp(10), color: '2E4A7A' })],
        spacing: { after: pt(2) },
        border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: '2E4A7A' } },
    }),
];

// ── Two-column info table ─────────────────────────────────────────────────────
const LW = 2700;
const VW = FULL - LW;

const infoRow = (label, value, shade) => new TableRow({ children: [
    new TableCell({
        width: { size: LW, type: WidthType.DXA },
        shading: { fill: shade ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
        borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: LIGHT },
        margins: { top: pt(2.5), bottom: pt(2.5), left: pt(7), right: pt(7) },
        children: [para(run(label, { bold: true, size: hp(10), color: '374151' }), { spacing: { after: 0 } })],
    }),
    new TableCell({
        width: { size: VW, type: WidthType.DXA },
        shading: { fill: shade ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
        borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: NONE },
        margins: { top: pt(2.5), bottom: pt(2.5), left: pt(7), right: pt(7) },
        children: [para(run(val(value)), { spacing: { after: 0 } })],
    }),
]});

const infoTable = (rows) => new Table({
    width: { size: FULL, type: WidthType.DXA },
    columnWidths: [LW, VW],
    borders: { top: DARK, bottom: DARK, left: DARK, right: DARK, insideH: LIGHT, insideV: NONE },
    rows: rows.filter(Boolean),
});

// ── Multi-column grid table ───────────────────────────────────────────────────
const gridTable = (headers, widths, rows) => new Table({
    width: { size: FULL, type: WidthType.DXA },
    columnWidths: widths,
    borders: { top: DARK, bottom: DARK, left: DARK, right: DARK, insideH: LIGHT, insideV: LIGHT },
    rows: [
        new TableRow({ children: headers.map((h, i) => new TableCell({
            width: { size: widths[i], type: WidthType.DXA },
            shading: { fill: '4A6FA5', type: ShadingType.CLEAR },
            borders: allBorders(NONE),
            margins: { top: pt(3), bottom: pt(3), left: pt(7), right: pt(7) },
            children: [para(run(h, { bold: true, color: 'FFFFFF', size: hp(9.5) }), { spacing: { after: 0 } })],
        })) }),
        ...rows.map((cells, ri) => new TableRow({ children: cells.map((cell, ci) => new TableCell({
            width: { size: widths[ci], type: WidthType.DXA },
            shading: { fill: ri % 2 === 1 ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: NONE },
            margins: { top: pt(2.5), bottom: pt(2.5), left: pt(7), right: pt(7) },
            children: [para(run(val(cell), { size: hp(9.5) }), { spacing: { after: 0 } })],
        })) })),
    ],
});

// ── Questionnaire table ───────────────────────────────────────────────────────
// Renders a table of questions with Yes/No tick responses, or free-text responses.
// answer: true = Yes, false = No, string = free text, null = blank line

const CHECK = '☑'; // ☑
const BOX   = '☐'; // ☐

const QW_Q = 7326;
const QW_R = 1700;

const qTableHeader = () => new TableRow({ children: [
    new TableCell({
        width: { size: QW_Q, type: WidthType.DXA },
        shading: { fill: '4A6FA5', type: ShadingType.CLEAR },
        borders: allBorders(NONE),
        margins: { top: pt(3), bottom: pt(3), left: pt(7), right: pt(7) },
        children: [para(run('QUESTION', { bold: true, color: 'FFFFFF', size: hp(9.5) }), { spacing: { after: 0 } })],
    }),
    new TableCell({
        width: { size: QW_R, type: WidthType.DXA },
        shading: { fill: '4A6FA5', type: ShadingType.CLEAR },
        borders: allBorders(NONE),
        margins: { top: pt(3), bottom: pt(3), left: pt(7), right: pt(7) },
        children: [para(run('RESPONSE', { bold: true, color: 'FFFFFF', size: hp(9.5) }), { spacing: { after: 0 } })],
    }),
]});

const qRow = (question, answer, shade) => {
    // Build response cell content
    let responseRuns;
    if (answer === true || answer === false) {
        const y = answer === true;
        responseRuns = [
            run((y ? CHECK : BOX) + ' Yes', { size: hp(10), bold: y }),
            run('     ', { size: hp(10) }),
            run((!y ? CHECK : BOX) + ' No',  { size: hp(10), bold: !y }),
        ];
    } else if (typeof answer === 'string' && answer) {
        responseRuns = [run(answer, { size: hp(10) })];
    } else if (answer === null || answer === undefined) {
        // unanswered — show both boxes unchecked
        responseRuns = [
            run(BOX + ' Yes', { size: hp(10) }),
            run('     ', { size: hp(10) }),
            run(BOX + ' No',  { size: hp(10) }),
        ];
    } else {
        responseRuns = [run('', { size: hp(10) })];
    }

    return new TableRow({ children: [
        new TableCell({
            width: { size: QW_Q, type: WidthType.DXA },
            shading: { fill: shade ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: LIGHT },
            margins: { top: pt(3), bottom: pt(3), left: pt(7), right: pt(7) },
            children: [para(run(question, { size: hp(10) }), { spacing: { after: 0 } })],
        }),
        new TableCell({
            width: { size: QW_R, type: WidthType.DXA },
            shading: { fill: shade ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: NONE },
            margins: { top: pt(3), bottom: pt(3), left: pt(7), right: pt(7) },
            children: [para(responseRuns, { spacing: { after: 0 } })],
        }),
    ]});
};

const qTable = (rows) => new Table({
    width: { size: FULL, type: WidthType.DXA },
    columnWidths: [QW_Q, QW_R],
    borders: { top: DARK, bottom: DARK, left: DARK, right: DARK, insideH: LIGHT, insideV: NONE },
    rows: [qTableHeader(), ...rows.filter(Boolean)],
});

// ── Choice row (multi-option response — each option on its own line) ──────────
const qChoiceRow = (question, options, selected, shade) => {
    const optionParas = options.map((opt, i) => {
        const checked = Array.isArray(selected) ? selected.includes(opt) : selected === opt;
        return para(
            run((checked ? CHECK : BOX) + '  ' + opt, { size: hp(10), bold: checked }),
            { spacing: { after: i < options.length - 1 ? pt(2) : 0 } }
        );
    });
    return new TableRow({ children: [
        new TableCell({
            width: { size: QW_Q, type: WidthType.DXA },
            shading: { fill: shade ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: LIGHT },
            margins: { top: pt(3), bottom: pt(3), left: pt(7), right: pt(7) },
            children: [para(run(question, { size: hp(10) }), { spacing: { after: 0 } })],
        }),
        new TableCell({
            width: { size: QW_R, type: WidthType.DXA },
            shading: { fill: shade ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: NONE },
            margins: { top: pt(3), bottom: pt(3), left: pt(7), right: pt(7) },
            children: optionParas,
        }),
    ]});
};

// ── Declaration block ─────────────────────────────────────────────────────────
const declarationBlock = (title, text) => [
    ...subHeading(title),
    para(run(text, { size: hp(10) }), { spacing: { after: pt(6) } }),
];

// ── Signature columns ─────────────────────────────────────────────────────────
const sigCol = (name, role, w) => new TableCell({
    width: { size: w, type: WidthType.DXA },
    borders: allBorders(NONE),
    margins: { top: 0, bottom: 0, left: pt(4), right: pt(16) },
    children: [
        para(run(''), { spacing: { before: pt(50), after: 0 }, border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: '000000' } } }),
        para(run(name || ' ', { bold: true, size: hp(10) }), { spacing: { after: pt(1) } }),
        para(run(role, { size: hp(9), color: '6B7280' }), { spacing: { after: 0 } }),
    ],
});

// ── Questionnaire answer helper ───────────────────────────────────────────────
// Reads from d.questionnaire if present, otherwise uses supplied default.
const qa = (key, def) => {
    if (d.questionnaire && d.questionnaire[key] !== undefined) return d.questionnaire[key];
    return def;
};

// ── Build document ────────────────────────────────────────────────────────────
const isCorp   = d.is_corp;
const body     = [];
const sector   = (d.sector || 'gold').toLowerCase();
const isGold   = sector === 'gold' || sector === 'bullion' || sector === 'precious_metals';

// Sanctions lists
const sanctionsList = [
    'UN Security Council Consolidated List',
    'UAE Cabinet Resolution No. 83 — Terrorist Designations List',
    'OFAC SDN & Consolidated Sanctions List (USA)',
    'UK HM Treasury Financial Sanctions List',
    'EU Consolidated Sanctions List',
    'FATF High-Risk and Monitored Jurisdictions',
    'Interpol Wanted Persons (Red Notices)',
    'UAE Central Bank — Designated Persons List',
    'Basel AML Index',
];

// ════════════════════════════════════════════════════════════════════════════════
// TITLE BLOCK
// ════════════════════════════════════════════════════════════════════════════════
body.push(
    new Paragraph({
        children: [run(d.tenant_name, { bold: true, size: hp(18) })],
        alignment: AlignmentType.CENTER,
        spacing: { after: pt(3) },
    }),
    new Paragraph({
        children: [run('Client Due Diligence Pack', { size: hp(14), color: '2E4A7A' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: pt(2) },
    }),
    new Paragraph({
        children: [run(`${d.ref}  ·  ${d.generated}`, { size: hp(9), color: '9CA3AF' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 0 },
        border: { bottom: { style: BorderStyle.SINGLE, size: 12, color: '2E4A7A' } },
    }),
    gap(10),
);

// Client summary strip
body.push(
    infoTable([
        infoRow('Client',          val(d.client_name)),
        infoRow('Type',            val(d.client_type_label),             true),
        infoRow('Status',          val(d.status)),
        infoRow('CDD Level',       (d.cdd_type || 'Standard').toUpperCase(), true),
        infoRow('Risk Rating',     (d.risk_rating || 'Unrated').toUpperCase()),
        d.next_review_date ? infoRow('Next Review Date', val(d.next_review_date), true) : null,
    ]),
    gap(6),
);

// ════════════════════════════════════════════════════════════════════════════════
// SECTION 1 — CLIENT IDENTIFICATION
// ════════════════════════════════════════════════════════════════════════════════
body.push(...sectionHeading('Client Identification'));

if (isCorp) {
    body.push(
        infoTable([
            infoRow('Company Name',              val(d.company_name)),
            infoRow('Legal Form',                val(d.legal_form),                 true),
            infoRow('Country of Incorporation',  val(d.country_of_incorporation)),
            infoRow('Trade License No.',         val(d.trade_license_no),           true),
            infoRow('Trade License Validity',    val(d.trade_license_validity)),
            infoRow('VAT / TRN',                 val(d.trn_number),                 true),
            infoRow('Ejari No.',                 val(d.ejari_number)),
            d.ejari_expiry ? infoRow('Ejari Expiry',     val(d.ejari_expiry),       true) : null,
            infoRow('Business Activity',         val(d.business_activity)),
            infoRow('Registered Address',        val(d.registered_address),         true),
            infoRow('Email',                     val(d.email)),
            infoRow('Phone',                     val(d.phone),                      true),
            d.website ? infoRow('Website',       val(d.website)) : null,
        ]),
        gap(6),
    );
} else {
    body.push(
        infoTable([
            infoRow('Full Name',         val(d.full_name)),
            d.name_arabic ? infoRow('Name (Arabic)', val(d.name_arabic),            true) : null,
            infoRow('Nationality',       val(d.nationality)),
            infoRow('Date of Birth',     val(d.dob),                                true),
            infoRow('Passport No.',      val(d.passport_number)),
            infoRow('Passport Expiry',   val(d.passport_expiry),                    true),
            infoRow('Emirates ID',       val(d.eid_number)),
            d.eid_expiry ? infoRow('Emirates ID Expiry', val(d.eid_expiry),         true) : null,
            infoRow('Occupation',        val(d.occupation)),
            infoRow('Employer',          val(d.employer_name),                      true),
            infoRow('PEP Status',        d.pep_status ? 'Yes — Politically Exposed Person' : 'No'),
            infoRow('Email',             val(d.email),                              true),
            infoRow('Phone',             val(d.phone)),
        ]),
        gap(6),
    );
}

// ════════════════════════════════════════════════════════════════════════════════
// SECTION 2 — AUTHORIZED SIGNATORIES (corporate only)
// ════════════════════════════════════════════════════════════════════════════════
if (isCorp && d.signatories && d.signatories.length) {
    const sw = [1950, 1400, 1250, 1100, 1050, 1076, 1200]; // sums to 9026
    body.push(
        ...sectionHeading('Authorized Signatories'),
        gridTable(
            ['Full Name', 'Position', 'Nationality', 'Date of Birth', 'Passport No.', 'Passport Expiry', 'Emirates ID'],
            sw,
            d.signatories.map(s => [s.full_name, s.position, s.nationality, s.dob, s.passport_number, s.passport_expiry, s.eid_number]),
        ),
        gap(6),
    );
}

// ════════════════════════════════════════════════════════════════════════════════
// SECTION 3 — SHAREHOLDERS & UBOs (corporate only)
// ════════════════════════════════════════════════════════════════════════════════
if (isCorp && d.shareholders && d.shareholders.length) {
    const shw = [2250, 1000, 1200, 900, 650, 1626, 1400]; // sums to 9026
    body.push(
        ...sectionHeading('Shareholders & Ultimate Beneficial Owners'),
        gridTable(
            ['Name', 'Type', 'Nationality', 'Ownership %', 'UBO', 'Passport / ID', 'Date of Birth'],
            shw,
            d.shareholders.map(s => [
                s.name, s.shareholder_type, s.nationality,
                s.ownership_percentage ? parseFloat(s.ownership_percentage).toFixed(1) + '%' : '—',
                s.is_ubo ? 'Yes' : 'No',
                s.passport_number || s.eid_number, s.dob,
            ]),
        ),
        gap(6),
    );
}

// ════════════════════════════════════════════════════════════════════════════════
// SECTION 4 — AML RISK PROFILE & BUSINESS RELATIONSHIP
// ════════════════════════════════════════════════════════════════════════════════
body.push(
    ...sectionHeading('AML Risk Profile & Business Relationship'),
    infoTable([
        infoRow('Source of Funds',           val(d.source_of_funds_label)),
        infoRow('Source of Wealth',          val(d.source_of_wealth_label),          true),
        infoRow('Purpose of Relationship',   val(d.purpose_of_relationship_label)),
        infoRow('Expected Monthly Volume',   d.expected_monthly_volume ? 'AED ' + Number(d.expected_monthly_volume).toLocaleString() : '—', true),
        infoRow('Expected Frequency',        d.expected_monthly_frequency ? d.expected_monthly_frequency + ' transactions / month' : '—'),
        infoRow('Countries Involved',        val(d.countries_involved),               true),
    ]),
    gap(6),
);

// ════════════════════════════════════════════════════════════════════════════════
// SECTION 5 — RISK ASSESSMENT
// ════════════════════════════════════════════════════════════════════════════════
body.push(
    ...sectionHeading('Risk Assessment'),
    infoTable([
        infoRow('Risk Rating',   (d.risk_rating || 'Unrated').toUpperCase()),
        d.risk_assessed_at ? infoRow('Assessed On',  val(d.risk_assessed_at),   true) : null,
        d.risk_assessed_by ? infoRow('Assessed By',  val(d.risk_assessed_by))         : null,
        d.next_review_date ? infoRow('Next Review',  val(d.next_review_date),   true) : null,
        d.risk_notes       ? infoRow('Notes',        val(d.risk_notes))               : null,
    ]),
    gap(6),
);

// ════════════════════════════════════════════════════════════════════════════════
// SECTION 6 — SANCTIONS & AML SCREENING
// ════════════════════════════════════════════════════════════════════════════════
body.push(
    ...sectionHeading('Sanctions & AML Screening'),
    infoTable([
        infoRow('Screening Result',     (d.screening_status || 'Not Screened').toUpperCase()),
        d.screening_date      ? infoRow('Last Screened',  val(d.screening_date),       true) : null,
        d.screening_reference ? infoRow('Reference',      val(d.screening_reference))         : null,
    ]),
    gap(4),
    para(run('The following lists and databases were screened:', { bold: true, size: hp(10) }), { spacing: { after: pt(3) } }),
    ...sanctionsList.map(item =>
        para([run('•  ', { size: hp(10), color: '2E4A7A' }), run(item, { size: hp(10) })],
            { spacing: { after: pt(2) }, indent: { left: pt(10) } })
    ),
    gap(6),
);

// ════════════════════════════════════════════════════════════════════════════════
// SECTION 7 — COMPLIANCE QUESTIONNAIRE (corporate only)
// ════════════════════════════════════════════════════════════════════════════════
if (isCorp) {
    body.push(new Paragraph({ children: [new PageBreak()], spacing: { after: 0 } }));
    body.push(...sectionHeading('Compliance Questionnaire'));

    // ── Part A: Entity Undertaking ────────────────────────────────────────────
    body.push(...subHeading('A. Entity Undertaking'));

    const euQs = [
        ['eu_no_regulator_action',   'We confirm that our organization has not received communication from law enforcement or regulatory authorities concerning non-compliance with the laws and regulations of the UAE or any other international regulator.',         null],
        ['eu_aml_compliant',         'We confirm that our organization has complied with all UAE Federal laws or regulations relating to AML/CFT and are not aware of any violations or possible violations of these laws and regulations.',                       null],
        ['eu_no_pep_directors',      'We confirm that our organization\'s shareholders, directors, officers, or senior employees are not senior officials in government, political organizations, or government-owned organizations, nor relatives or close associates of such officials.', null],
        ['eu_no_litigation',         'We confirm that our organization is not currently a party to any litigation that is in progress or expected.',                                                                                                                null],
        ['eu_no_disciplinary',       'We confirm that our organization has not been subject to any disciplinary action by a court, professional body, or regulatory agency within the past five years.',                                                           null],
        ['eu_anti_bribery',          'We confirm that our organization upholds anti-bribery and anti-corruption standards and has implemented policies to prevent unethical behavior.',                                                                            null],
        ['eu_code_of_conduct',       'We confirm that our organization has an internal code of conduct to guide employees in ethical and lawful behavior.',                                                                                                        null],
        ['eu_compliance_audits',     'We confirm that our organization conducts regular compliance audits to ensure adherence to relevant laws and regulations.',                                                                                                   null],
        ['eu_transparency',          'We confirm that our organization maintains transparency in its operations and accurately reports information to stakeholders and regulatory authorities.',                                                                     null],
        ['eu_human_rights',          'We confirm that our organization is committed to conducting its business in compliance with human rights, labor, and environmental standards.',                                                                               null],
        ['eu_remediation_policy',    'We confirm that our organization has a policy of promptly addressing and correcting any instances of non-compliance identified within its operations.',                                                                       null],
        ['eu_cooperates',            'We confirm that our organization actively cooperates with regulatory authorities and law enforcement agencies during investigations or inquiries.',                                                                            null],
    ];

    body.push(
        qTable(euQs.map(([key, q, def], i) => qRow(q, qa(key, def), i % 2 === 1))),
        gap(6),
    );

    // ── Part B: Due Diligence & AML/CFT Program ───────────────────────────────
    body.push(...subHeading('B. AML/CFT & Compliance Program'));

    const ddQs = [
        isGold ? ['dd_oecd',            'Does your organization comply with the OECD Due Diligence Guidance for Responsible Supply Chains of Minerals from Conflict-Affected and High-Risk Areas?', null]  : null,
        isGold ? ['dd_lbma_dmcc',       'Is your organization complying with LBMA, DMCC, or MOE industry initiatives regarding responsible sourcing of precious metals?',                            null]  : null,
        ['dd_subject_to_aml',           'Is your organization subject to Anti-Money Laundering / Combating the Financing of Terrorism (AML/CFT) laws and regulations?',                             null],
        ['dd_aml_program',              'Has your organization established an AML/CFT conformity program containing policies and procedures in accordance with applicable laws and international standards?', null],
        ['dd_anti_bribery_policy',      'Does your organization have an anti-bribery and anti-corruption policy in place?',                                                                          null],
        ['dd_bribery_charges',          'Has your organization or its senior management ever been charged with violation of applicable anti-bribery laws or regulations?',                           null],
        ['dd_data_protection_policy',   'Does your organization have a Data Protection Policy?',                                                                                                     null],
        ['dd_dpo',                      'Does your organization have a designated Data Protection Officer (DPO)?',                                                                                   null],
        ['dd_secure_data',              'Does your organization maintain a secure data storage system or information management system?',                                                             null],
        ['dd_whistleblowing',           'Does your organization have a whistleblowing mechanism through which employees may raise concerns about unethical or illegal activity?',                    null],
        ['dd_compliance_officer',       'Does your organization have a designated Compliance Officer responsible for AML/CFT matters?',                                                              null],
        ['dd_tfs_program',              'Has your organization implemented a Targeted Financial Sanctions (TFS) Compliance Program to identify sanctioned individuals and entities?',                null],
        ['dd_risk_assessments',         'Does your organization conduct regular risk assessments to identify potential compliance vulnerabilities?',                                                  null],
        ['dd_customer_risk',            'Does your organization classify customers by risk level (low, medium, or high)?',                                                                           null],
        ['dd_background_checks',        'Does your organization have a policy for conducting background checks on employees and key personnel?',                                                     null],
        ['dd_policy_updates',           'Does your organization regularly review and update its compliance policies and procedures to reflect changes in legislation?',                               null],
        ['dd_training',                 'Does your organization provide ongoing AML/CFT compliance training to its employees?',                                                                      null],
    ].filter(Boolean);

    body.push(
        qTable(ddQs.map(([key, q, def], i) => qRow(q, qa(key, def), i % 2 === 1))),
        gap(6),
    );

    // ════════════════════════════════════════════════════════════════════════════
    // SECTION 8 — COUNTERPARTY & BUSINESS PROFILE (corporate only)
    // ════════════════════════════════════════════════════════════════════════════
    body.push(...sectionHeading('Counterparty & Business Profile'));

    const cpProfile = qa('cp_profile', null);   // 'Entities' | 'Individuals' | 'Both'
    const cpMetals  = qa('cp_metals',  isGold ? 'Gold' : null);

    const cpQs = [
        qChoiceRow('What is the profile of your major counterparties?', ['Entities', 'Individuals', 'Both'], cpProfile, false),
        qRow('Does the company have any smelting or refining facilities?',                                             qa('cp_smelting',     null), true),
        qRow('Does the company have its own manufacturing facilities?',                                                qa('cp_manufacturing',null), false),
        isGold ? qRow('Does the company produce its own jewelry?',                                                     qa('cp_jewelry',      null), true)  : null,
        isGold ? qRow('What metals does the company send for refining?',                                               cpMetals,                   false) : null,
        isGold ? qRow('Does the company work directly with mines, refineries, or other third-party suppliers?',        qa('cp_mines',        null), true)  : null,
        qRow('Do you have offices or partnerships outside the UAE?',                                                   qa('cp_overseas',     null), false),
        qRow('Do you verify and obtain complete export documentation from your suppliers, including source of origin?', qa('cp_export_docs',  null), true),
        qRow('Does your company offer additional services such as storage, transportation, or product certification?',  qa('cp_services',     null), false),
        qRow('Are your typical transactions high-value?',                                                              qa('cp_high_value',   null), true),
        qRow('Do you outsource any of your operations to third parties?',                                              qa('cp_outsourcing',  null), false),
    ].filter(Boolean);

    body.push(qTable(cpQs));
}

// ════════════════════════════════════════════════════════════════════════════════
// SECTION 8 or 9 — DECLARATIONS & UNDERTAKINGS
// ════════════════════════════════════════════════════════════════════════════════
body.push(new Paragraph({ children: [new PageBreak()], spacing: { after: 0 } }));
body.push(...sectionHeading('Declarations & Undertakings'));

body.push(
    para(run(
        'The following declarations are made by the undersigned on behalf of ' +
        (isCorp ? d.company_name || d.client_name : d.full_name || d.client_name) +
        '. By signing this document, the undersigned confirms that each declaration is true, complete, and accurate to the best of their knowledge.',
        { size: hp(10), italics: true, color: '374151' }
    ), { spacing: { after: pt(8) } }),
);

// Declaration 1: PEP
const pepText = d.pep_status
    ? `The undersigned acknowledges that ${isCorp ? 'one or more directors, shareholders, or senior officers of ' + (d.company_name || d.client_name) : d.full_name || d.client_name} is or has been a Politically Exposed Person (PEP) as defined under the UAE AML/CFT framework. Full details have been disclosed to ${d.tenant_name} and enhanced due diligence has been applied accordingly. Any change in PEP status will be immediately reported to ${d.tenant_name}.`
    : `The undersigned confirms that ${isCorp ? 'none of the directors, shareholders, beneficial owners, or senior officers of ' + (d.company_name || d.client_name) : d.full_name || d.client_name + ' is'} ${isCorp ? 'are' : ''} a Politically Exposed Person (PEP), nor ${isCorp ? 'are any of them' : 'is the undersigned'} a close associate or immediate family member of a PEP, as defined under the UAE Anti-Money Laundering and Combating the Financing of Terrorism regulations. Any change in PEP status will be reported to ${d.tenant_name} immediately.`;

body.push(...declarationBlock('1. Politically Exposed Person (PEP) Declaration', pepText));

// Declaration 2: Source of Funds & Wealth
const sof  = d.source_of_funds_label  || 'legitimate business activities';
const sow  = d.source_of_wealth_label || 'legitimate business activities';
const sofText = `The undersigned declares that the primary source of funds for transactions conducted with ${d.tenant_name} is ${sof}, and the source of wealth is ${sow}. The undersigned confirms that all funds are derived from entirely legitimate activities, are not connected to any criminal offense, and are not subject to any asset-freezing order or sanction. Any material change in source of funds or wealth will be promptly disclosed to ${d.tenant_name}.`;

body.push(...declarationBlock('2. Source of Funds & Source of Wealth Declaration', sofText));

// Declaration 3: Sanctions Compliance
const sanctText = `The undersigned confirms that ${isCorp ? 'the company, its directors, shareholders, and ultimate beneficial owners' : 'the undersigned'} are not designated on, nor have any association with any party designated on, any applicable sanctions list — including but not limited to the UN Security Council Consolidated List, UAE Cabinet Resolution No. 83 Terrorist Designations, OFAC SDN List, UK HM Treasury Financial Sanctions List, and the EU Consolidated Sanctions List. The undersigned undertakes to notify ${d.tenant_name} immediately upon becoming aware of any sanctions designation, inquiry, or freeze order affecting ${isCorp ? 'the company or any of its associated parties' : 'the undersigned'}.`;

body.push(...declarationBlock('3. Sanctions Compliance Declaration', sanctText));

// Declaration 4: Supply Chain & CAHRA (corporate + gold sector)
if (isCorp && isGold) {
    const scText = `The undersigned confirms that all precious metals and commodities sourced, traded, or handled by ${d.company_name || d.client_name} have been responsibly procured in compliance with the OECD Due Diligence Guidance for Responsible Supply Chains of Minerals from Conflict-Affected and High-Risk Areas (CAHRA). The company does not knowingly engage in transactions with parties involved in illegal mining operations, armed conflict financing, human rights abuses, or any other illicit activity within the precious metals supply chain. The company maintains supply chain documentation and will make such records available to ${d.tenant_name} upon request.`;
    body.push(...declarationBlock('4. Gold Supply Chain & CAHRA Declaration', scText));
}

// Declaration 5: UBO (corporate only)
if (isCorp) {
    const uboText = `The undersigned declares that the information provided in this document regarding the ultimate beneficial ownership (UBO) structure of ${d.company_name || d.client_name} is complete, accurate, and current as of the date of signing. All individuals who ultimately own or control 25% or more of the company's shares, voting rights, or otherwise exercise effective control have been disclosed. The undersigned undertakes to notify ${d.tenant_name} in writing within 30 days of any change in the beneficial ownership structure, including any change in ownership percentages, addition or removal of beneficial owners, or transfer of control.`;
    body.push(...declarationBlock(`${isGold ? 5 : 4}. Ultimate Beneficial Ownership (UBO) Declaration`, uboText));
}

// Final Declaration: General Undertaking
const finalNum = isCorp ? (isGold ? 6 : 5) : 4;
const generalText = `The undersigned confirms that all information provided in this Client Due Diligence Pack is true, complete, and accurate to the best of their knowledge and belief as of the date of signing. The undersigned acknowledges that ${d.tenant_name} is required by law to collect and verify this information for the purposes of AML/CFT compliance under UAE Federal Decree-Law No. 20 of 2018 and its implementing regulations. The undersigned undertakes to promptly notify ${d.tenant_name} of any material changes to the information provided herein, including changes in ownership structure, business activity, address, source of funds, or any other information that may affect the risk profile of this relationship.`;

body.push(...declarationBlock(`${finalNum}. General Undertaking`, generalText));
body.push(gap(12));

// ════════════════════════════════════════════════════════════════════════════════
// AUTHORIZATION & SIGNATURE
// ════════════════════════════════════════════════════════════════════════════════
body.push(
    ...sectionHeading('Authorization & Signature', true),
    gap(4),
    para(run(
        'By signing below, the undersigned confirms they have read, understood, and agreed to all declarations and information set out in this Client Due Diligence Pack, and that they are duly authorized to sign on behalf of the client named above.',
        { size: hp(10), italics: true, color: '374151' }
    ), { spacing: { after: pt(14) } }),
);

const cW = Math.floor(FULL / 3);
body.push(
    new Table({
        width: { size: FULL, type: WidthType.DXA },
        columnWidths: [cW, cW, FULL - 2 * cW],
        borders: { ...allBorders(NONE), insideH: NONE, insideV: NONE },
        rows: [new TableRow({ children: [
            sigCol(d.signatory_name, d.signatory_title || 'Client / Authorized Signatory', cW),
            sigCol(d.mlro_name,      'MLRO / Compliance Officer',                          cW),
            sigCol('',               'Date',                                                FULL - 2 * cW),
        ]})],
    }),
);

// ════════════════════════════════════════════════════════════════════════════════
// ASSEMBLE DOCUMENT
// ════════════════════════════════════════════════════════════════════════════════
const doc = new Document({
    sections: [{
        properties: {
            page: { margin: { top: pt(72), bottom: pt(72), left: pt(72), right: pt(72) } },
        },
        headers: {
            default: new Header({ children: [
                new Paragraph({
                    children: [
                        run(d.tenant_name, { bold: true, size: hp(8.5), color: '2E4A7A' }),
                        run('  •  Client Due Diligence Pack', { size: hp(8.5), color: '9CA3AF' }),
                    ],
                    alignment: AlignmentType.LEFT,
                    spacing: { after: 0 },
                    border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: 'D0D0D0' } },
                }),
            ]}),
        },
        footers: {
            default: new Footer({ children: [
                new Paragraph({
                    children: [
                        run(d.ref + '  •  Confidential — For Compliance Purposes Only  •  Page ', { size: hp(8), color: '9CA3AF' }),
                        new TextRun({ children: [PageNumber.CURRENT], size: hp(8), font: 'Calibri', color: '9CA3AF' }),
                        run(' of ', { size: hp(8), color: '9CA3AF' }),
                        new TextRun({ children: [PageNumber.TOTAL_PAGES], size: hp(8), font: 'Calibri', color: '9CA3AF' }),
                    ],
                    alignment: AlignmentType.CENTER,
                    spacing: { after: 0 },
                    border: { top: { style: BorderStyle.SINGLE, size: 4, color: 'D0D0D0' } },
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
