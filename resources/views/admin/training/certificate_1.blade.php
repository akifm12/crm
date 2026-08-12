<html>
<head><meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { width: 297mm; height: 210mm; font-family: Georgia, 'Times New Roman', serif; background: #fff; color: #1b2d4f; }
</style>
</head>
<body>

{{-- Outer double-border frame --}}
<div style="position:absolute; top:5mm; left:5mm; width:287mm; height:200mm; border:2px solid #b8963e;"></div>
<div style="position:absolute; top:7.5mm; left:7.5mm; width:282mm; height:195mm; border:0.5px solid #b8963e;"></div>

{{--
  Main table — fills the inner frame.
  Starts at top:8mm, left:8mm, width:281mm.
  Row heights: 18 + 66 + 40 + 18 + 50 = 192mm → ends at y:200mm (inner border bottom).
--}}
<table style="width:281mm; margin-top:8mm; margin-left:8mm; border-collapse:collapse;">

  {{-- ══ ROW 1: HEADER BAR ══ --}}
  <tr>
    <td style="height:18mm; background-color:#1b2d4f; padding:0 8mm; vertical-align:middle;">
      <table style="width:100%; border-collapse:collapse;">
        <tr>
          <td style="vertical-align:middle;">
            @if($logoB64)
            <img src="{{ $logoB64 }}" style="height:9mm; vertical-align:middle; margin-right:3mm;" alt="">
            @endif
            <span style="font-size:15pt; font-weight:bold; color:#ffffff; font-family:Georgia; letter-spacing:0.5px;">BLUE ARROW</span>
            <span style="font-size:8pt; color:#c0bfbd; font-family:Arial; letter-spacing:1.5px; text-transform:uppercase; margin-left:2mm;">Management Consultants</span>
          </td>
          <td style="text-align:right; vertical-align:middle; white-space:nowrap;">
            <span style="font-size:6.5pt; color:#b8963e; font-family:Arial; letter-spacing:2.5px; text-transform:uppercase; font-weight:bold;">Professional Training</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  {{-- ══ ROW 2: CERTIFICATE TITLE + NAME + DIAMOND ══ --}}
  <tr>
    <td style="height:66mm; vertical-align:top; text-align:center; padding:9mm 20mm 6mm; border-bottom:1px solid #b8963e;">

      <div style="font-size:38pt; font-weight:bold; color:#1b2d4f; font-family:Georgia; letter-spacing:1px; line-height:1; margin-bottom:1.5mm;">
        CERTIFICATE
      </div>
      <div style="font-size:8pt; color:#b8963e; font-family:Arial; letter-spacing:2.5px; text-transform:uppercase; font-weight:bold; margin-bottom:4mm;">
        of Professional Training
      </div>
      <div style="font-size:9pt; color:#999; font-family:Georgia; font-style:italic; margin-bottom:3.5mm;">
        This is to certify that
      </div>
      <div style="font-size:21pt; font-weight:bold; color:#1b2d4f; font-family:Georgia; letter-spacing:1px; margin-bottom:5mm;">
        {{ strtoupper($training->employee_name) }}
      </div>

      {{-- Flanking lines + diamond --}}
      <table style="width:52%; margin:0 auto; border-collapse:collapse;">
        <tr>
          <td style="border-bottom:1.5px solid #b8963e; width:44%; padding-bottom:6px;"></td>
          <td style="width:12%; text-align:center; vertical-align:middle; font-size:11pt; color:#b8963e; padding:0 2mm;">&#9670;</td>
          <td style="border-bottom:1.5px solid #b8963e; width:44%; padding-bottom:6px;"></td>
        </tr>
      </table>

    </td>
  </tr>

  {{-- ══ ROW 3: COURSE DETAILS ══ --}}
  <tr>
    <td style="height:40mm; vertical-align:top; text-align:center; padding:9mm 18mm 0; border-bottom:1px solid #b8963e;">
      <div style="font-size:8.5pt; color:#777; font-family:Arial; margin-bottom:2mm;">
        @if($training->employee_role){{ $training->employee_role }} &nbsp;|&nbsp; @endif{{ $training->client->company_name }}
      </div>
      <div style="font-size:9pt; color:#777; font-family:Arial; margin-bottom:3mm;">
        has successfully completed the professional training programme
      </div>
      <div style="font-size:11.5pt; font-weight:bold; color:#1b2d4f; font-family:Georgia; line-height:1.45; margin-bottom:3mm; text-transform:uppercase; letter-spacing:0.3px;">
        {{ $training->training_type }}
      </div>
      <div style="font-size:8pt; color:#aaa; font-family:Arial;">
        Conducted by Blue Arrow Management Consultants
      </div>
    </td>
  </tr>

  {{-- ══ ROW 4: INFO BAR (3 columns) ══ --}}
  <tr>
    <td style="height:18mm; padding:0; border-bottom:1px solid #b8963e;">
      <table style="width:100%; height:18mm; border-collapse:collapse;">
        <tr>
          <td style="width:33%; background-color:#f3ede0; text-align:center; vertical-align:middle; padding:3mm 4mm; border-right:1px solid #b8963e;">
            <div style="font-size:6.5pt; font-weight:bold; color:#b8963e; font-family:Arial; letter-spacing:1.5px; text-transform:uppercase; margin-bottom:1.5mm;">Training Date</div>
            <div style="font-size:11pt; color:#1b2d4f; font-family:Georgia; font-weight:bold;">{{ $training->training_date->format('d F Y') }}</div>
          </td>
          <td style="width:34%; background-color:#f3ede0; text-align:center; vertical-align:middle; padding:3mm 4mm; border-right:1px solid #b8963e;">
            <div style="font-size:6.5pt; font-weight:bold; color:#b8963e; font-family:Arial; letter-spacing:1.5px; text-transform:uppercase; margin-bottom:1.5mm;">Certificate No.</div>
            <div style="font-size:11pt; color:#1b2d4f; font-family:Georgia; font-weight:bold;">BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}</div>
          </td>
          <td style="width:33%; background-color:#f3ede0; text-align:center; vertical-align:middle; padding:3mm 4mm;">
            <div style="font-size:6.5pt; font-weight:bold; color:#b8963e; font-family:Arial; letter-spacing:1.5px; text-transform:uppercase; margin-bottom:1.5mm;">Valid Until</div>
            <div style="font-size:11pt; color:#1b2d4f; font-family:Georgia; font-weight:bold;">
              @if($training->expiry_date){{ $training->expiry_date->format('d F Y') }}@else&mdash;@endif
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  {{-- ══ ROW 5: BOTTOM — issued text, signature, stamp ══ --}}
  <tr>
    <td style="height:50mm; padding:7mm 10mm 6mm; vertical-align:top;">
      <table style="width:100%; border-collapse:collapse;">
        <tr>
          <td style="width:68%; vertical-align:top; padding-right:6mm;">
            <div style="font-size:8.5pt; color:#777; font-family:Arial; text-align:center; margin-bottom:9mm; line-height:1.6;">
              Issued as evidence of attendance and successful completion of the above professional training programme.
            </div>
            {{-- Signature line: border-top on <td> is reliable in mPDF --}}
            <table style="width:100%; border-collapse:collapse;">
              <tr>
                <td style="width:55%; border-top:1.5px solid #1b2d4f; padding-top:3mm; vertical-align:top;">
                  <div style="font-size:10pt; font-weight:bold; color:#1b2d4f; font-family:Georgia;">{{ $training->signatory_name ?: 'Authorised Signatory' }}</div>
                  <div style="font-size:8pt; color:#888; font-family:Arial; margin-top:1mm;">{{ $training->signatory_title ?: 'Director' }}</div>
                </td>
                <td style="width:45%;"></td>
              </tr>
            </table>
          </td>
          <td style="width:32%; vertical-align:middle; text-align:center;">
            {{-- Stamp placeholder — replace with actual stamp image if available --}}
            {{-- <img src="..." style="width:28mm; height:28mm;" alt=""> --}}
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>

{{-- Footer licence text — outside the frame --}}
<div style="position:absolute; top:203mm; left:9mm; width:279mm; text-align:center; font-size:5.5pt; color:#bbb; font-family:Arial; letter-spacing:0.2px;">
  Blue Arrow Management Consultants is authorised to provide Professional and Management Development Training under Licence No. 3003-02 issued by Sharjah Research Technology and Innovation Park (SRTIP).
</div>

</body>
</html>
