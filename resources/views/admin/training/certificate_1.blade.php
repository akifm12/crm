<html>
<head><meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    width: 297mm; height: 210mm;
    font-family: Georgia, 'Times New Roman', serif;
    background: #fff;
    color: #1a1a2e;
}
.outer-border {
    position: absolute; top: 8mm; left: 8mm; right: 8mm; bottom: 8mm;
    border: 3px solid #b8963e;
}
.inner-border {
    position: absolute; top: 11mm; left: 11mm; right: 11mm; bottom: 11mm;
    border: 1px solid #b8963e;
}
.page {
    position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    padding: 20mm 22mm 16mm;
    display: block;
}
.corner {
    position: absolute;
    width: 18mm; height: 18mm;
}
.corner svg { width: 100%; height: 100%; }
.c-tl { top: 5mm; left: 5mm; }
.c-tr { top: 5mm; right: 5mm; transform: scaleX(-1); }
.c-bl { bottom: 5mm; left: 5mm; transform: scaleY(-1); }
.c-br { bottom: 5mm; right: 5mm; transform: scale(-1); }

.header { text-align: center; margin-bottom: 6mm; }
.logo   { height: 14mm; margin-bottom: 3mm; }
.issuer-name { font-size: 11pt; color: #b8963e; font-family: Arial, sans-serif; letter-spacing: 3px; text-transform: uppercase; font-weight: bold; }
.divider { width: 80mm; height: 1px; background: linear-gradient(to right, transparent, #b8963e, transparent); margin: 3mm auto; }

.cert-of { font-size: 9pt; letter-spacing: 4px; text-transform: uppercase; color: #666; font-family: Arial, sans-serif; text-align: center; margin-bottom: 2mm; }
.cert-title { font-size: 28pt; color: #1a1a2e; text-align: center; margin-bottom: 5mm; line-height: 1.1; }
.cert-title em { color: #b8963e; font-style: normal; }

.body-text { text-align: center; font-size: 10pt; color: #444; line-height: 1.7; margin-bottom: 4mm; }
.employee-name { font-size: 22pt; color: #1a1a2e; text-align: center; border-bottom: 1.5px solid #b8963e; display: inline-block; padding: 0 12mm 1mm; margin: 2mm 0 4mm; }
.training-name { font-size: 13pt; font-style: italic; color: #b8963e; text-align: center; margin-bottom: 1mm; }
.meta { font-size: 9pt; color: #666; font-family: Arial, sans-serif; text-align: center; letter-spacing: 0.5px; }

.footer { position: absolute; bottom: 16mm; left: 22mm; right: 22mm; }
.sig-row { width: 100%; }
.sig-row td { width: 33%; text-align: center; vertical-align: bottom; padding: 0 8mm; }
.sig-line { border-top: 1px solid #b8963e; padding-top: 2mm; }
.sig-name  { font-size: 9pt; font-weight: bold; color: #1a1a2e; font-family: Arial, sans-serif; }
.sig-title { font-size: 7.5pt; color: #888; font-family: Arial, sans-serif; }
.cert-no { font-size: 7pt; color: #aaa; font-family: Arial, sans-serif; text-align: right; position: absolute; bottom: 4mm; right: 14mm; letter-spacing: 0.5px; }
</style>
</head>
<body>
<div class="outer-border"></div>
<div class="inner-border"></div>

{{-- Corner ornaments --}}
@foreach(['c-tl','c-tr','c-bl','c-br'] as $cls)
<div class="corner {{ $cls }}">
<svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M2 2 L30 2 L2 30 Z" fill="#b8963e" opacity="0.3"/>
  <path d="M2 2 L18 2 L2 18 Z" fill="#b8963e"/>
  <circle cx="2" cy="2" r="2.5" fill="#b8963e"/>
  <circle cx="30" cy="2" r="1.5" fill="#b8963e" opacity="0.5"/>
  <circle cx="2" cy="30" r="1.5" fill="#b8963e" opacity="0.5"/>
</svg>
</div>
@endforeach

<div class="page">
    <div class="header">
        @if($logoB64)
        <img src="{{ $logoB64 }}" class="logo" alt="Blue Arrow">
        @endif
        <div class="issuer-name">Blue Arrow Consultancy</div>
    </div>
    <div class="divider"></div>

    <div class="cert-of">Certificate of Completion</div>
    <div class="cert-title">This certifies that</div>

    <div style="text-align:center;">
        <span class="employee-name">{{ $training->employee_name }}</span>
    </div>
    @if($training->employee_role)
    <div class="body-text" style="font-size:8.5pt; color:#888;">{{ $training->employee_role }} &nbsp;·&nbsp; {{ $training->client->company_name }}</div>
    @else
    <div class="body-text" style="font-size:8.5pt; color:#888;">{{ $training->client->company_name }}</div>
    @endif

    <div class="body-text" style="margin-top:3mm;">has successfully completed the training programme</div>
    <div class="training-name">&ldquo;{{ $training->training_type }}&rdquo;</div>
    <div class="meta">
        on {{ $training->training_date->format('d F Y') }}
        @if($training->trainer) &nbsp;·&nbsp; Delivered by {{ $training->trainer }} @endif
        @if($training->expiry_date) &nbsp;·&nbsp; Valid until {{ $training->expiry_date->format('d F Y') }} @endif
    </div>

    <div class="footer">
        <table class="sig-row"><tr>
            <td>
                <div class="sig-line">
                    <div class="sig-name">{{ $training->signatory_name ?: 'Authorised Signatory' }}</div>
                    <div class="sig-title">{{ $training->signatory_title ?: 'Blue Arrow Consultancy' }}</div>
                </div>
            </td>
            <td></td>
            <td>
                <div class="sig-line">
                    <div class="sig-name">{{ $training->training_date->format('d F Y') }}</div>
                    <div class="sig-title">Date of Issue</div>
                </div>
            </td>
        </tr></table>
    </div>
</div>
<div class="cert-no">Cert No: BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}</div>
</body>
</html>
