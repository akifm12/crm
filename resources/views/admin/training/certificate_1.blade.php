<html>
<head><meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { width: 297mm; height: 210mm; font-family: Georgia, 'Times New Roman', serif; background: #fff; color: #1a1a2e; }
</style>
</head>
<body>

{{--
  Outer border — explicit width/height so mPDF doesn't need right/bottom
  top:6mm left:6mm → width:285mm height:190mm  (leaves 14mm at bottom for footer)
--}}
<div style="position: absolute; top: 6mm; left: 6mm; width: 285mm; height: 190mm; border: 2.5px solid #b8963e;"></div>

{{--
  Corner ornaments — each corner has its OWN SVG (mPDF ignores CSS transform).
  Right-angle of each triangle sits exactly at the outer border corner.
  Div size: 18mm × 18mm
  TL border corner: (6mm, 6mm)   → div top:2mm  left:2mm   (SVG (0,0) is at ~2mm from corner)
  TR border corner: (291mm, 6mm) → div top:2mm  left:273mm
  BL border corner: (6mm, 196mm) → div top:178mm left:2mm
  BR border corner: (291mm,196mm)→ div top:178mm left:273mm
--}}

{{-- Top-left: right-angle at SVG top-left (0,0), triangle points inward (bottom-right) --}}
<div style="position: absolute; top: 2mm; left: 2mm; width: 18mm; height: 18mm;">
<svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M0 0 L52 0 L0 52 Z" fill="#b8963e" opacity="0.18"/>
  <path d="M0 0 L22 0 L0 22 Z" fill="#b8963e"/>
  <circle cx="0" cy="0" r="3" fill="#b8963e"/>
  <circle cx="52" cy="0" r="1.8" fill="#b8963e" opacity="0.4"/>
  <circle cx="0" cy="52" r="1.8" fill="#b8963e" opacity="0.4"/>
</svg>
</div>

{{-- Top-right: right-angle at SVG top-right (60,0), triangle points inward (bottom-left) --}}
<div style="position: absolute; top: 2mm; left: 273mm; width: 18mm; height: 18mm;">
<svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M60 0 L8 0 L60 52 Z" fill="#b8963e" opacity="0.18"/>
  <path d="M60 0 L38 0 L60 22 Z" fill="#b8963e"/>
  <circle cx="60" cy="0" r="3" fill="#b8963e"/>
  <circle cx="8" cy="0" r="1.8" fill="#b8963e" opacity="0.4"/>
  <circle cx="60" cy="52" r="1.8" fill="#b8963e" opacity="0.4"/>
</svg>
</div>

{{-- Bottom-left: right-angle at SVG bottom-left (0,60), triangle points inward (top-right) --}}
<div style="position: absolute; top: 178mm; left: 2mm; width: 18mm; height: 18mm;">
<svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M0 60 L52 60 L0 8 Z" fill="#b8963e" opacity="0.18"/>
  <path d="M0 60 L22 60 L0 38 Z" fill="#b8963e"/>
  <circle cx="0" cy="60" r="3" fill="#b8963e"/>
  <circle cx="52" cy="60" r="1.8" fill="#b8963e" opacity="0.4"/>
  <circle cx="0" cy="8" r="1.8" fill="#b8963e" opacity="0.4"/>
</svg>
</div>

{{-- Bottom-right: right-angle at SVG bottom-right (60,60), triangle points inward (top-left) --}}
<div style="position: absolute; top: 178mm; left: 273mm; width: 18mm; height: 18mm;">
<svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
  <path d="M60 60 L8 60 L60 8 Z" fill="#b8963e" opacity="0.18"/>
  <path d="M60 60 L38 60 L60 38 Z" fill="#b8963e"/>
  <circle cx="60" cy="60" r="3" fill="#b8963e"/>
  <circle cx="8" cy="60" r="1.8" fill="#b8963e" opacity="0.4"/>
  <circle cx="60" cy="8" r="1.8" fill="#b8963e" opacity="0.4"/>
</svg>
</div>

{{-- Watermark logo — large, centred, very faint --}}
@if($logoB64)
<div style="position: absolute; top: 68mm; left: 79mm; width: 139mm; opacity: 0.06;">
  <img src="{{ $logoB64 }}" style="width: 139mm;" alt="">
</div>
@endif

{{--
  Main content table.
  Starts at margin-top:6mm, height:190mm → spans y:6mm–196mm (inside the border).
  Row heights are EXPLICIT so mPDF fills space correctly:
    header row : 50mm
    body row   : 98mm  (content vertically centred)
    sig row    : 42mm  (content vertically bottomed)
    Total      : 190mm ✓
--}}
<table style="width: 297mm; height: 190mm; margin-top: 6mm; border-collapse: collapse;">

  {{-- ── HEADER ─────────────────────────────────────────────────────────── --}}
  <tr>
    <td style="height: 50mm; vertical-align: top; text-align: center; padding: 14mm 30mm 4mm;">
      @if($logoB64)
      <img src="{{ $logoB64 }}" style="height: 13mm; margin-bottom: 3mm;" alt="Blue Arrow">
      @endif
      <div style="font-size: 9pt; color: #b8963e; font-family: Arial, sans-serif; letter-spacing: 3px; text-transform: uppercase; font-weight: bold;">Blue Arrow Management Consultants</div>
      <div style="width: 70mm; height: 1px; background: #b8963e; margin: 3mm auto; opacity: 0.45;"></div>
      <div style="font-size: 8pt; letter-spacing: 4px; text-transform: uppercase; color: #888; font-family: Arial;">Certificate of Completion</div>
    </td>
  </tr>

  {{-- ── BODY ───────────────────────────────────────────────────────────── --}}
  <tr>
    <td style="height: 98mm; vertical-align: middle; text-align: center; padding: 0 30mm;">
      <div style="font-size: 9.5pt; color: #666; font-family: Arial; margin-bottom: 3mm;">This certifies that</div>
      <div style="font-size: 23pt; color: #1a1a2e; border-bottom: 1.5px solid #b8963e; display: inline-block; padding: 0 14mm 2mm; margin-bottom: 4mm;">{{ $training->employee_name }}</div>
      <div style="font-size: 8.5pt; color: #999; font-family: Arial; margin-bottom: 5mm;">
        @if($training->employee_role){{ $training->employee_role }} &nbsp;&nbsp;·&nbsp;&nbsp; @endif{{ $training->client->company_name }}
      </div>
      <div style="font-size: 9.5pt; color: #666; font-family: Arial; margin-bottom: 3mm;">has successfully completed the training programme</div>
      <div style="font-size: 14pt; font-style: italic; color: #b8963e; margin-bottom: 3mm; line-height: 1.4;">&ldquo;{{ $training->training_type }}&rdquo;</div>
      <div style="font-size: 8.5pt; color: #888; font-family: Arial;">
        {{ $training->training_date->format('d F Y') }}
        @if($training->trainer) &nbsp;&bull;&nbsp; Delivered by {{ $training->trainer }} @endif
        @if($training->expiry_date) &nbsp;&bull;&nbsp; Valid until {{ $training->expiry_date->format('d F Y') }} @endif
      </div>
    </td>
  </tr>

  {{-- ── SIGNATURES ─────────────────────────────────────────────────────── --}}
  <tr>
    <td style="height: 42mm; vertical-align: bottom; padding: 0 50mm 10mm;">
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td style="width: 44%; text-align: center; padding: 0 4mm;">
            <div style="border-top: 1px solid #b8963e; padding-top: 3mm;">
              <div style="font-size: 9.5pt; font-weight: bold; color: #1a1a2e; font-family: Arial;">{{ $training->signatory_name ?: 'Authorised Signatory' }}</div>
              <div style="font-size: 7.5pt; color: #aaa; font-family: Arial; margin-top: 1mm;">{{ $training->signatory_title ?: 'Blue Arrow Management Consultants' }}</div>
            </div>
          </td>
          <td style="width: 12%;"></td>
          <td style="width: 44%; text-align: center; padding: 0 4mm;">
            <div style="border-top: 1px solid #b8963e; padding-top: 3mm;">
              <div style="font-size: 9.5pt; font-weight: bold; color: #1a1a2e; font-family: Arial;">{{ $training->training_date->format('d F Y') }}</div>
              <div style="font-size: 7.5pt; color: #aaa; font-family: Arial; margin-top: 1mm;">Date of Issue</div>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>

{{-- Footer — sits BELOW the outer border (border bottom at y:196mm) --}}
<div style="position: absolute; top: 200mm; left: 12mm; width: 273mm; text-align: center; font-size: 5.5pt; color: #ccc; font-family: Arial; letter-spacing: 0.2px;">
  Blue Arrow Management Consultants is authorised to provide professional and management development training under Licence No. 3003-02 issued by the Sharjah Research Technology and Innovation Park (SRTIP).
  &nbsp; | &nbsp; Certificate No: BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}
</div>

</body>
</html>
