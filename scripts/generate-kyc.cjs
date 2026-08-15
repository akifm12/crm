'use strict';

// Usage: node generate-kyc.cjs <data.json> <out.docx>

const fs = require('fs');
const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, ShadingType,
    Header, Footer, PageNumber,
} = require('docx');

const dataFile = process.argv[2];
const outFile  = process.argv[3];
if (!dataFile || !outFile) { console.error('Usage: generate-kyc.cjs <data.json> <out.docx>'); process.exit(1); }

const d   = JSON.parse(fs.readFileSync(dataFile, 'utf8'));
const val = v => (v !== null && v !== undefined && String(v).trim()) ? String(v).trim() : '—';

// ── Units ─────────────────────────────────────────────────────────────────────
// A4 (11906 DXA) minus 1-inch margins on each side (1440 DXA × 2) = 9026 DXA text width
const FULL  = 9026;
const pt    = n => n * 20;   // points → DXA/twips
const hpts  = n => n * 2;    // points → half-points (font size)

// ── Borders ───────────────────────────────────────────────────────────────────
const NONE  = { style: BorderStyle.NONE,   size: 0, color: 'FFFFFF' };
const LIGHT = { style: BorderStyle.SINGLE, size: 4, color: 'D0D0D0' };
const DARK  = { style: BorderStyle.SINGLE, size: 6, color: '2E4A7A' };

// ── Text helpers ──────────────────────────────────────────────────────────────
const run = (text, opts = {}) => new TextRun({
    text: String(text ?? ''), size: hpts(10.5), font: 'Calibri', ...opts,
});

const para = (children, opts = {}) => new Paragraph({
    children: Array.isArray(children) ? children : [children],
    spacing: { after: pt(4), ...opts.spacing },
    ...opts,
});

const gap = (pts = 8) => para(run(''), { spacing: { after: pt(pts) } });

// ── Section heading — single-cell table guarantees same width as data tables ──
const sectionHeading = (text) => [
    gap(6),
    new Table({
        width: { size: FULL, type: WidthType.DXA },
        columnWidths: [FULL],
        borders: { top: NONE, bottom: NONE, left: NONE, right: NONE, insideH: NONE, insideV: NONE },
        rows: [new TableRow({ children: [
            new TableCell({
                width: { size: FULL, type: WidthType.DXA },
                shading: { fill: '2E4A7A', type: ShadingType.CLEAR },
                borders: { top: NONE, bottom: NONE, left: NONE, right: NONE },
                margins: { top: pt(4), bottom: pt(4), left: pt(8), right: pt(8) },
                children: [para(
                    run(text.toUpperCase(), { bold: true, color: 'FFFFFF', size: hpts(10) }),
                    { spacing: { after: 0 } }
                )],
            }),
        ]})],
    }),
];

// ── Two-column info table ─────────────────────────────────────────────────────
const LW = 2700;
const VW = FULL - LW;

const infoRow = (label, value, shade) => new TableRow({
    children: [
        new TableCell({
            width: { size: LW, type: WidthType.DXA },
            shading: { fill: shade ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: LIGHT },
            margins: { top: pt(2.5), bottom: pt(2.5), left: pt(7), right: pt(7) },
            children: [para(run(label, { bold: true, size: hpts(10), color: '374151' }), { spacing: { after: 0 } })],
        }),
        new TableCell({
            width: { size: VW, type: WidthType.DXA },
            shading: { fill: shade ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: NONE },
            margins: { top: pt(2.5), bottom: pt(2.5), left: pt(7), right: pt(7) },
            children: [para(run(val(value)), { spacing: { after: 0 } })],
        }),
    ],
});

const infoTable = (rows) => new Table({
    width: { size: FULL, type: WidthType.DXA },
    columnWidths: [LW, VW],
    borders: { top: DARK, bottom: DARK, left: DARK, right: DARK, insideH: LIGHT, insideV: NONE },
    rows: rows.filter(Boolean),
});

// ── Multi-column grid table ───────────────────────────────────────────────────
const gridTable = (headers, widths, rows) => {
    const headRow = new TableRow({
        children: headers.map((h, i) => new TableCell({
            width: { size: widths[i], type: WidthType.DXA },
            shading: { fill: '4A6FA5', type: ShadingType.CLEAR },
            borders: { top: NONE, bottom: NONE, left: NONE, right: NONE },
            margins: { top: pt(3), bottom: pt(3), left: pt(7), right: pt(7) },
            children: [para(run(h, { bold: true, color: 'FFFFFF', size: hpts(9.5) }), { spacing: { after: 0 } })],
        })),
    });
    const dataRows = rows.map((cells, ri) => new TableRow({
        children: cells.map((cell, ci) => new TableCell({
            width: { size: widths[ci], type: WidthType.DXA },
            shading: { fill: ri % 2 === 1 ? 'F2F4F8' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: LIGHT, bottom: LIGHT, left: NONE, right: NONE },
            margins: { top: pt(2.5), bottom: pt(2.5), left: pt(7), right: pt(7) },
            children: [para(run(val(cell), { size: hpts(9.5) }), { spacing: { after: 0 } })],
        })),
    }));
    return new Table({
        width: { size: FULL, type: WidthType.DXA },
        columnWidths: widths,
        borders: { top: DARK, bottom: DARK, left: DARK, right: DARK, insideH: LIGHT, insideV: LIGHT },
        rows: [headRow, ...dataRows],
    });
};

