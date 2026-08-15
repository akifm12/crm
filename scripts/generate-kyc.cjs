// scripts/generate-kyc.cjs
// KYC / Client Due Diligence Word document
// Usage: node generate-kyc.cjs <data_json_file> <output_path>
'use strict';

const fs = require('fs');
const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, ShadingType, HeadingLevel,
    PageNumber, NumberFormat, Header, Footer, convertInchesToTwip,
} = require('docx');

const dataFile = process.argv[2];
const outFile  = process.argv[3];
if (!dataFile || !outFile) { console.error('Usage: generate-kyc.cjs <data.json> <out.docx>'); process.exit(1); }

const d = JSON.parse(fs.readFileSync(dataFile, 'utf8'));

// ── Colours ───────────────────────────────────────────────────────────────────
const BLACK   = '000000';
const DARK    = '222222';
const LGREY   = 'f2f2f2';
const MGREY   = 'dddddd';
const DGREY   = '555555';

// ── Helpers ───────────────────────────────────────────────────────────────────
const pt  = n => n * 2;  // half-points
const twp = n => n * 20; // twips (1pt = 20 twips)

const run  = (text, opts = {}) => new TextRun({ text: String(text ?? ''), size: pt(9.5), ...opts });
const bold = (text, opts = {}) => run(text, { bold: true, ...opts });
const muted= (text) => run(text, { color: '777777', italics: true });

const para = (children, opts = {}) => new Paragraph({
    children: Array.isArray(children) ? children : [children],
    spacing:  { after: 80, ...(opts.spacing || {}) },
    alignment: opts.align || AlignmentType.LEFT,
    ...opts,
});

const blank = (n = 1) => Array.from({ length: n }, () =>
    new Paragraph({ children: [run('')], spacing: { after: 40 } })
);

const val = (v) => (v && String(v).trim()) ? String(v).trim() : '—';

// ── Section heading ───────────────────────────────────────────────────────────
const sectionHead = (text) => new Paragraph({
    children: [new TextRun({ text: text.toUpperCase(), bold: true, color: 'FFFFFF', size: pt(9.5) })],
    spacing: { before: twp(10), after: 0 },
    shading: { fill: DARK, type: ShadingType.CLEAR, color: DARK },
    indent: { left: twp(6), right: twp(6) },
});

// ── Standard 2-column info row ────────────────────────────────────────────────
const LABEL_W = 2700;
const VALUE_W = 5900;
const TABLE_W = LABEL_W + VALUE_W;

const noBorder = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };
const thinBorder = { style: BorderStyle.SINGLE, size: 4, color: MGREY };
const thickBorder = { style: BorderStyle.SINGLE, size: 12, color: DARK };

const infoRow = (label, value, shade = false) => new TableRow({
    children: [
        new TableCell({
            width: { size: LABEL_W, type: WidthType.DXA },
            shading: { fill: shade ? LGREY : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: thinBorder, bottom: thinBorder, left: noBorder, right: thinBorder },
            margins: { top: twp(3), bottom: twp(3), left: twp(6), right: twp(6) },
            children: [para(bold(label, { size: pt(9), color: DGREY }), { spacing: { after: 0 } })],
        }),
        new TableCell({
            width: { size: VALUE_W, type: WidthType.DXA },
            shading: { fill: shade ? LGREY : 'FFFFFF', type: ShadingType.CLEAR },
            borders: { top: thinBorder, bottom: thinBorder, left: noBorder, right: noBorder },
            margins: { top: twp(3), bottom: twp(3), left: twp(6), right: twp(6) },
            children: [para(Array.isArray(value) ? value : run(String(value ?? '—')), { spacing: { after: 0 } })],
        }),
    ],
});

const infoTable = (rows) => new Table({
    width: { size: TABLE_W, type: WidthType.DXA },
    columnWidths: [LABEL_W, VALUE_W],
    borders: {
        top: thickBorder, bottom: thinBorder,
        left: thinBorder, right: thinBorder,
        insideH: thinBorder, insideV: noBorder,
    },
    rows: rows.filter(Boolean),
});

// ── Data table (multi-column) ─────────────────────────────────────────────────
const dataTableHeader = (texts, widths) => new TableRow({
    children: texts.map((t, i) => new TableCell({
        width: { size: widths[i], type: WidthType.DXA },
        shading: { fill: '333333', type: ShadingType.CLEAR },
        margins: { top: twp(3), bottom: twp(3), left: twp(6), right: twp(6) },
        borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
        children: [para(new TextRun({ text: t, bold: true, color: 'FFFFFF', size: pt(8.5) }), { spacing: { after: 0 } })],
    })),
});

