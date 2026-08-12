<html>
<head><meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { width: 297mm; height: 210mm; font-family: Arial, Helvetica, sans-serif; }
</style>
</head>
<body>

<table style="width: 297mm; height: 210mm; border-collapse: collapse; background-color: #0f172a;">

  {{-- Gold top bar --}}
  <tr>
    <td colspan="1" style="height: 3mm; background: linear-gradient(to right, #f59e0b, #d97706, #f59e0b);"></td>
  </tr>

  {{-- HEADER --}}
  <tr>
    <td style="height: 38mm; vertical-align: top; padding: 9mm 20mm 4mm;">
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td style="vertical-align: middle; width: 55mm;">
            @if($logoB64)
            <img src="{{ $logoB64 }}" style="max-height: 13mm; max-width: 44mm;" alt="Blue Arrow">
            @else
            <div style="color: #fff; font-size: 13pt; font-weight: 900;">Blue Arrow</div>
            @endif
          </td>
          <td style="text-align: right; vertical-align: middle;">
            <div style="display: inline-block; border: 1px solid #f59e0b; color: #f59e0b; font-size: 6.5pt; letter-spacing: 2px; text-transform: uppercase; padding: 2px 8px;">Certificate of Achievement</div>
            <div style="font-size: 6.5pt; color: rgba(255,255,255,0.3); margin-top: 2mm; letter-spacing: 1px;">No. BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}</div>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  {{-- GOLD DIVIDER --}}
  <tr>
    <td style="height: 1px; padding: 0 20mm;">
      <div style="height: 1px; background: #f59e0b; opacity: 0.4;"></div>
    </td>
  </tr>

  {{-- MAIN BODY --}}
  <tr>
    <td style="vertical-align: middle; text-align: center; padding: 0 24mm;">
      <div style="font-size: 8pt; color: rgba(255,255,255,0.4); letter-spacing: 3px; text-transform: uppercase; margin-bottom: 4mm;">This is to certify that</div>
      <div style="font-size: 28pt; font-weight: 900; color: #ffffff; letter-spacing: -0.5px; line-height: 1.05; margin-bottom: 2mm;">{{ $training->employee_name }}</div>
      <div style="font-size: 9pt; color: #f59e0b; margin-bottom: 5mm; letter-spacing: 0.5px;">
        @if($training->employee_role){{ $training->employee_role }} &nbsp;&bull;&nbsp; @endif{{ $training->client->company_name }}
      </div>
      <div style="font-size: 8pt; color: rgba(255,255,255,0.5); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 2mm;">has successfully completed</div>
      <div style="font-size: 15pt; font-weight: bold; color: #f59e0b; margin-bottom: 4mm;">{{ $training->training_type }}</div>
      <div>
        <span style="display: inline-block; background-color: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; padding: 1.5px 8px; margin: 0 2px; font-size: 7.5pt; color: rgba(255,255,255,0.5);">{{ $training->training_date->format('d F Y') }}</span>
        @if($training->trainer)<span style="display: inline-block; background-color: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; padding: 1.5px 8px; margin: 0 2px; font-size: 7.5pt; color: rgba(255,255,255,0.5);">{{ $training->trainer }}</span>@endif
        @if($training->expiry_date)<span style="display: inline-block; background-color: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; padding: 1.5px 8px; margin: 0 2px; font-size: 7.5pt; color: rgba(255,255,255,0.5);">Valid to {{ $training->expiry_date->format('d M Y') }}</span>@endif
      </div>
    </td>
  </tr>

  {{-- SIGNATURES + FOOTER --}}
  <tr>
    <td style="height: 40mm; vertical-align: bottom; padding: 0 20mm 5mm;">
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 5mm;">
        <tr>
          <td style="width: 38%; text-align: center; padding: 0 6mm;">
            <div style="border-top: 1px solid rgba(245,158,11,0.45); padding-top: 2mm; margin-top: 10mm;">
              <div style="font-size: 8.5pt; font-weight: bold; color: #fff;">{{ $training->signatory_name ?: '___________________' }}</div>
              <div style="font-size: 7pt; color: rgba(255,255,255,0.4); margin-top: 1mm;">{{ $training->signatory_title ?: 'Authorised Signatory' }}</div>
            </div>
          </td>
          <td style="width: 24%;"></td>
          <td style="width: 38%; text-align: center; padding: 0 6mm;">
            <div style="border-top: 1px solid rgba(245,158,11,0.45); padding-top: 2mm; margin-top: 10mm;">
              <div style="font-size: 8.5pt; font-weight: bold; color: #fff;">{{ $training->training_date->format('d F Y') }}</div>
              <div style="font-size: 7pt; color: rgba(255,255,255,0.4); margin-top: 1mm;">Date of Issue</div>
            </div>
          </td>
        </tr>
      </table>
      <div style="text-align: center; font-size: 5.5pt; color: rgba(255,255,255,0.2); letter-spacing: 0.2px; border-top: 0.5px solid rgba(255,255,255,0.08); padding-top: 3mm;">
        Blue Arrow Management Consultants is authorised to provide professional and management development training under Licence No. 3003-02 issued by the Sharjah Research Technology and Innovation Park (SRTIP).
      </div>
    </td>
  </tr>

  {{-- Gold bottom bar --}}
  <tr>
    <td style="height: 3mm; background: linear-gradient(to right, #f59e0b, #d97706, #f59e0b);"></td>
  </tr>

</table>

</body>
</html>