// ── Signature columns ─────────────────────────────────────────────────────────
const sigCol = (name, role, w) => new TableCell({
    width: { size: w, type: WidthType.DXA },
    borders: { top: NONE, bottom: NONE, left: NONE, right: NONE },
    margins: { top: 0, bottom: 0, left: pt(4), right: pt(16) },
    children: [
        para(run(''), { spacing: { before: pt(50), after: 0 }, border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: '000000' } } }),
        para(run(name || ' ', { bold: true, size: hpts(10) }), { spacing: { after: pt(1) } }),
        para(run(role, { size: hpts(9), color: '6B7280' }), { spacing: { after: 0 } }),
    ],
});

// ── Build body ────────────────────────────────────────────────────────────────
const isCorp = d.is_corp;
const sn     = (corp, ind) => isCorp ? corp : ind;
const body   = [];

// Title block
body.push(
    new Paragraph({
        children: [run(d.tenant_name, { bold: true, size: hpts(18) })],
        alignment: AlignmentType.CENTER,
        spacing: { after: pt(3) },
    }),
    new Paragraph({
        children: [run('Know Your Customer — Client Due Diligence File', { size: hpts(13), color: '2E4A7A' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: pt(2) },
    }),
    new Paragraph({
        children: [run(`${d.ref}  ·  ${d.generated}`, { size: hpts(9), color: '9CA3AF' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: 0 },
        border: { bottom: { style: BorderStyle.SINGLE, size: 12, color: '2E4A7A' } },
    }),
    gap(10),
);

// Client summary
body.push(
    infoTable([
        infoRow('Client',             val(d.client_name)),
        infoRow('Type',               val(d.client_type_label),              true),
        infoRow('Status',             val(d.status)),
        infoRow('CDD Level',          (d.cdd_type || 'Standard').toUpperCase(), true),
        infoRow('Risk Rating',        (d.risk_rating || 'Unrated').toUpperCase()),
        d.next_review_date ? infoRow('Next Review Date', val(d.next_review_date), true) : null,
    ]),
    gap(6),
);

// Section 1: Client Identification
body.push(...sectionHeading(`${sn(1, 1)}. Client Identification`));

if (isCorp) {
    body.push(
        infoTable([
            infoRow('Company Name',              val(d.company_name)),
            infoRow('Legal Form',                val(d.legal_form),               true),
            infoRow('Country of Incorporation',  val(d.country_of_incorporation)),
            infoRow('Trade License No.',         val(d.trade_license_no),         true),
            infoRow('Trade License Validity',    val(d.trade_license_validity)),
            infoRow('VAT / TRN',                 val(d.trn_number),               true),
            infoRow('Ejari No.',                 val(d.ejari_number)),
            d.ejari_expiry ? infoRow('Ejari Expiry',     val(d.ejari_expiry),     true) : null,
            infoRow('Business Activity',         val(d.business_activity)),
            infoRow('Registered Address',        val(d.registered_address),       true),
            infoRow('Email',                     val(d.email)),
            infoRow('Phone',                     val(d.phone),                    true),
            d.website ? infoRow('Website',       val(d.website)) : null,
        ]),
        gap(6),
    );
} else {
    body.push(
        infoTable([
            infoRow('Full Name',         val(d.full_name)),
            d.name_arabic ? infoRow('Name (Arabic)', val(d.name_arabic),          true) : null,
            infoRow('Nationality',       val(d.nationality)),
            infoRow('Date of Birth',     val(d.dob),                              true),
            infoRow('Passport No.',      val(d.passport_number)),
            infoRow('Passport Expiry',   val(d.passport_expiry),                  true),
            infoRow('Emirates ID',       val(d.eid_number)),
            d.eid_expiry ? infoRow('Emirates ID Expiry', val(d.eid_expiry),       true) : null,
            infoRow('Occupation',        val(d.occupation)),
            infoRow('Employer',          val(d.employer_name),                    true),
            infoRow('PEP Status',        d.pep_status ? 'Yes — Politically Exposed Person' : 'No'),
            infoRow('Email',             val(d.email),                            true),
            infoRow('Phone',             val(d.phone)),
        ]),
        gap(6),
    );
}

// Section 2: Authorized Signatories (corporate only)
if (isCorp && d.signatories && d.signatories.length) {
    const sigW = [1950, 1400, 1250, 1100, 1050, 1076, 1200]; // sums to 9026
    body.push(
        ...sectionHeading('2. Authorized Signatories'),
        gridTable(
            ['Full Name', 'Position', 'Nationality', 'Date of Birth', 'Passport No.', 'Passport Expiry', 'Emirates ID'],
            sigW,
            d.signatories.map(s => [s.full_name, s.position, s.nationality, s.dob, s.passport_number, s.passport_expiry, s.eid_number]),
        ),
        gap(6),
    );
}

// Section 3: Shareholders & UBOs (corporate only)
if (isCorp && d.shareholders && d.shareholders.length) {
    const shW = [2250, 1000, 1200, 900, 650, 1626, 1400]; // sums to 9026
    body.push(
        ...sectionHeading('3. Shareholders & Ultimate Beneficial Owners'),
        gridTable(
            ['Name', 'Type', 'Nationality', 'Ownership %', 'UBO', 'Passport / ID', 'Date of Birth'],
            shW,
            d.shareholders.map(s => [
                s.name,
                s.shareholder_type,
                s.nationality,
                s.ownership_percentage ? parseFloat(s.ownership_percentage).toFixed(1) + '%' : '—',
                s.is_ubo ? 'Yes' : 'No',
                s.passport_number || s.eid_number,
                s.dob,
            ]),
        ),
        gap(6),
    );
}

// Section 4/2: AML Risk Profile
body.push(
    ...sectionHeading(`${sn(4, 2)}. AML Risk Profile & Business Relationship`),
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

// Section 5/3: Risk Assessment
body.push(
    ...sectionHeading(`${sn(5, 3)}. Risk Assessment`),
    infoTable([
        infoRow('Risk Rating',   (d.risk_rating || 'Unrated').toUpperCase()),
        d.risk_assessed_at ? infoRow('Assessed On',  val(d.risk_assessed_at),  true) : null,
        d.risk_assessed_by ? infoRow('Assessed By',  val(d.risk_assessed_by))        : null,
        d.next_review_date ? infoRow('Next Review',  val(d.next_review_date),  true) : null,
        d.risk_notes       ? infoRow('Notes',        val(d.risk_notes))              : null,
    ]),
    gap(6),
);

// Section 6/4: Sanctions & AML Screening
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

body.push(
    ...sectionHeading(`${sn(6, 4)}. Sanctions & AML Screening`),
    infoTable([
        infoRow('Screening Result',     (d.screening_status || 'Not Screened').toUpperCase()),
        d.screening_date      ? infoRow('Last Screened',  val(d.screening_date),      true) : null,
        d.screening_reference ? infoRow('Reference',      val(d.screening_reference))        : null,
    ]),
    gap(4),
    para(run('The following lists and databases were screened:', { bold: true, size: hpts(10) }), { spacing: { after: pt(3) } }),
    ...sanctionsList.map(item =>
        para([
            run('•  ', { size: hpts(10), color: '2E4A7A' }),
            run(item, { size: hpts(10) }),
        ], { spacing: { after: pt(2) }, indent: { left: pt(10) } })
    ),
    gap(16),
);

// Signature block
const colW = Math.floor(FULL / 3);
body.push(
    new Table({
        width: { size: FULL, type: WidthType.DXA },
        columnWidths: [colW, colW, FULL - 2 * colW],
        borders: { top: NONE, bottom: NONE, left: NONE, right: NONE, insideH: NONE, insideV: NONE },
        rows: [new TableRow({ children: [
            sigCol(d.signatory_name, d.signatory_title || 'Client / Authorized Signatory', colW),
            sigCol(d.mlro_name,      'MLRO / Compliance Officer', colW),
            sigCol('',               'Date of Approval', FULL - 2 * colW),
        ]})],
    }),
);

// ── Assemble document ─────────────────────────────────────────────────────────
const doc = new Document({
    sections: [{
        properties: {
            page: { margin: { top: pt(72), bottom: pt(72), left: pt(72), right: pt(72) } },
        },
        headers: {
            default: new Header({
                children: [
                    new Paragraph({
                        children: [
                            run(d.tenant_name, { bold: true, size: hpts(8.5), color: '2E4A7A' }),
                            run('  •  Know Your Customer — Client Due Diligence File', { size: hpts(8.5), color: '9CA3AF' }),
                        ],
                        alignment: AlignmentType.LEFT,
                        spacing: { after: 0 },
                        border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: 'D0D0D0' } },
                    }),
                ],
            }),
        },
        footers: {
            default: new Footer({
                children: [
                    new Paragraph({
                        children: [
                            run(d.ref + '  •  Confidential — For Compliance Purposes Only  •  Page ', { size: hpts(8), color: '9CA3AF' }),
                            new TextRun({ children: [PageNumber.CURRENT], size: hpts(8), font: 'Calibri', color: '9CA3AF' }),
                            run(' of ', { size: hpts(8), color: '9CA3AF' }),
                            new TextRun({ children: [PageNumber.TOTAL_PAGES], size: hpts(8), font: 'Calibri', color: '9CA3AF' }),
                        ],
                        alignment: AlignmentType.CENTER,
                        spacing: { after: 0 },
                        border: { top: { style: BorderStyle.SINGLE, size: 4, color: 'D0D0D0' } },
                    }),
                ],
            }),
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