const dataTableRow = (cells, widths, shade = false) => new TableRow({
    children: cells.map((c, i) => new TableCell({
        width: { size: widths[i], type: WidthType.DXA },
        shading: { fill: shade ? LGREY : 'FFFFFF', type: ShadingType.CLEAR },
        margins: { top: twp(3), bottom: twp(3), left: twp(6), right: twp(6) },
        borders: { top: thinBorder, bottom: thinBorder, left: noBorder, right: noBorder },
        children: [para(run(String(c ?? '—')), { spacing: { after: 0 } })],
    })),
});

// ── Signature line ────────────────────────────────────────────────────────────
const sigCell = (name, title) => new TableCell({
    width: { size: 2800, type: WidthType.DXA },
    borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
    margins: { top: 0, bottom: 0, left: twp(4), right: twp(4) },
    children: [
        new Paragraph({ children: [run('')], spacing: { after: 0 }, border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: BLACK } } }),
        ...blank(1),
        para(bold(name || ' ', { size: pt(9.5) }), { spacing: { after: 20 } }),
        para(run(title, { size: pt(8.5), color: DGREY }), { spacing: { after: 0 } }),
    ],
});

// ── Build document sections ───────────────────────────────────────────────────

const isCorp = d.is_corp;
const sn     = (c, i) => isCorp ? c : i;

const children = [];

// ── Client identity block ─────────────────────────────────────────────────────
children.push(
    new Table({
        width: { size: TABLE_W, type: WidthType.DXA },
        columnWidths: [6000, 2600],
        borders: { top: thickBorder, bottom: thickBorder, left: thickBorder, right: thickBorder, insideH: noBorder, insideV: noBorder },
        rows: [new TableRow({ children: [
            new TableCell({
                width: { size: 6000, type: WidthType.DXA },
                shading: { fill: LGREY, type: ShadingType.CLEAR },
                margins: { top: twp(5), bottom: twp(5), left: twp(8), right: twp(8) },
                borders: { top: noBorder, bottom: noBorder, left: noBorder, right: thinBorder },
                children: [
                    para(bold(d.client_name, { size: pt(13) }), { spacing: { after: 40 } }),
                    para([
                        run(d.client_type_label || d.client_type, { size: pt(9) }),
                        run('  ·  Status: ', { size: pt(9), color: DGREY }),
                        bold(d.status || '—', { size: pt(9) }),
                        run('  ·  CDD: ', { size: pt(9), color: DGREY }),
                        bold((d.cdd_type || 'standard').toUpperCase(), { size: pt(9) }),
                    ], { spacing: { after: 0 } }),
                ],
            }),
            new TableCell({
                width: { size: 2600, type: WidthType.DXA },
                shading: { fill: LGREY, type: ShadingType.CLEAR },
                margins: { top: twp(5), bottom: twp(5), left: twp(8), right: twp(8) },
                borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
                children: [
                    para(bold((d.risk_rating || 'UNRATED').toUpperCase() + ' RISK', { size: pt(9), color: BLACK }), { align: AlignmentType.RIGHT, spacing: { after: 40 } }),
                    d.next_review_date
                        ? para(run('Review by: ' + d.next_review_date, { size: pt(8.5), color: DGREY }), { align: AlignmentType.RIGHT, spacing: { after: 0 } })
                        : para(run(''), { spacing: { after: 0 } }),
                ],
            }),
        ]})],
    }),
    ...blank(1),
);

// ── Section 1 — Client Identification ────────────────────────────────────────
children.push(sectionHead(sn('1', '1') + '. Client Identification'), blank(0)[0]);

