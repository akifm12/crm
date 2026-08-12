<html>
<head><meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    width: 297mm; height: 210mm;
    font-family: Arial, Helvetica, sans-serif;
    background: #0f172a;
    color: #f1f5f9;
}
.bg-pattern {
    position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background-image: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 40px,
        rgba(255,255,255,0.015) 40px,
        rgba(255,255,255,0.015) 41px
    );
}
.top-bar {
    position: absolute; top: 0; left: 0; right: 0; height: 2mm;
    background: linear-gradient(to right, #f59e0b, #d97706, #f59e0b);
}
.bottom-bar {
    position: absolute; bottom: 0; left: 0; right: 0; height: 2mm;
    background: linear-gradient(to right, #f59e0b, #d97706, #f59e0b);
}
.page { position: absolute; top: 0; left: 0; right: 0; bottom: 0; padding: 14mm 20mm 14mm; }

.header { display: block; margin-bottom: 5mm; }
.header-table { width: 100%; border-collapse: collapse; }
.header-table td { vertical-align: middle; }
.logo-cell { width: 50mm; }
.logo-cell img { max-height: 14mm; max-width: 44mm; }
.logo-cell .text-logo { font-size: 14pt; font-weight: 900; color: #fff; letter-spacing: -0.5px; }
.header-right { text-align: right; }
.cert-badge {
    display: inline-block;
    border: 1px solid #f59e0b;
    color: #f59e0b;
    font-size: 7pt;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 2px;
}
.cert-no { font-size: 7pt; color: rgba(255,255,255,0.3); margin-top: 2mm; letter-spacing: 1px; }

.gold-line { height: 1px; background: linear-gradient(to right, transparent, #f59e0b, transparent); margin: 4mm 0; }

.main { text-align: center; }
.this-certifies { font-size: 8.5pt; color: rgba(255,255,255,0.4); letter-spacing: 3px; text-transform: uppercase; margin-bottom: 4mm; }
.employee-name {
    font-size: 30pt; font-weight: 900; color: #fff;
    letter-spacing: -0.5px; margin-bottom: 2mm; line-height: 1;
}
.employee-sub { font-size: 9pt; color: #f59e0b; margin-bottom: 5mm; letter-spacing: 0.5px; }
.completed-text { font-size: 8.5pt; color: rgba(255,255,255,0.5); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 2mm; }
.training-name { font-size: 15pt; font-weight: bold; color: #f59e0b; margin-bottom: 2mm; }
.meta-pills { margin-top: 3mm; }
.pill {
    display: inline-block; background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 20px; padding: 1.5px 8px; margin: 0 2px;
    font-size: 7.5pt; color: rgba(255,255,255,0.5); letter-spacing: 0.5px;
}

.footer { position: absolute; bottom: 10mm; left: 20mm; right: 20mm; }
.sig-table { width: 100%; border-collapse: collapse; }
.sig-table td { width: 33%; text-align: center; vertical-align: top; padding: 0 6mm; }
.sig-bar { border-top: 1px solid rgba(245,158,11,0.4); padding-top: 2mm; margin-top: 10mm; }
.sig-name  { font-size: 8.5pt; font-weight: bold; color: #fff; }
.sig-title { font-size: 7pt; color: rgba(255,255,255,0.4); margin-top: 1mm; }
</style>
</head>
<body>
<div class="bg-pattern"></div>
<div class="top-bar"></div>
<div class="bottom-bar"></div>

<div class="page">
    <div class="header">
        <table class="header-table"><tr>
            <td class="logo-cell">
                @if($logoB64)
                <img src="{{ $logoB64 }}" alt="Blue Arrow">
                @else
                <div class="text-logo">Blue Arrow</div>
                @endif
            </td>
            <td class="header-right">
                <div class="cert-badge">Certificate of Achievement</div>
                <div class="cert-no">No. BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr></table>
    </div>

    <div class="gold-line"></div>

    <div class="main">
        <div class="this-certifies">This is to certify that</div>
        <div class="employee-name">{{ $training->employee_name }}</div>
        <div class="employee-sub">
            @if($training->employee_role){{ $training->employee_role }} &nbsp;&bull;&nbsp; @endif
            {{ $training->client->company_name }}
        </div>

        <div class="completed-text">has successfully completed</div>
        <div class="training-name">{{ $training->training_type }}</div>

        <div class="meta-pills">
            <span class="pill">{{ $training->training_date->format('d F Y') }}</span>
            @if($training->trainer)<span class="pill">{{ $training->trainer }}</span>@endif
            @if($training->expiry_date)<span class="pill">Valid to {{ $training->expiry_date->format('d M Y') }}</span>@endif
        </div>
    </div>

    <div class="footer">
        <table class="sig-table"><tr>
            <td>
                <div class="sig-bar">
                    <div class="sig-name">{{ $training->signatory_name ?: '___________________' }}</div>
                    <div class="sig-title">{{ $training->signatory_title ?: 'Authorised Signatory' }}</div>
                </div>
            </td>
            <td></td>
            <td>
                <div class="sig-bar">
                    <div class="sig-name">{{ $training->training_date->format('d F Y') }}</div>
                    <div class="sig-title">Date of Issue</div>
                </div>
            </td>
        </tr></table>
    </div>
</div>

</body>
</html>
