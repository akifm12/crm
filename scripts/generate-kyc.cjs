'use strict';

// Usage: node generate-kyc.cjs <data.json> <out.docx>

const fs = require('fs');
const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, ShadingType,
    Header, Footer, PageNumber, NumberFormat,
    HeadingLevel, UnderlineType,
} = require('docx');

const dataFile = process.argv[2];
const outFile  = process.argv[3];
if (!dataFile || !outFile) {
    console.error('Usage: generate-kyc.cjs <data.json> <out.docx>');
    process.exit(1);
}

const d   = JSON.parse(fs.readFileSync(dataFile, 'utf8'));
const val = v => (v && String(v).trim()) ? String(v).trim() : '—';

// ── Typography helpers ────────────────────────────────────────────────────────
const sz  = n => n * 2;   // half-points
const twip = n => n * 20; // 1pt = 20 twips

const t = (text, opts = {}) =>
    new TextRun({ text: String(text ?? ''), size: sz(11), font: 'Calibri', ...opts });

const p = (runs, opts = {}) =>
    new Paragraph({
        children: Array.isArray(runs) ? runs : [runs],
        spacing: { after: twip(4), ...opts.spacing },
        ...opts,
    });

const blank = () => p(t(''), { spacing: { after: twip(6) } });

// ── Section heading ───────────────────────────────────────────────────────────
const heading = (text) => new Paragraph({
    children: [new TextRun({ text: text.toUpperCase(), bold: true, size: sz(11), font: 'Calibri', color: 'FFFFFF' })],
    spacing: { before: twip(12), after: 0 },
    shading: { fill: '1F3864', type: ShadingType.CLEAR },
    indent: { left: twip(4), right: twip(4) },
});

// ── Two-column info table ─────────────────────────────────────────────────────
const FULL = 8640;   // total usable table width in DXA (~6 inches)
const LW   = 2600;
const VW   = FULL - LW;

const thin   = { style: BorderStyle.SINGLE, size: 4, color: 'C0C0C0' };
const none   = { style: BorderStyle.NONE,   size: 0, color: 'FFFFFF' };

const infoRow = (label, value, shade) => new TableRow({
    children: [
        new TableCell({
            width: { size: LW, type: WidthType.DXA },
            shading: { fill: shade ? 'F5F5F5' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: thin, bottom: thin, left: none, right: thin },
            margins: { top: twip(2), bottom: twip(2), left: twip(6), right: twip(6) },
            children: [p(t(label, { bold: true, size: sz(10), color: '444444' }), { spacing: { after: 0 } })],
        }),
        new TableCell({
            width: { size: VW, type: WidthType.DXA },
            shading: { fill: shade ? 'F5F5F5' : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: thin, bottom: thin, left: none, right: none },
            margins: { top: twip(2), bottom: twip(2), left: twip(6), right: twip(6) },
            children: [p(t(val(value)), { spacing: { after: 0 } })],
        }),
    ],
});

const infoTable = (rows) => new Table({
    width: { size: FULL, type: WidthType.DXA },
    columnWidths: [LW, VW],
    borders: { top: thin, bottom: thin, left: thin, right: thin, insideH: thin, insideV: none },
    rows: rows.filter(Boolean),
});

// ── Multi-column data table ───────────────────────────────────────────────────
const thHead = { style: BorderStyle.SINGLE, size: 4, color: '1F3864' };

const dataHead = (labels, widths) => new TableRow({
    children: labels.map((label, i) => new TableCell({
        width: { size: widths[i], type: WidthType.DXA },
        shading: { fill: '1F3864', type: ShadingType.CLEAR },
        borders: { top: none, bottom: none, left: none, right: none },
        margins: { top: twip(2), bottom: twip(2), left: twip(6), right: twip(6) },
        children: [p(t(label, { bold: true, color: 'FFFFFF', size: sz(9.5) }), { spacing: { after: 0 } })],
    })),
});

const dataRow = (cells, widths, shade) => new TableRow({
    children: cells.map((cell, i) => new TableCell({
        width: { size: widths[i], type: WidthType.DXA },
        shading: { fill: shade ? 'F5F5F5' : 'FFFFFF', type: ShadingType.CLEAR },
        borders: { top: thin, bottom: thin, left: none, right: none },
        margins: { top: twip(2), bottom: twip(2), left: twip(6), right: twip(6) },
        children: [p(t(val(cell), { size: sz(10) }), { spacing: { after: 0 } })],
    })),
});

const dataTable = (headers, widths, rows) => new Table({
    width: { size: FULL, type: WidthType.DXA },
    columnWidths: widths,
    borders: { top: thHead, bottom: thin, left: thin, right: thin, insideH: thin, insideV: none },
    rows: [
        dataHead(headers, widths),
        ...rows.map((row, i) => dataRow(row, widths, i % 2 === 1)),
    ],
});

