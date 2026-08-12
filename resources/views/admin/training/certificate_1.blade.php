<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>
    @page {
        margin: 0;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Georgia, 'Times New Roman', serif;
        color: #1b2d4f;
        background: #ffffff;
    }

    .certificate {
        position: absolute;
        top: 5mm;
        left: 5mm;
        width: 287mm;
        height: 200mm;
        border: 1.5mm solid #b8963e;
    }

    .inner {
        position: absolute;
        top: 2mm;
        left: 2mm;
        width: 281mm;
        height: 194mm;
        border: 0.3mm solid #b8963e;
    }

    .header {
        position: absolute;
        top: 0;
        left: 0;
        width: 281mm;
        height: 18mm;
        background: #1b2d4f;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-table td {
        height: 18mm;
        vertical-align: middle;
    }

    .brand {
        padding-left: 8mm;
    }

    .brand-main {
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 15pt;
        font-weight: bold;
        color: #ffffff;
    }

    .brand-sub {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 7pt;
        color: #d0d0d0;
        letter-spacing: 1px;
    }

    .training-label {
        padding-right: 8mm;
        text-align: right;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 7pt;
        font-weight: bold;
        color: #d0b66a;
    }

    .main {
        position: absolute;
        top: 26mm;
        left: 12mm;
        width: 257mm;
        text-align: center;
    }

    .certificate-title {
        font-size: 34pt;
        font-weight: bold;
        line-height: 1;
        color: #1b2d4f;
    }

    .certificate-subtitle {
        margin-top: 2mm;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        font-weight: bold;
        color: #b8963e;
        letter-spacing: 2px;
    }

    .certify-text {
        margin-top: 7mm;
        font-size: 9pt;
        font-style: italic;
        color: #888888;
    }

    .employee-name {
        margin-top: 3mm;
        font-size: 21pt;
        font-weight: bold;
        color: #1b2d4f;
    }

    .divider {
        margin: 4mm auto 0 auto;
        width: 130mm;
        border-collapse: collapse;
    }

    .divider-line {
        border-bottom: 0.4mm solid #b8963e;
        width: 45%;
    }

    .divider-symbol {
        width: 10%;
        text-align: center;
        color: #b8963e;
        font-size: 10pt;
    }

    .employment {
        margin-top: 5mm;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8.5pt;
        color: #777777;
    }

    .completion {
        margin-top: 3mm;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9pt;
        color: #666666;
    }

    .course {
        width: 225mm;
        margin: 4mm auto 0 auto;
        font-size: 12pt;
        line-height: 1.35;
        font-weight: bold;
        color: #1b2d4f;
    }

    .conducted {
        margin-top: 3mm;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        color: #999999;
    }

    .info {
        position: absolute;
        top: 123mm;
        left: 0;
        width: 281mm;
        height: 20mm;
        border-collapse: collapse;
        background: #f3ede0;
        border-top: 0.3mm solid #b8963e;
        border-bottom: 0.3mm solid #b8963e;
    }

    .info td {
        width: 33.33%;
        height: 20mm;
        text-align: center;
        vertical-align: middle;
    }

    .info-middle {
        border-left: 0.3mm solid #b8963e;
        border-right: 0.3mm solid #b8963e;
    }

    .info-label {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 6.5pt;
        font-weight: bold;
        color: #b8963e;
        letter-spacing: 1px;
    }

    .info-value {
        margin-top: 1.5mm;
        font-size: 10pt;
        font-weight: bold;
        color: #1b2d4f;
    }

    .bottom {
        position: absolute;
        top: 149mm;
        left: 12mm;
        width: 257mm;
        text-align: center;
    }

    .evidence {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        color: #777777;
    }

    .signature-table {
        width: 230mm;
        margin: 10mm auto 0 auto;
        border-collapse: collapse;
    }

    .signature-table td {
        width: 50%;
        text-align: center;
        vertical-align: bottom;
    }

    .signature-line {
        width: 75mm;
        margin: 0 auto;
        border-top: 0.4mm solid #1b2d4f;
        padding-top: 2.5mm;
    }

    .signature-name {
        font-size: 10pt;
        font-weight: bold;
    }

    .signature-title {
        margin-top: 1mm;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 7.5pt;
        color: #888888;
    }

    .licence {
        position: absolute;
        bottom: 3mm;
        left: 10mm;
        width: 261mm;
        text-align: center;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 5.5pt;
        line-height: 1.3;
        color: #999999;
    }
