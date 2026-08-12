<html>
<head><meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { width: 297mm; height: 210mm; font-family: Arial, Helvetica, sans-serif; background: #fff; color: #1e293b; }
</style>
</head>
<body>

<table style="width: 297mm; height: 210mm; border-collapse: collapse;">
  <tr>

    {{-- LEFT PANEL --}}
    <td style="width: 75mm; background-color: #1e3a5f; vertical-align: top; padding: 16mm 9mm 0 10mm;">

      <div style="margin-bottom: 5mm;">
        @if($logoB64)
        <img src="{{ $logoB64 }}" style="max-width: 44mm; max-height: 16mm;" alt="Blue Arrow">
        @else
        <div style="color: #fff; font-size: 13pt; font-weight: 900;">Blue Arrow</div>
        @endif
      </div>

      <div style="color: #93c5fd; font-size: 7pt; letter-spacing: 1.5px; text-transform: uppercase; line-height: 1.7; margin-bottom: 8mm;">Blue Arrow Management<br>Consultants<br>Training Division</div>

      <div style="background-color: #162d4a; border: 1px solid rgba(255,255,255,0.15); border-radius: 5px; padding: 5mm 4mm; margin-bottom: 4mm;">
        <div style="color: #93c5fd; font-size: 6pt; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 1mm;">Organisation</div>
        <div style="color: #fff; font-size: 9pt; font-weight: bold; line-height: 1.4;">{{ $training->client->company_name }}</div>
      </div>

      <div style="background-color: #162d4a; border: 1px solid rgba(255,255,255,0.15); border-radius: 5px; padding: 5mm 4mm; margin-bottom: 4mm;">
        <div style="color: #93c5fd; font-size: 6pt; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 1mm;">Date Issued</div>
        <div style="color: #fff; font-size: 9pt; font-weight: bold;">{{ $training->training_date->format('d M Y') }}</div>
      </div>

      @if($training->expiry_date)
      <div style="background-color: #162d4a; border: 1px solid rgba(255,255,255,0.15); border-radius: 5px; padding: 5mm 4mm; margin-bottom: 4mm;">
        <div style="color: #93c5fd; font-size: 6pt; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 1mm;">Valid Until</div>
        <div style="color: #fff; font-size: 9pt; font-weight: bold;">{{ $training->expiry_date->format('d M Y') }}</div>
      </div>
      @endif

      {{-- cert no at very bottom --}}
      <div style="margin-top: 8mm; color: rgba(255,255,255,0.25); font-size: 6pt; letter-spacing: 1px;">BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}</div>
    </td>

    {{-- RIGHT PANEL --}}
    <td style="vertical-align: top; padding: 0;">
      <table style="width: 100%; height: 210mm; border-collapse: collapse;">

        {{-- Top accent strip --}}
        <tr>
          <td style="height: 3mm; background: linear-gradient(to right, #2563eb, #93c5fd);"></td>
        </tr>

        {{-- Header --}}
        <tr>
          <td style="height: 28mm; vertical-align: top; padding: 10mm 14mm 4mm;">
            <div style="font-size: 7pt; letter-spacing: 3px; text-transform: uppercase; color: #2563eb; font-weight: bold; margin-bottom: 2mm;">Certificate of Completion</div>
            <div style="font-size: 20pt; font-weight: 900; color: #1e293b; line-height: 1.1;">Training <span style="color: #2563eb;">Achievement</span></div>
          </td>
        </tr>

        {{-- Main content --}}
        <tr>
          <td style="vertical-align: middle; padding: 0 14mm;">
            <div style="font-size: 7pt; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2mm;">Awarded to</div>
            <div style="font-size: 24pt; font-weight: 800; color: #1e3a5f; line-height: 1.1; margin-bottom: 1mm;">{{ $training->employee_name }}</div>
            @if($training->employee_role)
            <div style="font-size: 9pt; color: #64748b; margin-bottom: 5mm;">{{ $training->employee_role }}</div>
            @else
            <div style="margin-bottom: 5mm;"></div>
            @endif
            <div style="height: 2px; background: #2563eb; margin-bottom: 4mm; opacity: 0.3;"></div>
            <div style="font-size: 9pt; color: #475569; line-height: 1.7;">
              This certificate is awarded in recognition of successfully completing the<br>
              <strong style="color: #1e3a5f;">{{ $training->training_type }}</strong> programme
              @if($training->trainer), delivered by <strong>{{ $training->trainer }}</strong>@endif.
            </div>
          </td>
        </tr>

        {{-- Signatures --}}
        <tr>
          <td style="height: 36mm; vertical-align: bottom; padding: 0 14mm 5mm;">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 4mm;">
              <tr>
                <td style="width: 45%; padding-right: 8mm;">
                  <div style="border-top: 1.5px solid #cbd5e1; padding-top: 2mm; margin-top: 12mm;">
                    <div style="font-size: 9pt; font-weight: bold; color: #1e293b;">{{ $training->signatory_name ?: 'Authorised Signatory' }}</div>
                    <div style="font-size: 7pt; color: #94a3b8; margin-top: 1mm;">{{ $training->signatory_title ?: 'Blue Arrow Management Consultants' }}</div>
                  </div>
                </td>
                <td style="width: 45%; padding-right: 0;">
                  <div style="border-top: 1.5px solid #cbd5e1; padding-top: 2mm; margin-top: 12mm;">
                    <div style="font-size: 9pt; font-weight: bold; color: #1e293b;">{{ $training->training_date->format('d F Y') }}</div>
                    <div style="font-size: 7pt; color: #94a3b8; margin-top: 1mm;">Date of Issue</div>
                  </div>
                </td>
              </tr>
            </table>
            <div style="font-size: 5.5pt; color: #cbd5e1; letter-spacing: 0.2px; border-top: 0.5px solid #f0f0f0; padding-top: 3mm;">
              Blue Arrow Management Consultants is authorised to provide professional and management development training under Licence No. 3003-02 issued by the Sharjah Research Technology and Innovation Park (SRTIP).
            </div>
          </td>
        </tr>

      </table>
    </td>

  </tr>
</table>

</body>
</html>
