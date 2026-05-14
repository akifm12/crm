#!/usr/bin/env node
// scripts/generate-sla.js
// Usage: node generate-sla.js <json_data_file> <output_path>

const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, ShadingType, LevelFormat,
    HeadingLevel
} = require('docx');
const fs = require('fs');

const dataFile  = process.argv[2];
const outputPath = process.argv[3];

if (!dataFile || !outputPath) {
    console.error('Usage: node generate-sla.js <data.json> <output.docx>');
    process.exit(1);
}

const d = JSON.parse(fs.readFileSync(dataFile, 'utf8'));

// ── Colours ────────────────────────────────────────────────────────────────
const BLUE  = '1a56db';
const DARK  = '1e3a5f';
const GRAY  = 'f0f4f8';
const BLACK = '111111';

// ── Borders ────────────────────────────────────────────────────────────────
const border    = { style: BorderStyle.SINGLE, size: 1, color: 'CCCCCC' };
const borders   = { top: border, bottom: border, left: border, right: border };
const noBorder  = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };
const noBorders = { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder };

// ── Helpers ────────────────────────────────────────────────────────────────

function p(text, opts = {}) {
    return new Paragraph({
        spacing:   opts.spacing   || { before: 100, after: 100 },
        alignment: opts.align     || AlignmentType.JUSTIFIED,
        border:    opts.border    || undefined,
        children:  [new TextRun({
            text,
            font:    'Arial',
            size:    opts.size    || 20,
            bold:    opts.bold    || false,
            color:   opts.color   || BLACK,
            italics: opts.italic  || false,
        })]
    });
}

function blank() {
    return new Paragraph({ spacing: { before: 60, after: 60 }, children: [new TextRun('')] });
}

function sectionHeading(text) {
    return new Paragraph({
        spacing: { before: 320, after: 100 },
        border:  { bottom: { style: BorderStyle.SINGLE, size: 6, color: BLUE, space: 4 } },
        children: [new TextRun({ text, font: 'Arial', size: 22, bold: true, color: DARK })]
    });
}

// Parse bullet text — lines starting with "- " become bullet items
function textToParagraphs(text, numbering) {
    if (!text) return [blank()];
    return text.split('\n').map(line => {
        const isBullet = line.trim().startsWith('- ');
        const content  = isBullet ? line.trim().slice(2) : line;
        if (!content.trim()) return blank();
        return new Paragraph({
            spacing: { before: 60, after: 60 },
            alignment: AlignmentType.JUSTIFIED,
            numbering: isBullet ? { reference: numbering, level: 0 } : undefined,
            children: [new TextRun({ text: content, font: 'Arial', size: 20, color: BLACK })]
        });
    });
}

function sigCell(lines, width, shade) {
    return new TableCell({
        borders,
        width: { size: width, type: WidthType.DXA },
        shading: shade ? { fill: GRAY, type: ShadingType.CLEAR } : undefined,
        margins: { top: 100, bottom: 200, left: 120, right: 120 },
        children: lines.map((line, i) => new Paragraph({
            spacing: { before: i === 0 ? 160 : 60, after: 60 },
            children: [new TextRun({
                text: line,
                font: 'Arial',
                size: 18,
                bold: line.startsWith('__'),
                color: line.startsWith('__') ? '666666' : BLACK,
            })]
        }))
    });
}

// ── Document ───────────────────────────────────────────────────────────────