if (isCorp) {
    children.push(infoTable([
        infoRow('Company Name',             val(d.company_name)),
        infoRow('Legal Form',               val(d.legal_form),             true),
        infoRow('Country of Incorporation', val(d.country_of_incorporation)),
        infoRow('Trade Licence No.',        val(d.trade_license_no),       true),
        infoRow('Trade Licence Validity',   val(d.trade_license_validity)),
        infoRow('VAT / TRN',                val(d.trn_number),             true),
        infoRow('Ejari No.',                val(d.ejari_number)),
        d.ejari_expiry ? infoRow('Ejari Expiry', val(d.ejari_expiry), true) : null,
        infoRow('Business Activity',        val(d.business_activity)),
        infoRow('Registered Address',       val(d.registered_address),     true),
        infoRow('Email',                    val(d.email)),
        infoRow('Phone',                    val(d.phone),                  true),
        d.website ? infoRow('Website', val(d.website)) : null,
    ]));
} else {
    children.push(infoTable([
        infoRow('Full Name',         val(d.full_name)),
        d.name_arabic ? infoRow('Name (Arabic)', val(d.name_arabic), true) : null,
        infoRow('Nationality',       val(d.nationality),       true),
        infoRow('Date of Birth',     val(d.dob)),
        infoRow('Passport No.',      val(d.passport_number),   true),
        infoRow('Passport Expiry',   val(d.passport_expiry)),
        infoRow('Emirates ID No.',   val(d.eid_number),        true),
        d.eid_expiry ? infoRow('Emirates ID Expiry', val(d.eid_expiry)) : null,
        infoRow('Occupation',        val(d.occupation),        true),
        infoRow('Employer',          val(d.employer_name)),
        infoRow('PEP Status',        d.pep_status ? 'YES — Politically Exposed Person' : 'No', true),
        infoRow('Email',             val(d.email)),
        infoRow('Phone',             val(d.phone),             true),
    ]));
}
children.push(...blank(1));

// ── Section 2 — Signatories (corp) ───────────────────────────────────────────
if (isCorp && d.signatories && d.signatories.length) {
    children.push(sectionHead('2. Authorised Signatories'), blank(0)[0]);
    const sigW = [400, 1600, 1300, 1200, 1100, 1100, 1000, 1100];
    children.push(new Table({
        width: { size: TABLE_W, type: WidthType.DXA },
        columnWidths: sigW,
        borders: { top: thickBorder, bottom: thinBorder, left: thinBorder, right: thinBorder, insideH: thinBorder, insideV: noBorder },
        rows: [
            dataTableHeader(['#','Full Name','Position','Nationality','Date of Birth','Passport No.','Passport Expiry','Emirates ID'], sigW),
            ...d.signatories.map((s, i) => dataTableRow([
                i + 1, val(s.full_name), val(s.position), val(s.nationality),
                val(s.dob), val(s.passport_number), val(s.passport_expiry), val(s.eid_number),
            ], sigW, i % 2 === 1)),
        ],
    }));
    children.push(...blank(1));
}

// ── Section 3 — Shareholders (corp) ──────────────────────────────────────────
if (isCorp && d.shareholders && d.shareholders.length) {
    children.push(sectionHead('3. Shareholders & Ultimate Beneficial Owners'), blank(0)[0]);
    const shW = [1800, 900, 1100, 900, 700, 1200, 1000];
    children.push(new Table({
        width: { size: TABLE_W, type: WidthType.DXA },
        columnWidths: shW,
        borders: { top: thickBorder, bottom: thinBorder, left: thinBorder, right: thinBorder, insideH: thinBorder, insideV: noBorder },
        rows: [
            dataTableHeader(['Shareholder','Type','Nationality','Ownership %','UBO','Passport / ID','DOB'], shW),
            ...d.shareholders.map((s, i) => dataTableRow([
                val(s.name), val(s.shareholder_type), val(s.nationality),
                s.ownership_percentage ? parseFloat(s.ownership_percentage).toFixed(1) + '%' : '—',
                s.is_ubo ? 'Yes' : 'No',
                val(s.passport_number || s.eid_number),
                val(s.dob),
            ], shW, i % 2 === 1)),
        ],
    }));
    children.push(...blank(1));
}

// ── Section 4/2 — AML Risk Profile ───────────────────────────────────────────
children.push(sectionHead(sn('4', '2') + '. AML Risk Profile & Business Relationship'), blank(0)[0]);
children.push(infoTable([
    infoRow('Source of Funds',         val(d.source_of_funds_label)),
    infoRow('Source of Wealth',        val(d.source_of_wealth_label),        true),
    infoRow('Purpose of Relationship', val(d.purpose_of_relationship_label)),
    infoRow('Expected Monthly Volume', d.expected_monthly_volume ? 'AED ' + Number(d.expected_monthly_volume).toLocaleString() : '—', true),
    infoRow('Expected Frequency',      d.expected_monthly_frequency ? d.expected_monthly_frequency + ' transactions/month' : '—'),
    infoRow('Countries Involved',      val(d.countries_involved),             true),
]));
children.push(...blank(1));

