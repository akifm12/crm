#!/usr/bin/env node
// scripts/generate-quotation.js
// Usage: node generate-quotation.js <json_data_file> <output_path>

const {
    Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
    AlignmentType, BorderStyle, WidthType, ShadingType, LevelFormat,
} = require('docx');
const fs = require('fs');

const dataFile   = process.argv[2];
const outputPath = process.argv[3];

if (!dataFile || !outputPath) {
    console.error('Usage: node generate-quotation.js <data.json> <output.docx>');
    process.exit(1);
}

const d = JSON.parse(fs.readFileSync(dataFile, 'utf8'));

const BLUE  = '1a56db';
const DARK  = '1e3a5f';
const GRAY  = 'f0f4f8';
const BLACK = '111111';
const border    = { style: BorderStyle.SINGLE, size: 1, color: 'CCCCCC' };
const borders   = { top: border, bottom: border, left: border, right: border };
const noBorder  = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };
const noBorders = { top: noBorder, bottom: noBorder, left: noBorder, right: noBorder };

function p(text, opts = {}) {
    return new Paragraph({
        spacing:   opts.spacing || { before: 80, after: 80 },
        alignment: opts.align   || AlignmentType.LEFT,
        children:  [new TextRun({
            text, font: 'Arial',
            size:    opts.size   || 20,
            bold:    opts.bold   || false,
            color:   opts.color  || BLACK,
            italics: opts.italic || false,
        })]
    });
}

function blank() { return new Paragraph({ children: [new TextRun('')] }); }

function cell(text, width, opts = {}) {
    return new TableCell({
        borders: opts.noBorder ? noBorders : borders,
        width: { size: width, type: WidthType.DXA },
        shading: opts.fill ? { fill: opts.fill, type: ShadingType.CLEAR } : undefined,
        margins: { top: 80, bottom: 80, left: 120, right: 120 },
        verticalAlign: opts.vAlign,
        children: [new Paragraph({
            alignment: opts.align || AlignmentType.LEFT,
            children: [new TextRun({
                text: String(text),
                font: 'Arial',
                size: opts.size || 20,
                bold: opts.bold || false,
                color: opts.color || BLACK,
            })]
        })]
    });
}

const lineItems = d.line_items || [];
const subtotal  = lineItems.reduce((sum, i) => sum + ((i.qty || 1) * (i.unit_price || 0)), 0);
const vat       = Math.round(subtotal * 0.05 * 100) / 100;
const total     = subtotal + vat;