const doc = new Document({
    numbering: {
        config: [{
            reference: 'bullets',
            levels: [{ level: 0, format: LevelFormat.BULLET, text: '\u2022',
                alignment: AlignmentType.LEFT,
                style: { paragraph: { indent: { left: 720, hanging: 360 } } }
            }]
        }]
    },
    styles: {
        default: { document: { run: { font: 'Arial', size: 20, color: BLACK } } }
    },
    sections: [{
        properties: {
            page: {
                size: { width: 11906, height: 16838 },
                margin: { top: 1134, right: 1134, bottom: 1134, left: 1134 }
            }
        },
        children: [

            // ── HEADER ──────────────────────────────────────────────────────

            // BA name large
            new Paragraph({
                spacing: { before: 0, after: 60 },
                children: [new TextRun({ text: 'Blue Arrow Management Consultants FZC', font: 'Arial', size: 30, bold: true, color: DARK })]
            }),
            new Paragraph({
                spacing: { before: 0, after: 40 },
                children: [new TextRun({ text: 'B1602, SRTIP Building, Sharjah, UAE', font: 'Arial', size: 18, color: '555555' })]
            }),
            new Paragraph({
                spacing: { before: 0, after: 200 },
                children: [new TextRun({ text: 'info@bluearrow.ae  |  Tel: 050-8474481', font: 'Arial', size: 18, color: '555555' })]
            }),

            // Divider
            new Paragraph({
                spacing: { before: 0, after: 240 },
                border: { bottom: { style: BorderStyle.SINGLE, size: 12, color: BLUE, space: 4 } },
                children: []
            }),

            // Title
            new Paragraph({
                spacing: { before: 200, after: 100 },
                alignment: AlignmentType.CENTER,
                children: [new TextRun({ text: 'SERVICE LEVEL AGREEMENT', font: 'Arial', size: 32, bold: true, color: DARK })]
            }),

            // For / By / Date block
            blank(),
            ...['For:  ' + d.client_name,
                'By:   Blue Arrow Management Consultants FZC',
                'Effective Date: ' + d.start_date,
            ].map(line => p(line, { bold: line.startsWith('For') || line.startsWith('By'), size: 22 })),

            blank(),

            // Signatory table
            new Table({
                width: { size: 9026, type: WidthType.DXA },
                columnWidths: [3000, 2000, 2000, 2026],
                rows: [
                    new TableRow({
                        children: [
                            new TableCell({ borders, shading: { fill: DARK, type: ShadingType.CLEAR }, width: { size: 3000, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: 'Entity', font: 'Arial', size: 18, bold: true, color: 'FFFFFF' })] })] }),
                            new TableCell({ borders, shading: { fill: DARK, type: ShadingType.CLEAR }, width: { size: 2000, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: 'Signatory', font: 'Arial', size: 18, bold: true, color: 'FFFFFF' })] })] }),
                            new TableCell({ borders, shading: { fill: DARK, type: ShadingType.CLEAR }, width: { size: 2000, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: 'Signature', font: 'Arial', size: 18, bold: true, color: 'FFFFFF' })] })] }),
                            new TableCell({ borders, shading: { fill: DARK, type: ShadingType.CLEAR }, width: { size: 2026, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: 'Dated', font: 'Arial', size: 18, bold: true, color: 'FFFFFF' })] })] }),
                        ]
                    }),
                    new TableRow({
                        children: [
                            new TableCell({ borders, width: { size: 3000, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: 'Blue Arrow Management Consultants FZC', font: 'Arial', size: 18 })] })] }),
                            new TableCell({ borders, width: { size: 2000, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: 'Akif Mushtaq', font: 'Arial', size: 18 })] })] }),
                            new TableCell({ borders, width: { size: 2000, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: '', font: 'Arial', size: 18 })] })] }),
                            new TableCell({ borders, width: { size: 2026, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: d.start_date, font: 'Arial', size: 18 })] })] }),
                        ]
                    }),
                    new TableRow({
                        children: [
                            new TableCell({ borders, width: { size: 3000, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: d.client_name, font: 'Arial', size: 18 })] })] }),
                            new TableCell({ borders, width: { size: 2000, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: d.client_signatory || '', font: 'Arial', size: 18 })] })] }),
                            new TableCell({ borders, width: { size: 2000, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: '', font: 'Arial', size: 18 })] })] }),
                            new TableCell({ borders, width: { size: 2026, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [new Paragraph({ children: [new TextRun({ text: d.start_date, font: 'Arial', size: 18 })] })] }),
                        ]
                    }),
                ]
            }),

            blank(),

            // ── BODY SECTIONS ───────────────────────────────────────────────

            sectionHeading('1. Agreement Overview'),
            p(`This Agreement represents a Service Level Agreement ("SLA" or "Agreement") between Blue Arrow Management Consultants FZC (the "Service Provider") and ${d.client_name} (the "Customer") for the provisioning of services required to support and sustain ${d.service_type}.`),
            p(`This Agreement and all its stipulations apply to ${d.client_name}.`),
            p('This Agreement remains valid until superseded by a revised agreement mutually endorsed by the stakeholders.'),

            sectionHeading('2. Goals and Objectives'),
            p('The purpose of this Agreement is to ensure that the proper elements and commitments are in place to provide consistent service support and delivery to the Customer by the Service Provider.'),
            p('The objectives of this Agreement are to:'),
            ...['Provide clear reference to service ownership, accountability, roles and responsibilities.',
                'Present a clear, concise and measurable description of service provision to the Customer.',
                'Match perceptions of the expected service provision with actual service support and delivery.'
            ].map(text => new Paragraph({
                spacing: { before: 60, after: 60 },
                numbering: { reference: 'bullets', level: 0 },
                children: [new TextRun({ text, font: 'Arial', size: 20, color: BLACK })]
            })),

            sectionHeading('3. Stakeholders'),
            p(`Service Provider: Blue Arrow Management Consultants FZC ("Provider")`, { bold: false }),
            p(`Customer: ${d.client_name} ("Customer")`, { bold: false }),

            sectionHeading('4. Periodic Review'),
            p('This Agreement is valid from the Effective Date and remains valid until further notice. This Agreement should be reviewed at a minimum once per fiscal year. The Business Relationship Manager is responsible for facilitating regular reviews of this document.'),
            p('Business Relationship Manager: Blue Arrow Management Consultants FZC  |  Review Period: Quarterly'),

            sectionHeading('5. Scope of Services'),
            ...textToParagraphs(d.scope_of_work, 'bullets'),

            ...(d.client_obligations ? [
                sectionHeading('6. Customer Requirements'),
                ...textToParagraphs(d.client_obligations, 'bullets'),
            ] : []),

            ...(d.deliverables ? [
                sectionHeading('7. Deliverables'),
                ...textToParagraphs(d.deliverables, 'bullets'),
            ] : []),

            sectionHeading('8. Service Availability'),
            ...['Telephone support: 9:00 A.M. to 5:00 P.M. Monday – Friday.',
                'Email support: Monitored 8:00 A.M. to 6:00 P.M. Monday – Friday.',
                'Onsite assistance guaranteed within 72 hours during the business week.',
            ].map(text => new Paragraph({
                spacing: { before: 60, after: 60 },
                numbering: { reference: 'bullets', level: 0 },
                children: [new TextRun({ text, font: 'Arial', size: 20, color: BLACK })]
            })),

            sectionHeading('9. Response Times'),
            ...['0–8 hours (during business hours) for issues classified as High priority.',
                'Within 24 hours for issues classified as Medium priority.',
                'Within 3 working days for issues classified as Low priority.',
            ].map(text => new Paragraph({
                spacing: { before: 60, after: 60 },
                numbering: { reference: 'bullets', level: 0 },
                children: [new TextRun({ text, font: 'Arial', size: 20, color: BLACK })]
            })),

            ...(d.fee ? [
                sectionHeading('10. Fees'),
                p(`The fee for services under this Agreement is AED ${d.fee.toLocaleString()}${d.fee_frequency ? ' per ' + d.fee_frequency : ''}.`),
                blank(),
                ...textToParagraphs(d.payment_terms || '', 'bullets'),
            ] : []),

            ...(d.termination_clause ? [
                sectionHeading('11. Termination'),
                ...textToParagraphs(d.termination_clause, 'bullets'),
            ] : []),

            ...(d.governing_law ? [
                sectionHeading('12. Governing Law'),
                p(d.governing_law),
            ] : []),

            blank(), blank(),

            // ── SIGNATURE BLOCK ──────────────────────────────────────────────

            new Paragraph({
                spacing: { before: 200, after: 100 },
                border: { top: { style: BorderStyle.SINGLE, size: 6, color: BLUE, space: 4 } },
                children: [new TextRun({ text: 'Authorised Signatures', font: 'Arial', size: 20, bold: true, color: DARK })]
            }),

            new Table({
                width: { size: 9026, type: WidthType.DXA },
                columnWidths: [4400, 4626],
                rows: [new TableRow({
                    children: [
                        sigCell([
                            'For Blue Arrow Management Consultants FZC',
                            '', '', '',
                            'Akif Mushtaq',
                            'Director, Head of Compliance',
                            '',
                            'Amita Parulekar',
                            'Director, Head of Finance',
                            '', '',
                            '__Signature: _______________________',
                            '__Date: ____________________________',
                        ], 4400, false),
                        sigCell([
                            `For ${d.client_name}`,
                            '', '', '',
                            d.client_signatory || '___________________________',
                            d.client_signatory_title || 'Authorised Signatory',
                            '', '', '', '',
                            '__Signature: _______________________',
                            '__Date: ____________________________',
                            '__Company Stamp:',
                        ], 4626, true),
                    ]
                })]
            }),

            blank(),

            // ── FOOTER ──────────────────────────────────────────────────────
            new Paragraph({
                spacing: { before: 200, after: 0 },
                alignment: AlignmentType.CENTER,
                children: [new TextRun({
                    text: 'Blue Arrow Management Consultants FZC is licensed under Sharjah Research Technology & Innovation Park',
                    font: 'Arial', size: 16, italics: true, color: '888888'
                })]
            }),

        ]
    }]
});

Packer.toBuffer(doc).then(buffer => {
    fs.writeFileSync(outputPath, buffer);
    console.log('OK:' + outputPath);
});