// ── Section 5/3 — Risk Assessment ────────────────────────────────────────────
children.push(sectionHead(sn('5', '3') + '. Risk Assessment'), blank(0)[0]);
children.push(
    new Table({
        width: { size: TABLE_W, type: WidthType.DXA },
        columnWidths: [TABLE_W],
        borders: { top: thickBorder, bottom: thickBorder, left: thickBorder, right: thickBorder, insideH: noBorder, insideV: noBorder },
        rows: [new TableRow({ children: [new TableCell({
            width: { size: TABLE_W, type: WidthType.DXA },
            shading: { fill: LGREY, type: ShadingType.CLEAR },
            margins: { top: twp(5), bottom: twp(5), left: twp(8), right: twp(8) },
            borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
            children: [
                para(bold((d.risk_rating || 'UNRATED').toUpperCase() + ' RISK', { size: pt(11) }), { spacing: { after: 40 } }),
                d.risk_assessed_at ? para(run('Assessed: ' + d.risk_assessed_at + (d.risk_assessed_by ? '  ·  By: ' + d.risk_assessed_by : ''), { size: pt(9), color: DGREY }), { spacing: { after: 20 } }) : null,
                d.next_review_date  ? para(run('Next review: ' + d.next_review_date, { size: pt(9) }), { spacing: { after: 0 } }) : null,
            ].filter(Boolean),
        })]})],
    }),
);
if (d.risk_notes) {
    children.push(blank(0)[0], infoTable([infoRow('Risk Notes', val(d.risk_notes))]));
}
children.push(...blank(1));

// ── Section 6/4 — Sanctions & AML Screening ──────────────────────────────────
children.push(sectionHead(sn('6', '4') + '. Sanctions & AML Screening'), blank(0)[0]);
children.push(infoTable([
    infoRow('Screening Status',    (d.screening_status || 'NOT SCREENED').toUpperCase()),
    d.screening_date      ? infoRow('Last Screened',       val(d.screening_date),      true) : null,
    d.screening_reference ? infoRow('Screening Reference', val(d.screening_reference))        : null,
]));

// Sanctions list
const lists = [
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
children.push(
    blank(0)[0],
    para(bold('Sanctions & Watch-Lists Screened:', { size: pt(9) }), { spacing: { after: 40 } }),
    new Table({
        width: { size: TABLE_W, type: WidthType.DXA },
        columnWidths: [TABLE_W / 2, TABLE_W / 2],
        borders: { top: thinBorder, bottom: thinBorder, left: thinBorder, right: thinBorder, insideH: thinBorder, insideV: thinBorder },
        rows: chunk(lists, 2).map((pair, ri) => new TableRow({ children: pair.map((item, ci) =>
            new TableCell({
                width: { size: TABLE_W / 2, type: WidthType.DXA },
                shading: { fill: ri % 2 === 1 ? LGREY : 'FFFFFF', type: ShadingType.CLEAR },
                margins: { top: twp(2), bottom: twp(2), left: twp(6), right: twp(6) },
                borders: { top: noBorder, bottom: noBorder, left: noBorder, right: ci === 0 ? thinBorder : noBorder },
                children: [para(run('– ' + item, { size: pt(9) }), { spacing: { after: 0 } })],
            })
        ).concat(pair.length === 1 ? [new TableCell({
            width: { size: TABLE_W / 2, type: WidthType.DXA },
            shading: { fill: ri % 2 === 1 ? LGREY : 'FFFFFF', type: ShadingType.CLEAR },
            margins: { top: twp(2), bottom: twp(2), left: twp(6), right: twp(6) },
            borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
            children: [para(run(''), { spacing: { after: 0 } })],
        })] : [])})),
    }),
);
children.push(...blank(2));

// ── Signature block ───────────────────────────────────────────────────────────
children.push(new Table({
    width: { size: TABLE_W, type: WidthType.DXA },
    columnWidths: [2800, 2800, 2800],
    borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder, insideH: noBorder, insideV: noBorder },
    rows: [new TableRow({ children: [
        sigCell(d.signatory_name, d.signatory_title || 'Client / Authorised Signatory'),
        sigCell(d.mlro_name, 'MLRO / Compliance Officer'),
        sigCell('Date:', 'Date of Approval'),
    ]})],
}));

// ── Chunk helper ──────────────────────────────────────────────────────────────
function chunk(arr, n) {
    const out = [];
    for (let i = 0; i < arr.length; i += n) out.push(arr.slice(i, i + n));
    return out;
}