// ── Signature line ────────────────────────────────────────────────────────────
const sigBlock = (name, role) => new TableCell({
    width: { size: Math.floor(FULL / 3), type: WidthType.DXA },
    borders: { top: none, bottom: none, left: none, right: none },
    margins: { top: 0, bottom: 0, left: twip(4), right: twip(4) },
    children: [
        new Paragraph({
            children: [t('')],
            spacing: { before: twip(40), after: 0 },
            border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: '000000' } },
        }),
        p(t(name || ' ', { bold: true, size: sz(10) }), { spacing: { after: twip(1) } }),
        p(t(role, { size: sz(9), color: '666666' }), { spacing: { after: 0 } }),
    ],
});

// ── Build document body ───────────────────────────────────────────────────────
const isCorp = d.is_corp;
const sn     = (c, i) => isCorp ? c : i;
const body   = [];

// Title block
body.push(
    new Paragraph({
        children: [new TextRun({ text: d.tenant_name, bold: true, size: sz(16), font: 'Calibri' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: twip(4) },
    }),
    new Paragraph({
        children: [new TextRun({ text: 'Know Your Customer — Client Due Diligence File', size: sz(13), font: 'Calibri', color: '1F3864' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: twip(3) },
    }),
    new Paragraph({
        children: [new TextRun({ text: `${d.ref}  ·  Generated: ${d.generated}`, size: sz(9), font: 'Calibri', color: '888888' })],
        alignment: AlignmentType.CENTER,
        spacing: { after: twip(2) },
    }),
    // Horizontal rule via paragraph border
    new Paragraph({
        children: [t('')],
        spacing: { after: twip(10) },
        border: { bottom: { style: BorderStyle.SINGLE, size: 12, color: '1F3864' } },
    }),
);

// Client summary row
body.push(
    infoTable([
        infoRow('Client',       val(d.client_name)),
        infoRow('Type',         val(d.client_type_label), true),
        infoRow('Status',       val(d.status)),
        infoRow('CDD Level',    (d.cdd_type || 'Standard').toUpperCase(), true),
        infoRow('Risk Rating',  (d.risk_rating || 'Unrated').toUpperCase()),
        d.next_review_date ? infoRow('Next Review', val(d.next_review_date), true) : null,
    ]),
    blank(),
);

// ── Section 1: Client Identification ─────────────────────────────────────────
body.push(heading(`${sn(1, 1)}. Client Identification`));
if (isCorp) {
    body.push(infoTable([
        infoRow('Company Name',             val(d.company_name)),
        infoRow('Legal Form',               val(d.legal_form),               true),
        infoRow('Country of Incorporation', val(d.country_of_incorporation)),
        infoRow('Trade Licence No.',        val(d.trade_license_no),         true),
        infoRow('Trade Licence Validity',   val(d.trade_license_validity)),
        infoRow('VAT / TRN',                val(d.trn_number),               true),
        infoRow('Ejari No.',                val(d.ejari_number)),
        d.ejari_expiry ? infoRow('Ejari Expiry', val(d.ejari_expiry),        true) : null,
        infoRow('Business Activity',        val(d.business_activity)),
        infoRow('Registered Address',       val(d.registered_address),       true),
        infoRow('Email',                    val(d.email)),
        infoRow('Phone',                    val(d.phone),                    true),
        d.website ? infoRow('Website',      val(d.website)) : null,
    ]));
} else {
    body.push(infoTable([
        infoRow('Full Name',        val(d.full_name)),
        d.name_arabic ? infoRow('Name (Arabic)', val(d.name_arabic),         true) : null,
        infoRow('Nationality',      val(d.nationality)),
        infoRow('Date of Birth',    val(d.dob),                              true),
        infoRow('Passport No.',     val(d.passport_number)),
        infoRow('Passport Expiry',  val(d.passport_expiry),                  true),
        infoRow('Emirates ID',      val(d.eid_number)),
        d.eid_expiry ? infoRow('Emirates ID Expiry', val(d.eid_expiry),      true) : null,
        infoRow('Occupation',       val(d.occupation)),
        infoRow('Employer',         val(d.employer_name),                    true),
        infoRow('PEP Status',       d.pep_status ? 'Yes — Politically Exposed Person' : 'No'),
        infoRow('Email',            val(d.email),                            true),
        infoRow('Phone',            val(d.phone)),
    ]));
}
body.push(blank());

// ── Section 2: Signatories ────────────────────────────────────────────────────
if (isCorp && d.signatories && d.signatories.length) {
    const sigW = [1600, 1400, 1200, 1100, 1000, 1140, 1200];
    body.push(
        heading('2. Authorised Signatories'),
        dataTable(
            ['Full Name', 'Position', 'Nationality', 'Date of Birth', 'Passport No.', 'Passport Expiry', 'Emirates ID'],
            sigW,
            d.signatories.map(s => [s.full_name, s.position, s.nationality, s.dob, s.passport_number, s.passport_expiry, s.eid_number]),
        ),
        blank(),
    );
}

// ── Section 3: Shareholders ───────────────────────────────────────────────────
if (isCorp && d.shareholders && d.shareholders.length) {
    const shW = [2200, 900, 1100, 900, 600, 1440, 1500];
    body.push(
        heading('3. Shareholders & Ultimate Beneficial Owners'),
        dataTable(
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
        blank(),
    );
}

// ── Section 4/2: AML Risk Profile ────────────────────────────────────────────
body.push(
    heading(`${sn(4, 2)}. AML Risk Profile & Business Relationship`),
    infoTable([
        infoRow('Source of Funds',          val(d.source_of_funds_label)),
        infoRow('Source of Wealth',         val(d.source_of_wealth_label),        true),
        infoRow('Purpose of Relationship',  val(d.purpose_of_relationship_label)),
        infoRow('Expected Monthly Volume',  d.expected_monthly_volume ? 'AED ' + Number(d.expected_monthly_volume).toLocaleString() : '—', true),
        infoRow('Expected Frequency',       d.expected_monthly_frequency ? d.expected_monthly_frequency + ' transactions / month' : '—'),
        infoRow('Countries Involved',       val(d.countries_involved),             true),
    ]),
    blank(),
);

// ── Section 5/3: Risk Assessment ─────────────────────────────────────────────
body.push(
    heading(`${sn(5, 3)}. Risk Assessment`),
    infoTable([
        infoRow('Risk Rating',   (d.risk_rating || 'Unrated').toUpperCase()),
        d.risk_assessed_at ? infoRow('Assessed On', val(d.risk_assessed_at),       true) : null,
        d.risk_assessed_by ? infoRow('Assessed By', val(d.risk_assessed_by))             : null,
        d.next_review_date ? infoRow('Next Review', val(d.next_review_date),        true) : null,
        d.risk_notes       ? infoRow('Notes',       val(d.risk_notes))                   : null,
    ]),
    blank(),
);

// ── Section 6/4: Sanctions & AML Screening ───────────────────────────────────
const sanctionsList = [
    'UN Security Council Consolidated List',
    'UAE Cabinet Resolution No. 83 — Terror List',
    'OFAC SDN & Consolidated Sanctions List (USA)',
    'UK HM Treasury Financial Sanctions List',
    'EU Consolidated Sanctions List',
    'FATF High-Risk & Monitored Jurisdictions',
    'Interpol Wanted Persons (Red Notices)',
    'UAE Central Bank — Designated Persons List',
    'Basel AML Index',
];

body.push(
    heading(`${sn(6, 4)}. Sanctions & AML Screening`),
    infoTable([
        infoRow('Screening Status',    (d.screening_status || 'Not Screened').toUpperCase()),
        d.screening_date      ? infoRow('Last Screened',       val(d.screening_date),      true) : null,
        d.screening_reference ? infoRow('Reference',           val(d.screening_reference))        : null,
    ]),
    blank(),
    p(t('Lists screened:', { bold: true, size: sz(10) }), { spacing: { after: twip(3) } }),
    ...sanctionsList.map(item =>
        p([t('•  ', { size: sz(10), color: '1F3864' }), t(item, { size: sz(10) })], { spacing: { after: twip(2) }, indent: { left: twip(6) } })
    ),
    blank(),
    blank(),
);

// ── Signature block ───────────────────────────────────────────────────────────
body.push(
    new Table({
        width: { size: FULL, type: WidthType.DXA },
        columnWidths: [Math.floor(FULL / 3), Math.floor(FULL / 3), Math.floor(FULL / 3)],
        borders: { top: none, bottom: none, left: none, right: none, insideH: none, insideV: none },
        rows: [new TableRow({ children: [
            sigBlock(d.signatory_name, d.signatory_title || 'Client / Authorised Signatory'),
            sigBlock(d.mlro_name,      'MLRO / Compliance Officer'),
            sigBlock('',               'Date of Approval'),
        ]})],
    }),
);

// ── Assemble & write ──────────────────────────────────────────────────────────
const doc = new Document({
    sections: [{
        properties: { page: { margin: { top: twip(72), bottom: twip(72), left: twip(72), right: twip(72) } } },
        headers: {
            default: new Header({
                children: [
                    p([
                        t(d.tenant_name, { bold: true, size: sz(9), color: '1F3864' }),
                        t('  ·  Know Your Customer — Client Due Diligence File', { size: sz(9), color: '888888' }),
                    ], { spacing: { after: 0 }, border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: 'C0C0C0' } } }),
                ],
            }),
        },
        footers: {
            default: new Footer({
                children: [
                    new Paragraph({
                        children: [
                            t(d.ref + '  ·  Confidential — For Compliance Purposes Only  ·  Page ', { size: sz(8), color: '888888' }),
                            new TextRun({ children: [PageNumber.CURRENT], size: sz(8), font: 'Calibri', color: '888888' }),
                            t(' of ', { size: sz(8), color: '888888' }),
                            new TextRun({ children: [PageNumber.TOTAL_PAGES], size: sz(8), font: 'Calibri', color: '888888' }),
                        ],
                        alignment: AlignmentType.CENTER,
                        spacing: { before: 0, after: 0 },
                        border: { top: { style: BorderStyle.SINGLE, size: 4, color: 'C0C0C0' } },
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
