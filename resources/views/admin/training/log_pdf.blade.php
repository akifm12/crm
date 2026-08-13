<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 10pt;
        color: #1a1a2e;
        background: #fff;
    }

    .page {
        width: 210mm;
        min-height: 297mm;
        padding: 12mm 14mm 14mm;
    }

    /* Letterhead */
    .letterhead {
        width: 100%;
        margin-bottom: 8mm;
        text-align: center;
    }
    .letterhead img {
        max-height: 25mm;
        max-width: 100%;
        object-fit: contain;
    }
    .letterhead-fallback {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid #1a1a2e;
        padding-bottom: 4mm;
    }
    .letterhead-fallback .company-name {
        font-size: 18pt;
        font-weight: bold;
        color: #1a1a2e;
        letter-spacing: 0.5px;
    }
    .letterhead-fallback .company-sub {
        font-size: 8pt;
        color: #555;
        margin-top: 2px;
    }

    /* Divider under letterhead */
    .divider {
        border: none;
        border-top: 1.5px solid #b8963e;
        margin-bottom: 6mm;
    }

    /* Session info box */
    .session-box {
        background: #f8f9fc;
        border: 1px solid #dde2ef;
        border-radius: 4px;
        padding: 5mm 6mm;
        margin-bottom: 7mm;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .session-box .label {
        font-size: 7.5pt;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 1.5px;
    }
    .session-box .value {
        font-size: 11pt;
        font-weight: bold;
        color: #1a1a2e;
    }
    .session-box .meta {
        font-size: 8.5pt;
        color: #444;
    }

    /* Doc title */
    .doc-title {
        font-size: 9.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #b8963e;
        margin-bottom: 4mm;
        text-align: center;
    }

    /* Attendee table */
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }
    thead tr {
        background: #1a1a2e;
        color: #fff;
    }
    thead th {
        padding: 3mm 4mm;
        text-align: left;
        font-size: 8pt;
        font-weight: 600;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    thead th.center { text-align: center; }

    tbody tr:nth-child(even) { background: #f5f6fa; }
    tbody tr { border-bottom: 0.5px solid #dde2ef; }

    tbody td {
        padding: 2.5mm 4mm;
        color: #222;
        vertical-align: middle;
    }
    tbody td.center { text-align: center; }

    /* Signature cell — blank for physical signature */
    .sig-cell {
        width: 36mm;
        height: 12mm;
        border-bottom: 1px solid #999;
        display: block;
        margin: 0 auto;
    }

    /* Footer */
    .footer {
        margin-top: 10mm;
        border-top: 1px solid #dde2ef;
        padding-top: 4mm;
        display: flex;
        justify-content: space-between;
        font-size: 7.5pt;
        color: #aaa;
    }
</style>
</head>
<body>
<div class="page">

    {{-- Letterhead --}}
    <div class="letterhead">
        @if($letterheadB64)
            <img src="{{ $letterheadB64 }}" alt="Letterhead">
        @else
            <div class="letterhead-fallback">
                <div>
                    <div class="company-name">Blue Arrow</div>
                    <div class="company-sub">Compliance &amp; Consulting</div>
                </div>
                <div style="text-align:right; font-size:8pt; color:#555;">
                    contact@bluearrow.ae &nbsp;|&nbsp; www.bluearrow.ae
                </div>
            </div>
        @endif
    </div>

    <hr class="divider">

    <div class="doc-title">Training Attendance Log</div>

    {{-- Session summary --}}
    <div class="session-box">
        <div>
            <div class="label">Programme / Training</div>
            <div class="value">{{ $type }}</div>
        </div>
        <div style="text-align:center;">
            <div class="label">Date</div>
            <div class="meta" style="font-size:10pt; font-weight:600;">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</div>
        </div>
        <div style="text-align:right;">
            <div class="label">Total Attendees</div>
            <div class="value">{{ $attendees->count() }}</div>
        </div>
    </div>

    {{-- Attendee table --}}
    <table>
        <thead>
            <tr>
                <th style="width:6%;">#</th>
                <th style="width:22%;">Employee Name</th>
                <th style="width:14%;">ID Number</th>
                <th style="width:18%;">Role / Position</th>
                <th style="width:22%;">Company</th>
                <th class="center" style="width:18%;">Signature</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendees as $i => $tr)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $tr->employee_name }}</td>
                <td>{{ $tr->employee_id_number ?: '—' }}</td>
                <td>{{ $tr->employee_role ?: '—' }}</td>
                <td>{{ $tr->client->company_name ?? '—' }}</td>
                <td class="center"><span class="sig-cell"></span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <span>Blue Arrow Compliance &amp; Consulting &nbsp;|&nbsp; contact@bluearrow.ae</span>
        <span>Generated: {{ now()->format('d M Y') }}</span>
    </div>

</div>
</body>
</html>