// ── Assemble document ─────────────────────────────────────────────────────────
const doc = new Document({
    sections: [{
        properties: {
            page: {
                margin: { top: twp(20), bottom: twp(20), left: twp(18), right: twp(18) },
            },
        },
        headers: {
            default: new Header({
                children: [
                    new Table({
                        width: { size: TABLE_W, type: WidthType.DXA },
                        columnWidths: [1800, 5000, 1800],
                        borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder, insideH: noBorder, insideV: noBorder },
                        rows: [new TableRow({ children: [
                            // Left: logo placeholder or blank
                            new TableCell({
                                width: { size: 1800, type: WidthType.DXA },
                                borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
                                margins: { top: 0, bottom: 0, left: 0, right: twp(4) },
                                children: [para(run(''), { spacing: { after: 0 } })],
                            }),
                            // Centre: tenant name + KYC title
                            new TableCell({
                                width: { size: 5000, type: WidthType.DXA },
                                borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
                                margins: { top: 0, bottom: 0, left: twp(4), right: twp(4) },
                                children: [
                                    para(new TextRun({ text: d.tenant_name.toUpperCase(), size: pt(8), color: DGREY }), { align: AlignmentType.CENTER, spacing: { after: 20 } }),
                                    para(new TextRun({ text: 'KNOW YOUR CUSTOMER', bold: true, size: pt(13) }), { align: AlignmentType.CENTER, spacing: { after: 20 } }),
                                    para(new TextRun({ text: 'CLIENT DUE DILIGENCE FILE', size: pt(7.5), color: DGREY }), { align: AlignmentType.CENTER, spacing: { after: 0 } }),
                                ],
                            }),
                            // Right: ref / date / DNFBP
                            new TableCell({
                                width: { size: 1800, type: WidthType.DXA },
                                borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
                                margins: { top: 0, bottom: 0, left: twp(4), right: 0 },
                                children: [
                                    para(bold(d.ref, { size: pt(9) }), { align: AlignmentType.RIGHT, spacing: { after: 20 } }),
                                    para(run(d.generated, { size: pt(7.5), color: DGREY }), { align: AlignmentType.RIGHT, spacing: { after: 20 } }),
                                    d.dnfbp_reg_no ? para(run('DNFBP: ' + d.dnfbp_reg_no, { size: pt(7.5), color: DGREY }), { align: AlignmentType.RIGHT, spacing: { after: 0 } }) : para(run(''), { spacing: { after: 0 } }),
                                ],
                            }),
                        ]})],
                    }),
                    new Paragraph({
                        children: [run('')],
                        spacing: { after: 0 },
                        border: { bottom: { style: BorderStyle.DOUBLE, size: 6, color: BLACK } },
                    }),
                    para(new TextRun({ text: 'CONFIDENTIAL — FOR COMPLIANCE PURPOSES ONLY', bold: true, size: pt(7.5), color: 'FFFFFF', allCaps: true }),
                        { align: AlignmentType.CENTER, spacing: { after: 0 },
                          shading: { fill: BLACK, type: ShadingType.CLEAR } }),
                ],
            }),
        },
        footers: {
            default: new Footer({
                children: [
                    new Paragraph({
                        children: [run('')],
                        spacing: { after: 40 },
                        border: { top: { style: BorderStyle.SINGLE, size: 4, color: MGREY } },
                    }),
                    new Table({
                        width: { size: TABLE_W, type: WidthType.DXA },
                        columnWidths: [5000, 3600],
                        borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder, insideH: noBorder, insideV: noBorder },
                        rows: [new TableRow({ children: [
                            new TableCell({
                                width: { size: 5000, type: WidthType.DXA },
                                borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
                                children: [para(run(d.tenant_name + (d.tenant_address ? '  ·  ' + d.tenant_address : '') + (d.tenant_email ? '  ·  ' + d.tenant_email : ''), { size: pt(7.5), color: DGREY }), { spacing: { after: 0 } })],
                            }),
                            new TableCell({
                                width: { size: 3600, type: WidthType.DXA },
                                borders: { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder },
                                children: [para(run(d.ref + '  ·  ' + d.generated, { size: pt(7.5), color: DGREY }), { align: AlignmentType.RIGHT, spacing: { after: 0 } })],
                            }),
                        ]})],
                    }),
                ],
            }),
        },
        children,
    }],
});

Packer.toBuffer(doc).then(buf => {
    fs.writeFileSync(outFile, buf);
    console.log('ok');
}).catch(err => {
    console.error(err.message);
    process.exit(1);
});
