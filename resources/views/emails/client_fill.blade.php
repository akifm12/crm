<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KYC Form — {{ $tenantName }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; padding: 0; background: #f8fafc; }
        .container { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #1e3a5f; padding: 32px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 600; }
        .header p { color: #93c5fd; margin: 4px 0 0; font-size: 14px; }
        .body { padding: 32px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .btn { display: block; margin: 24px auto; padding: 14px 32px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: 600; text-align: center; }
        .note { background: #f1f5f9; border-radius: 8px; padding: 16px; margin-top: 24px; }
        .note p { color: #64748b; font-size: 13px; margin: 0; }
        .footer { padding: 20px 32px; border-top: 1px solid #e2e8f0; text-align: center; }
        .footer p { color: #94a3b8; font-size: 12px; margin: 0; }
        .url { word-break: break-all; color: #2563eb; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $tenantName }}</h1>
        <p>KYC / AML Compliance Form</p>
    </div>
    <div class="body">
        <p>Dear {{ $clientName }},</p>
        <p>As part of our compliance requirements under UAE AML/CFT regulations, we kindly request you to complete the following KYC (Know Your Customer) form at your earliest convenience.</p>
        <p>Please click the button below to access your secure, personalised form:</p>
        <a href="{{ $link }}" class="btn">Complete KYC Form</a>
        <div class="note">
            <p>⏳ This link expires on <strong>{{ $expiresAt }}</strong>. Please complete the form before the expiry date.</p>
            <p style="margin-top:8px">🔒 This is a one-time secure link. Your information is encrypted and stored securely.</p>
            <p style="margin-top:8px">If the button above doesn't work, copy and paste this URL into your browser:</p>
            <p class="url" style="margin-top:4px">{{ $link }}</p>
        </div>
        <p style="margin-top:24px">If you have any questions, please contact us directly.</p>
        <p>Best regards,<br><strong>{{ $tenantName }}</strong><br>Compliance Department</p>
    </div>
    <div class="footer">
        <p>This email was sent as part of our AML/CFT compliance obligations under UAE Federal Decree-Law No. 20 of 2018.</p>
    </div>
</div>
</body>
</html>
