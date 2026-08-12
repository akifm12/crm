<html>
<head><meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { width: 297mm; height: 210mm; font-family: Georgia, 'Times New Roman', serif; background: #fff; color: #1a1a2e; }
.outer-border { position: absolute; top: 7mm; left: 7mm; right: 7mm; bottom: 7mm; border: 3px solid #b8963e; }
.inner-border { position: absolute; top: 10mm; left: 10mm; right: 10mm; bottom: 10mm; border: 1px solid #b8963e; }
.corner { position: absolute; width: 16mm; height: 16mm; }
.corner svg { width: 100%; height: 100%; }
.c-tl { top: 4mm; left: 4mm; }
.c-tr { top: 4mm; right: 4mm; transform: scaleX(-1); }
.c-bl { bottom: 4mm; left: 4mm; transform: scaleY(-1); }
.c-br { bottom: 4mm; right: 4mm; transform: scale(-1); }
</style>
</head>
<body>

<div class="outer-border"></div>
<div class="inner-border"></div>

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

<table style="width: 297mm; height: 210mm; border-collapse: collapse;">

  {{-- HEADER --}}
  <tr>
    <td style="height: 50mm; vertical-align: top; text-align: center; padding: 18mm 28mm 4mm;">
      @if($logoB64)
      <img src="{{ $logoB64 }}" style="height: 13mm; margin-bottom: 2mm;" alt="Blue Arrow">
      @endif
      <div style="font-size: 9.5pt; color: #b8963e; font-family: Arial, sans-serif; letter-spacing: 3px; text-transform: uppercase; font-weight: bold;">Blue Arrow Management Consultants</div>
      <div style="width: 70mm; height: 1px; background: #b8963e; margin: 3mm auto; opacity: 0.5;"></div>
      <div style="font-size: 8pt; letter-spacing: 4px; text-transform: uppercase; color: #888; font-family: Arial;">Certificate of Completion</div>
    </td>
  </tr>

  {{-- MAIN BODY --}}
  <tr>
    <td style="vertical-align: middle; text-align: center; padding: 0 28mm;">
      <div style="font-size: 9.5pt; color: #555; font-family: Arial; line-height: 1.7; margin-bottom: 3mm;">This certifies that</div>
      <div style="font-size: 22pt; color: #1a1a2e; border-bottom: 1.5px solid #b8963e; display: inline-block; padding: 0 14mm 1mm; margin-bottom: 3mm;">{{ $training->employee_name }}</div>
      <div style="font-size: 8.5pt; color: #888; font-family: Arial; margin-bottom: 4mm;">
        @if($training->employee_role){{ $training->employee_role }} &nbsp;·&nbsp; @endif{{ $training->client->company_name }}
      </div>
      <div style="font-size: 9.5pt; color: #555; font-family: Arial; line-height: 1.7; margin-bottom: 2mm;">has successfully completed the training programme</div>
      <div style="font-size: 14pt; font-style: italic; color: #b8963e; margin-bottom: 2mm;">&ldquo;{{ $training->training_type }}&rdquo;</div>
      <div style="font-size: 8.5pt; color: #777; font-family: Arial; letter-spacing: 0.5px;">
        {{ $training->training_date->format('d F Y') }}
        @if($training->trainer) &nbsp;&bull;&nbsp; Delivered by {{ $training->trainer }} @endif
        @if($training->expiry_date) &nbsp;&bull;&nbsp; Valid until {{ $training->expiry_date->format('d F Y') }} @endif
      </div>
    </td>
  </tr>

  {{-- SIGNATURES + FOOTER --}}
  <tr>
    <td style="height: 44mm; vertical-align: bottom; padding: 0 28mm 8mm;">
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 5mm;">
        <tr>
          <td style="width: 38%; text-align: center; padding: 0 4mm;">
            <div style="border-top: 1px solid #b8963e; padding-top: 2mm;">
              <div style="font-size: 9pt; font-weight: bold; color: #1a1a2e; font-family: Arial;">{{ $training->signatory_name ?: 'Authorised Signatory' }}</div>
              <div style="font-size: 7pt; color: #999; font-family: Arial; margin-top: 1mm;">{{ $training->signatory_title ?: 'Blue Arrow Management Consultants' }}</div>
            </div>
          </td>
          <td style="width: 24%;"></td>
          <td style="width: 38%; text-align: center; padding: 0 4mm;">
            <div style="border-top: 1px solid #b8963e; padding-top: 2mm;">
              <div style="font-size: 9pt; font-weight: bold; color: #1a1a2e; font-family: Arial;">{{ $training->training_date->format('d F Y') }}</div>
              <div style="font-size: 7pt; color: #999; font-family: Arial; margin-top: 1mm;">Date of Issue</div>
            </div>
          </td>
        </tr>
      </table>
      <div style="text-align: center; font-size: 5.5pt; color: #bbb; font-family: Arial; letter-spacing: 0.2px; border-top: 0.5px solid #e8e8e8; padding-top: 3mm;">
        Blue Arrow Management Consultants is authorised to provide professional and management development training under Licence No. 3003-02 issued by the Sharjah Research Technology and Innovation Park (SRTIP). &nbsp;|&nbsp; Certificate No: BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}
      </div>
    </td>
  </tr>

</table>
</body>
</html>
