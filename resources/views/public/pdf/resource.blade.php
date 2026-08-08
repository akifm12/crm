<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .brand { color: #1a56db; font-size: 20px; font-weight: bold; }
        .subtitle { color: #777; font-size: 10px; margin-top: 2px; }
        .title { font-size: 17px; font-weight: bold; color: #111; margin-top: 22px; }
        .lead { color: #555; font-size: 10px; margin-top: 6px; line-height: 1.5; }
        h2 { font-size: 12px; color: #1e3a8a; margin-top: 20px; margin-bottom: 6px; border-bottom: 1px solid #dbeafe; padding-bottom: 4px; }
        ul { margin: 0; padding-left: 16px; }
        li { font-size: 10px; margin-bottom: 5px; line-height: 1.5; }
        .footer { margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; font-size: 8px; color: #999; line-height: 1.5; }
    </style>
</head>
<body>

    <div class="brand">Blue Arrow</div>
    <div class="subtitle">Management Consultants — AML &amp; Regulatory Compliance</div>

    <div class="title">{{ $title }}</div>
    <div class="lead">{{ $lead }}</div>

    @foreach ($sections as $section)
        <h2>{{ $section['heading'] }}</h2>
        <ul>
            @foreach ($section['items'] as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @endforeach

    <div class="footer">
        This document is provided as general guidance by Blue Arrow Management Consultants and does not constitute
        legal advice. Requirements vary by license type and business activity — always confirm specifics with your
        MLRO or the Blue Arrow compliance team. &copy; {{ date('Y') }} Blue Arrow Management Consultants ·
        bluearrow.ae · info@bluearrow.ae
    </div>

</body>
</html>
