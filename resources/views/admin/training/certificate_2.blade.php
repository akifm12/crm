<html>
<head><meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    width: 297mm; height: 210mm;
    font-family: Arial, Helvetica, sans-serif;
    background: #fff;
    color: #1e293b;
}
.left-panel {
    position: absolute; top: 0; left: 0; bottom: 0; width: 72mm;
    background: #1e3a5f;
}
.left-accent {
    position: absolute; top: 0; left: 64mm; bottom: 0; width: 8mm;
    background: linear-gradient(to bottom, #2563eb, #1e3a5f);
    opacity: 0.4;
}
.left-content {
    position: absolute; top: 0; left: 0; bottom: 0; width: 64mm;
    padding: 18mm 10mm;
    display: block;
}
.left-logo { margin-bottom: 6mm; }
.left-logo img { max-width: 44mm; max-height: 18mm; }
.left-issuer { color: #93c5fd; font-size: 7.5pt; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8mm; line-height: 1.6; }

.left-badge {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 6px;
    padding: 6mm 5mm;
    margin-bottom: 6mm;
}
.left-badge-label { color: #93c5fd; font-size: 6.5pt; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 2mm; }
.left-badge-value { color: #fff; font-size: 9pt; font-weight: bold; line-height: 1.4; }

.cert-no-left { position: absolute; bottom: 10mm; left: 10mm; color: rgba(255,255,255,0.3); font-size: 6.5pt; letter-spacing: 1px; }

.right-panel {
    position: absolute; top: 0; left: 80mm; right: 0; bottom: 0;
    padding: 18mm 16mm 16mm;
}
.cert-label { font-size: 7.5pt; letter-spacing: 3px; text-transform: uppercase; color: #2563eb; font-weight: bold; margin-bottom: 3mm; }
.cert-heading { font-size: 22pt; font-weight: 900; color: #1e293b; line-height: 1.1; margin-bottom: 6mm; }
.cert-heading span { color: #2563eb; }

.presented-to { font-size: 8pt; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2mm; }
.employee-name { font-size: 26pt; font-weight: 800; color: #1e3a5f; margin-bottom: 1mm; line-height: 1.1; }
.employee-role { font-size: 9pt; color: #64748b; margin-bottom: 6mm; }

.desc { font-size: 9.5pt; color: #475569; line-height: 1.7; margin-bottom: 6mm; }
.training-name { color: #1e3a5f; font-weight: bold; }

.divider-line { height: 2px; background: linear-gradient(to right, #2563eb, #93c5fd, transparent); margin: 4mm 0; }

.footer { position: absolute; bottom: 12mm; left: 96mm; right: 16mm; }
.sig-table { width: 100%; border-collapse: collapse; }
.sig-table td { width: 50%; vertical-align: top; padding-right: 8mm; }
.sig-line-bar { border-top: 1.5px solid #cbd5e1; margin-bottom: 2mm; margin-top: 12mm; }
.sig-name  { font-size: 9pt; font-weight: bold; color: #1e293b; }
.sig-title { font-size: 7.5pt; color: #94a3b8; }

.watermark {
    position: absolute; bottom: 14mm; right: 18mm;
    font-size: 48pt; font-weight: 900; color: rgba(37,99,235,0.04);
    text-transform: uppercase; letter-spacing: -2px;
    pointer-events: none;
}
</style>
</head>
<body>

<div class="left-panel"></div>
<div class="left-accent"></div>

<div class="left-content">
    <div class="left-logo">
        @if($logoB64)
        <img src="{{ $logoB64 }}" alt="Blue Arrow">
        @else
        <div style="color:#fff; font-size:14pt; font-weight:900;">Blue Arrow</div>
        @endif
    </div>
    <div class="left-issuer">Blue Arrow<br>Consultancy<br>Training Division</div>

    <div class="left-badge">
        <div class="left-badge-label">Organisation</div>
        <div class="left-badge-value">{{ $training->client->company_name }}</div>
    </div>
    <div class="left-badge">
        <div class="left-badge-label">Date Issued</div>
        <div class="left-badge-value">{{ $training->training_date->format('d M Y') }}</div>
    </div>
    @if($training->expiry_date)
    <div class="left-badge">
        <div class="left-badge-label">Valid Until</div>
        <div class="left-badge-value">{{ $training->expiry_date->format('d M Y') }}</div>
    </div>
    @endif

    <div class="cert-no-left">BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}</div>
</div>

<div class="watermark">&#10003;</div>

<div class="right-panel">
    <div class="cert-label">Certificate of Completion</div>
    <div class="cert-heading">Training<br><span>Achievement</span></div>

    <div class="presented-to">Awarded to</div>
    <div class="employee-name">{{ $training->employee_name }}</div>
    @if($training->employee_role)
    <div class="employee-role">{{ $training->employee_role }}</div>
    @endif

    <div class="divider-line"></div>

    <div class="desc">
        This certificate is awarded in recognition of successfully completing the<br>
        <span class="training-name">{{ $training->training_type }}</span> programme
        @if($training->trainer), delivered by <strong>{{ $training->trainer }}</strong>@endif.
    </div>

    <div class="footer">
        <table class="sig-table"><tr>
            <td>
                <div class="sig-line-bar"></div>
                <div class="sig-name">{{ $training->signatory_name ?: 'Authorised Signatory' }}</div>
                <div class="sig-title">{{ $training->signatory_title ?: 'Blue Arrow Consultancy' }}</div>
            </td>
            <td>
                <div class="sig-line-bar"></div>
                <div class="sig-name">{{ $training->training_date->format('d F Y') }}</div>
                <div class="sig-title">Date of Issue</div>
            </td>
        </tr></table>
    </div>
</div>

</body>
</html>