const fmt = (n) => 'AED ' + Number(n).toLocaleString('en-AE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const doc = new Document({
    styles: { default: { document: { run: { font: 'Arial', size: 20, color: BLACK } } } },
    sections: [{
        properties: {
            page: {
                size: { width: 11906, height: 16838 },
                margin: { top: 1134, right: 1134, bottom: 1134, left: 1134 }
            }
        },
        children: [

            // ── LETTERHEAD ────────────────────────────────────────────────────

            // Top: BA name left, QT reference right
            new Table({
                width: { size: 9026, type: WidthType.DXA },
                columnWidths: [5500, 3526],
                rows: [new TableRow({ children: [
                    new TableCell({
                        borders: noBorders,
                        width: { size: 5500, type: WidthType.DXA },
                        children: [
                            new Paragraph({ children: [new TextRun({ text: 'Blue Arrow Management Consultants FZC', font: 'Arial', size: 28, bold: true, color: DARK })] }),
                            new Paragraph({ spacing: { before: 40 }, children: [new TextRun({ text: 'B1602, SRTIP Building, Sharjah, UAE', font: 'Arial', size: 18, color: '555555' })] }),
                            new Paragraph({ spacing: { before: 20 }, children: [new TextRun({ text: 'info@bluearrow.ae  |  050-8474481', font: 'Arial', size: 18, color: '555555' })] }),
                        ]
                    }),
                    new TableCell({
                        borders: noBorders,
                        width: { size: 3526, type: WidthType.DXA },
                        children: [
                            new Paragraph({ alignment: AlignmentType.RIGHT, children: [new TextRun({ text: 'QUOTATION', font: 'Arial', size: 32, bold: true, color: BLUE })] }),
                            new Paragraph({ alignment: AlignmentType.RIGHT, spacing: { before: 40 }, children: [new TextRun({ text: d.reference, font: 'Arial', size: 20, color: DARK })] }),
                        ]
                    }),
                ]})]
            }),

            // Divider
            new Paragraph({
                spacing: { before: 160, after: 240 },
                border: { bottom: { style: BorderStyle.SINGLE, size: 12, color: BLUE, space: 4 } },
                children: []
            }),

            // ── CLIENT + QUOTATION INFO ─────────────────────────────────────

            new Table({
                width: { size: 9026, type: WidthType.DXA },
                columnWidths: [4500, 4526],
                rows: [new TableRow({ children: [
                    // To (client)
                    new TableCell({
                        borders: noBorders,
                        width: { size: 4500, type: WidthType.DXA },
                        children: [
                            new Paragraph({ children: [new TextRun({ text: 'Quotation for:', font: 'Arial', size: 18, bold: true, color: '888888' })] }),
                            new Paragraph({ spacing: { before: 60 }, children: [new TextRun({ text: d.client_name, font: 'Arial', size: 22, bold: true, color: DARK })] }),
                            ...(d.client_address ? [new Paragraph({ spacing: { before: 40 }, children: [new TextRun({ text: d.client_address, font: 'Arial', size: 18, color: '555555' })] })] : []),
                            ...(d.client_email ? [new Paragraph({ spacing: { before: 20 }, children: [new TextRun({ text: d.client_email, font: 'Arial', size: 18, color: '555555' })] })] : []),
                        ]
                    }),
                    // Quotation metadata
                    new TableCell({
                        borders: noBorders,
                        width: { size: 4526, type: WidthType.DXA },
                        children: [
                            new Table({
                                width: { size: 4400, type: WidthType.DXA },
                                columnWidths: [1800, 2600],
                                rows: [
                                    ...[
                                        ['Quotation no.', d.reference],
                                        ['Date issued',   d.issued_date],
                                        ['Valid until',   d.valid_until],
                                        ['Subject',       d.subject],
                                    ].map(([k, v]) => new TableRow({ children: [
                                        new TableCell({ borders, shading: { fill: GRAY, type: ShadingType.CLEAR }, width: { size: 1800, type: WidthType.DXA }, margins: { top: 60, bottom: 60, left: 100, right: 100 }, children: [new Paragraph({ children: [new TextRun({ text: k, font: 'Arial', size: 18, bold: true, color: DARK })] })] }),
                                        new TableCell({ borders, width: { size: 2600, type: WidthType.DXA }, margins: { top: 60, bottom: 60, left: 100, right: 100 }, children: [new Paragraph({ children: [new TextRun({ text: String(v || ''), font: 'Arial', size: 18, color: BLACK })] })] }),
                                    ]}))
                                ]
                            })
                        ]
                    }),
                ]})]
            }),

            blank(), blank(),

            // ── LINE ITEMS TABLE ────────────────────────────────────────────

            new Table({
                width: { size: 9026, type: WidthType.DXA },
                columnWidths: [400, 5226, 1200, 1100, 1100],
                rows: [
                    // Header row
                    new TableRow({ children: [
                        cell('#',    400,  { fill: DARK, bold: true, color: 'FFFFFF', align: AlignmentType.CENTER }),
                        cell('Description', 5226, { fill: DARK, bold: true, color: 'FFFFFF' }),
                        cell('Qty',  1200, { fill: DARK, bold: true, color: 'FFFFFF', align: AlignmentType.CENTER }),
                        cell('Unit price', 1100, { fill: DARK, bold: true, color: 'FFFFFF', align: AlignmentType.RIGHT }),
                        cell('Amount', 1100, { fill: DARK, bold: true, color: 'FFFFFF', align: AlignmentType.RIGHT }),
                    ]}),

                    // Item rows
                    ...lineItems.map((item, i) => {
                        const qty    = Number(item.qty   || 1);
                        const price  = Number(item.unit_price || 0);
                        const amount = qty * price;
                        const fillRow = i % 2 === 1 ? GRAY : 'FFFFFF';
                        return new TableRow({ children: [
                            cell(String(i + 1), 400,  { fill: fillRow, align: AlignmentType.CENTER }),
                            cell(item.description || '', 5226, { fill: fillRow }),
                            cell(String(qty), 1200, { fill: fillRow, align: AlignmentType.CENTER }),
                            cell(price > 0 ? fmt(price) : 'As agreed', 1100, { fill: fillRow, align: AlignmentType.RIGHT }),
                            cell(price > 0 ? fmt(amount) : '—', 1100, { fill: fillRow, align: AlignmentType.RIGHT }),
                        ]});
                    }),

                    // Subtotal
                    new TableRow({ children: [
                        new TableCell({ borders: noBorders, columnSpan: 3, width: { size: 6826, type: WidthType.DXA }, children: [new Paragraph({ children: [] })] }),
                        cell('Subtotal', 1100, { fill: GRAY, bold: true, align: AlignmentType.RIGHT }),
                        cell(subtotal > 0 ? fmt(subtotal) : 'TBD', 1100, { fill: GRAY, align: AlignmentType.RIGHT }),
                    ]}),

                    // VAT
                    new TableRow({ children: [
                        new TableCell({ borders: noBorders, columnSpan: 3, width: { size: 6826, type: WidthType.DXA }, children: [new Paragraph({ children: [] })] }),
                        cell('VAT (5%)', 1100, { fill: GRAY, bold: true, align: AlignmentType.RIGHT }),
                        cell(subtotal > 0 ? fmt(vat) : 'TBD', 1100, { fill: GRAY, align: AlignmentType.RIGHT }),
                    ]}),

                    // Total
                    new TableRow({ children: [
                        new TableCell({ borders: noBorders, columnSpan: 3, width: { size: 6826, type: WidthType.DXA }, children: [new Paragraph({ children: [] })] }),
                        cell('TOTAL', 1100, { fill: DARK, bold: true, color: 'FFFFFF', align: AlignmentType.RIGHT }),
                        cell(subtotal > 0 ? fmt(total) : 'TBD', 1100, { fill: DARK, bold: true, color: 'FFFFFF', align: AlignmentType.RIGHT }),
                    ]}),
                ]
            }),

            blank(), blank(),

            // ── TERMS ──────────────────────────────────────────────────────

            ...(d.terms ? [
                new Paragraph({
                    spacing: { before: 100, after: 80 },
                    children: [new TextRun({ text: 'Terms & Conditions', font: 'Arial', size: 20, bold: true, color: DARK })]
                }),
                ...d.terms.split('\n').filter(l => l.trim()).map(line =>
                    new Paragraph({
                        spacing: { before: 40, after: 40 },
                        children: [new TextRun({ text: line, font: 'Arial', size: 18, color: '444444' })]
                    })
                ),
                blank(),
            ] : []),

            // ── ACCEPTANCE ─────────────────────────────────────────────────

            new Paragraph({
                spacing: { before: 100, after: 60 },
                border: { top: { style: BorderStyle.SINGLE, size: 4, color: 'DDDDDD', space: 4 } },
                children: [new TextRun({ text: 'To accept this quotation, please sign below and return a copy to info@bluearrow.ae', font: 'Arial', size: 18, italics: true, color: '666666' })]
            }),

            blank(),

            new Table({
                width: { size: 9026, type: WidthType.DXA },
                columnWidths: [4400, 4626],
                rows: [new TableRow({ children: [
                    new TableCell({
                        borders,
                        width: { size: 4400, type: WidthType.DXA },
                        margins: { top: 80, bottom: 240, left: 120, right: 120 },
                        children: [
                            new Paragraph({ spacing: { before: 0 }, children: [new TextRun({ text: 'For Blue Arrow Management Consultants FZC', font: 'Arial', size: 18, bold: true, color: DARK })] }),
                            new Paragraph({ spacing: { before: 400 }, children: [new TextRun({ text: 'Signature: ______________________', font: 'Arial', size: 18, color: '888888' })] }),
                            new Paragraph({ spacing: { before: 100 }, children: [new TextRun({ text: 'Date: ___________________________', font: 'Arial', size: 18, color: '888888' })] }),
                        ]
                    }),
                    new TableCell({
                        borders,
                        shading: { fill: GRAY, type: ShadingType.CLEAR },
                        width: { size: 4626, type: WidthType.DXA },
                        margins: { top: 80, bottom: 240, left: 120, right: 120 },
                        children: [
                            new Paragraph({ children: [new TextRun({ text: `For ${d.client_name}`, font: 'Arial', size: 18, bold: true, color: DARK })] }),
                            new Paragraph({ spacing: { before: 400 }, children: [new TextRun({ text: 'Signature: ______________________', font: 'Arial', size: 18, color: '888888' })] }),
                            new Paragraph({ spacing: { before: 100 }, children: [new TextRun({ text: 'Date: ___________________________', font: 'Arial', size: 18, color: '888888' })] }),
                            new Paragraph({ spacing: { before: 100 }, children: [new TextRun({ text: 'Company Stamp:', font: 'Arial', size: 18, color: '888888' })] }),
                        ]
                    }),
                ]})]
            }),

            blank(),

            // Footer
            new Paragraph({
                alignment: AlignmentType.CENTER,
                children: [new TextRun({
                    text: 'Blue Arrow Management Consultants FZC is licensed under Sharjah Research Technology & Innovation Park',
                    font: 'Arial', size: 16, italics: true, color: '999999'
                })]
            }),
        ]
    }]
});

Packer.toBuffer(doc).then(buffer => {
    fs.writeFileSync(outputPath, buffer);
    console.log('OK:' + outputPath);
});
