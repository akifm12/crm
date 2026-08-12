<html>
<head><meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { width: 297mm; height: 210mm; font-family: Georgia, 'Times New Roman', serif; background: #fff; color: #1a1a2e; }
</style>
</head>
<body>

{{-- ── OUTER BORDER (position:absolute works for non-text elements) ────────── --}}
<div style="position:absolute; top:6mm; left:6mm; width:285mm; height:190mm; border:2.5px solid #b8963e;"></div>

{{-- ── CORNER ORNAMENTS — unique SVG per corner (mPDF ignores CSS transform) ── --}}

{{-- Top-left --}}
<div style="position:absolute; top:2mm; left:2mm; width:18mm; height:18mm;">
<svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M0 0 L52 0 L0 52 Z" fill="#b8963e" opacity="0.18"/>
  <path d="M0 0 L22 0 L0 22 Z" fill="#b8963e"/>
  <circle cx="0" cy="0" r="3" fill="#b8963e"/>
  <circle cx="50" cy="0" r="1.8" fill="#b8963e" opacity="0.35"/>
  <circle cx="0" cy="50" r="1.8" fill="#b8963e" opacity="0.35"/>
</svg>
</div>

{{-- Top-right --}}
<div style="position:absolute; top:2mm; left:273mm; width:18mm; height:18mm;">
<svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M60 0 L8 0 L60 52 Z" fill="#b8963e" opacity="0.18"/>
  <path d="M60 0 L38 0 L60 22 Z" fill="#b8963e"/>
  <circle cx="60" cy="0" r="3" fill="#b8963e"/>
  <circle cx="10" cy="0" r="1.8" fill="#b8963e" opacity="0.35"/>
  <circle cx="60" cy="50" r="1.8" fill="#b8963e" opacity="0.35"/>
</svg>
</div>

{{-- Bottom-left --}}
<div style="position:absolute; top:178mm; left:2mm; width:18mm; height:18mm;">
<svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M0 60 L52 60 L0 8 Z" fill="#b8963e" opacity="0.18"/>
  <path d="M0 60 L22 60 L0 38 Z" fill="#b8963e"/>
  <circle cx="0" cy="60" r="3" fill="#b8963e"/>
  <circle cx="50" cy="60" r="1.8" fill="#b8963e" opacity="0.35"/>
  <circle cx="0" cy="10" r="1.8" fill="#b8963e" opacity="0.35"/>
</svg>
</div>

{{-- Bottom-right --}}
<div style="position:absolute; top:178mm; left:273mm; width:18mm; height:18mm;">
<svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M60 60 L8 60 L60 8 Z" fill="#b8963e" opacity="0.18"/>
  <path d="M60 60 L38 60 L60 38 Z" fill="#b8963e"/>
  <circle cx="60" cy="60" r="3" fill="#b8963e"/>
  <circle cx="10" cy="60" r="1.8" fill="#b8963e" opacity="0.35"/>
  <circle cx="60" cy="10" r="1.8" fill="#b8963e" opacity="0.35"/>
</svg>
</div>

{{-- ── WATERMARK — image only (no text), absolute works for this ─────────────── --}}
@if($logoB64)
<div style="position:absolute; top:72mm; left:84mm; width:129mm; opacity:0.055;">
  <img src="{{ $logoB64 }}" style="width:129mm;" alt="">
</div>
@endif

{{--
  ── MAIN CONTENT TABLE ────────────────────────────────────────────────────────
  mPDF does NOT apply position:absolute to text elements, so we use a table.
  Rows MUST sum to 190mm (= frame height) so the table fills the frame exactly.
    Row 1 — Header  :  38mm   padding-top:12mm  → content starts ~y:18mm
    Row 2 — Body    : 110mm   padding-top:24mm  → content starts ~y:68mm
    Row 3 — Sigs    :  42mm   vertical-align:bottom, padding-bottom:12mm → ~y:184mm
  Use border-top on inner <td> cells for signature lines (more reliable than on divs).
--}}
<table style="width:297mm; margin-top:6mm; border-collapse:collapse;">

  {{-- ── HEADER ── --}}
  <tr>
    <td style="height:38mm; vertical-align:top; text-align:center; padding:12mm 28mm 0;">
      @if($logoB64)
      <img src="{{ $logoB64 }}" style="height:12mm; margin-bottom:2mm;" alt="Blue Arrow">
      @endif
      <div style="font-size:9pt; color:#b8963e; font-family:Arial,sans-serif; letter-spacing:3px; text-transform:uppercase; font-weight:bold;">
        Blue Arrow Management Consultants
      </div>
      <div style="width:66mm; height:1px; background:#b8963e; margin:2.5mm auto; opacity:0.4;"></div>
      <div style="font-size:7.5pt; letter-spacing:4px; text-transform:uppercase; color:#aaa; font-family:Arial;">
        Certificate of Completion
      </div>
    </td>
  </tr>

  {{-- ── BODY ── --}}
  <tr>
    <td style="height:110mm; vertical-align:top; text-align:center; padding:26mm 28mm 0;">
      <div style="font-size:9.5pt; color:#888; font-family:Arial; margin-bottom:3mm;">
        This certifies that
      </div>
      <div style="font-size:22pt; color:#1a1a2e; border-bottom:1.5px solid #b8963e; display:inline-block; padding:0 12mm 2mm; margin-bottom:3.5mm;">
        {{ $training->employee_name }}
      </div>
      <div style="font-size:8.5pt; color:#aaa; font-family:Arial; margin-bottom:5mm;">
        @if($training->employee_role){{ $training->employee_role }} &nbsp;&nbsp;·&nbsp;&nbsp; @endif{{ $training->client->company_name }}
      </div>
      <div style="font-size:9.5pt; color:#888; font-family:Arial; margin-bottom:3mm;">
        has successfully completed the training programme
      </div>
      <div style="font-size:13pt; font-style:italic; color:#b8963e; line-height:1.45; margin-bottom:3.5mm;">
        &ldquo;{{ $training->training_type }}&rdquo;
      </div>
      <div style="font-size:8pt; color:#aaa; font-family:Arial;">
        {{ $training->training_date->format('d F Y') }}
        @if($training->trainer) &nbsp;&bull;&nbsp; Delivered by {{ $training->trainer }} @endif
        @if($training->expiry_date) &nbsp;&bull;&nbsp; Valid until {{ $training->expiry_date->format('d F Y') }} @endif
      </div>
    </td>
  </tr>

  {{-- ── SIGNATURES — border-top on inner <td> is reliable in mPDF ── --}}
  <tr>
    <td style="height:42mm; vertical-align:bottom; padding:0 48mm 12mm;">
      <table style="width:100%; border-collapse:collapse;">
        <tr>
          <td style="width:44%; text-align:center; padding:0 4mm; border-top:1px solid #b8963e; padding-top:5mm;">
            <div style="font-size:9.5pt; font-weight:bold; color:#1a1a2e; font-family:Arial;">{{ $training->signatory_name ?: 'Authorised Signatory' }}</div>
            <div style="font-size:7.5pt; color:#bbb; font-family:Arial; margin-top:1.5mm;">{{ $training->signatory_title ?: 'Blue Arrow Management Consultants' }}</div>
          </td>
          <td style="width:12%;"></td>
          <td style="width:44%; text-align:center; padding:0 4mm; border-top:1px solid #b8963e; padding-top:5mm;">
            <div style="font-size:9.5pt; font-weight:bold; color:#1a1a2e; font-family:Arial;">{{ $training->training_date->format('d F Y') }}</div>
            <div style="font-size:7.5pt; color:#bbb; font-family:Arial; margin-top:1.5mm;">Date of Issue</div>
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>

{{-- ── FOOTER — below the frame (frame bottom = y:196mm, footer at y:200mm+) ── --}}
{{-- Falls to normal flow below the table if absolute doesn't render for text. --}}
<div style="position:absolute; top:201mm; left:12mm; width:273mm; text-align:center; font-size:5.5pt; color:#ccc; font-family:Arial; letter-spacing:0.2px;">
  Blue Arrow Management Consultants is authorised to provide professional and management development training under Licence No. 3003-02 issued by the Sharjah Research Technology and Innovation Park (SRTIP).
  &nbsp;|&nbsp; Certificate No: BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}
</div>

</body>
</html>
