<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Training Certificates</title>
<style>
    body { margin: 0; padding: 0; background: #f4f4f5; font-family: Helvetica, Arial, sans-serif; color: #1f2937; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
    .header { background: #1b2d4f; padding: 28px 36px; }
    .header-logo { color: #ffffff; font-size: 18px; font-weight: bold; letter-spacing: 1px; }
    .header-sub { color: #b8c8e8; font-size: 11px; margin-top: 3px; letter-spacing: 1px; }
    .body { padding: 32px 36px; }
    .greeting { font-size: 15px; color: #374151; margin-bottom: 20px; line-height: 1.6; }
    .session-box { background: #f8f7f4; border: 1px solid #e5c87a; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; }
    .session-label { font-size: 10px; font-weight: bold; color: #b8963e; letter-spacing: 1px; text-transform: uppercase; }
    .session-title { font-size: 15px; font-weight: bold; color: #1b2d4f; margin-top: 3px; }
    .session-date { font-size: 12px; color: #6b7280; margin-top: 2px; }
    .table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
    .table th { font-size: 10px; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; padding: 0 0 8px 0; border-bottom: 1px solid #e5e7eb; text-align: left; }
    .table td { padding: 12px 0; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .emp-name { font-size: 14px; font-weight: 600; color: #1f2937; }
    .emp-role { font-size: 11px; color: #9ca3af; margin-top: 1px; }
    .btn { display: inline-block; background: #1b2d4f; color: #ffffff !important; text-decoration: none; font-size: 12px; font-weight: bold; padding: 8px 16px; border-radius: 6px; letter-spacing: 0.3px; }
    .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 36px; }
    .footer-text { font-size: 11px; color: #9ca3af; line-height: 1.6; }
    .footer-licence { font-size: 10px; color: #d1d5db; margin-top: 8px; line-height: 1.5; }
</style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <div class="header-logo">BLUE ARROW</div>
        <div class="header-sub">MANAGEMENT CONSULTANTS</div>
    </div>

    <div class="body">
        <p class="greeting">
            Dear {{ $companyName }},<br><br>
            Please find below the training certificates for your staff who attended the following programme. Each link allows direct download of the individual certificate.
        </p>

        <div class="session-box">
            <div class="session-label">Training Programme</div>
            <div class="session-title">{{ $sessionTitle }}</div>
            <div class="session-date">{{ $sessionDate }}</div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Certificate No.</th>
                    <th style="text-align:right;">Download</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendees as $attendee)
                <tr>
                    <td>
                        <div class="emp-name">{{ $attendee->employee_name }}</div>
                        @if($attendee->employee_role)
                            <div class="emp-role">{{ $attendee->employee_role }}</div>
                        @endif
                    </td>
                    <td style="font-size:12px; color:#6b7280;">
                        BA-TRN-{{ str_pad($attendee->id, 5, '0', STR_PAD_LEFT) }}
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('certificate.public.show', $attendee->public_token) }}" class="btn">
                            Download →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="font-size:13px; color:#6b7280; line-height:1.6;">
            Each link is private and unique to the individual certificate. If you have any questions, please do not hesitate to contact us.
        </p>
        <p style="font-size:13px; color:#6b7280; margin-top:4px;">
            Warm regards,<br>
            <strong style="color:#1b2d4f;">Blue Arrow Management Consultants</strong>
        </p>
    </div>

    <div class="footer">
        <div class="footer-text">
            This email was sent from the Blue Arrow Management Portal.
        </div>
        <div class="footer-licence">
            Blue Arrow Management Consultants is authorised to provide Professional and Management Development Training under Licence No. 3003-02 issued by Sharjah Research Technology and Innovation Park (SRTIP).
        </div>
    </div>

</div>
</body>
</html>