</style>
</head>

<body>

<div class="certificate">
    <div class="inner">

        <!-- HEADER -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="brand">

                        @if($logoB64)
                            <img
                                src="{{ $logoB64 }}"
                                style="height:9mm; vertical-align:middle; margin-right:3mm;"
                            >
                        @endif

                        <span class="brand-main">
                            BLUE ARROW
                        </span>

                        &nbsp;&nbsp;

                        <span class="brand-sub">
                            MANAGEMENT CONSULTANTS
                        </span>

                    </td>

                    <td class="training-label">
                        PROFESSIONAL TRAINING
                    </td>
                </tr>
            </table>
        </div>


        <!-- MAIN CERTIFICATE AREA -->
        <div class="main">

            <div class="certificate-title">
                CERTIFICATE
            </div>

            <div class="certificate-subtitle">
                OF PROFESSIONAL TRAINING
            </div>

            <div class="certify-text">
                This is to certify that
            </div>

            <div class="employee-name">
                {{ strtoupper($training->employee_name) }}
            </div>


            <!-- GOLD DIVIDER -->
            <table class="divider">
                <tr>
                    <td class="divider-line"></td>

                    <td class="divider-symbol">
                        &#9670;
                    </td>

                    <td class="divider-line"></td>
                </tr>
            </table>


            <!-- EMPLOYEE DETAILS -->
            <div class="employment">

                @if($training->employee_role)
                    {{ $training->employee_role }}
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                @endif

                {{ $training->client->company_name }}

            </div>


            <div class="completion">
                has successfully completed the professional training programme
            </div>


            <div class="course">
                {{ strtoupper($training->training_type) }}
            </div>


            <div class="conducted">
                Conducted by Blue Arrow Management Consultants
            </div>

        </div>


        <!-- INFORMATION BAR -->
        <table class="info">
            <tr>

                <td>
                    <div class="info-label">
                        TRAINING DATE
                    </div>

                    <div class="info-value">
                        {{ $training->training_date->format('d F Y') }}
                    </div>
                </td>


                <td class="info-middle">

                    <div class="info-label">
                        CERTIFICATE NO.
                    </div>

                    <div class="info-value">
                        BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}
                    </div>

                </td>


                <td>

                    <div class="info-label">
                        VALID UNTIL
                    </div>

                    <div class="info-value">

                        @if($training->expiry_date)

                            {{ $training->expiry_date->format('d F Y') }}

                        @else

                            &mdash;

                        @endif

                    </div>

                </td>

            </tr>
        </table>


        <!-- LOWER SECTION -->
        <div class="bottom">

            <div class="evidence">
                Issued as evidence of attendance and successful completion
                of the above professional training programme.
            </div>


            <table class="signature-table">
                <tr>

                    <td>

                        <div class="signature-line">

                            <div class="signature-name">
                                {{ $training->signatory_name ?: 'Authorised Signatory' }}
                            </div>

                            <div class="signature-title">
                                {{ $training->signatory_title ?: 'Director' }}
                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="signature-line">

                            <div class="signature-name">
                                {{ $training->training_date->format('d F Y') }}
                            </div>

                            <div class="signature-title">
                                Date of Issue
                            </div>

                        </div>

                    </td>

                </tr>
            </table>

        </div>


        <!-- LICENCE / AUTHORISATION -->
        <div class="licence">

            Blue Arrow Management Consultants is authorised to provide
            Professional and Management Development Training under
            Licence No. 3003-02 issued by Sharjah Research Technology
            and Innovation Park (SRTIP).

        </div>

    </div>
</div>

</body>
</html>
