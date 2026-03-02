<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cognisense Certificate of Mastery</title>
    <style>
        @page { margin: 0; size: A4 landscape; }
        body { 
            font-family: "Georgia", "Times New Roman", serif; 
            margin: 0; 
            padding: 0; 
            background-color: #f7f9fc;
            color: #1a202c;
            width: 100%;
            height: 100%;
        }

        /* Outer container providing the full bleed background */
        .wrapper {
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            padding: 45px;
            background: #ffffff;
        }

        /* Beautiful layered border system */
        .border-outer {
            position: relative;
            height: 100%;
            box-sizing: border-box;
            border: 2px solid #0f172a;
            padding: 8px;
            background: #fff;
        }

        .border-inner {
            height: 100%;
            box-sizing: border-box;
            border: 12px solid #f8fafc;
            outline: 1px solid #cbd5e1;
            position: relative;
            background: radial-gradient(circle at center, #ffffff 40%, #fefce8 120%);
            padding: 40px 60px;
            text-align: center;
            box-shadow: inset 0 0 40px rgba(0,0,0,0.02);
            overflow: hidden;
        }

        /* Corner ornaments */
        .corner {
            position: absolute;
            width: 40px;
            height: 40px;
            border: 2px solid #0f172a;
        }
        .corner-tl { top: -2px; left: -2px; border-right: none; border-bottom: none; }
        .corner-tr { top: -2px; right: -2px; border-left: none; border-bottom: none; }
        .corner-bl { bottom: -2px; left: -2px; border-right: none; border-top: none; }
        .corner-br { bottom: -2px; right: -2px; border-left: none; border-top: none; }

        /* Very faint background watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.025;
            font-size: 140px;
            font-weight: 900;
            letter-spacing: 15px;
            font-family: "Arial", sans-serif;
            white-space: nowrap;
            color: #000;
            z-index: 0;
        }

        /* Actual Content Wrapper (sits above background graphics) */
        .content {
            position: relative;
            z-index: 10;
            height: 100%;
        }

        /* Top Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .header-left {
            display: table-cell;
            text-align: left;
            vertical-align: middle;
            width: 33%;
        }
        
        .header-center {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            width: 34%;
        }
        
        .header-right {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            width: 33%;
        }

        .logo {
            height: 60px;
            object-fit: contain;
        }

        .cert-id {
            font-family: "Arial", sans-serif;
            font-size: 11px;
            color: #64748b;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Main Titles */
        .pre-title {
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 5px;
            color: #475569;
            text-transform: uppercase;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .main-title {
            font-family: "Georgia", serif;
            font-size: 52px;
            font-weight: 400;
            color: #0f172a;
            margin: 0;
            letter-spacing: 2px;
        }

        .subtitle {
            font-size: 16px;
            color: #3b82f6; /* Elegant subtle blue */
            font-style: italic;
            margin-top: 10px;
            margin-bottom: 35px;
        }

        .presented-to {
            font-size: 14px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 15px;
        }

        .recipient-name {
            font-size: 48px;
            font-weight: 700;
            color: #1e293b;
            font-style: italic;
            border-bottom: 2px solid #cbd5e1;
            display: inline-block;
            padding: 0 40px 5px;
            margin-bottom: 30px;
            min-width: 400px;
        }

        .description {
            font-size: 16px;
            line-height: 1.8;
            color: #334155;
            max-width: 700px;
            margin: 0 auto 40px;
        }

        /* Bottom Details & Signatures */
        .bottom-section {
            display: table;
            width: 100%;
            margin-top: auto;
            position: absolute;
            bottom: 0px;
            left: 0;
        }

        .signature-block {
            display: table-cell;
            width: 30%;
            text-align: center;
            vertical-align: bottom;
        }

        .seal-block {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #1e293b;
            width: 80%;
            margin: 0 auto 8px;
        }

        .signature-label {
            font-size: 13px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .signature-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Custom Seal Graphic built with CSS */
        .seal {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            border-radius: 50%;
            background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
            border: 4px double #fef08a;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            display: table;
            color: #fff;
        }
        
        .seal-inner {
            display: table-cell;
            vertical-align: middle;
            font-family: "Arial", sans-serif;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.2;
        }

        .seal-star {
            font-size: 16px;
            display: block;
            margin-bottom: 3px;
            margin-top: 3px;
        }

        /* Metrics Table */
        .metrics-table {
            margin: 0 auto 30px;
            border-collapse: collapse;
            font-family: "Arial", sans-serif;
        }
        
        .metrics-table td {
            padding: 8px 16px;
            font-size: 13px;
            color: #334155;
        }
        
        .metrics-label {
            text-align: right;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-right: 2px solid #e2e8f0;
        }
        
        .metrics-value {
            text-align: left;
            color: #0f172a;
            font-weight: bold;
            font-size: 14px;
        }

    </style>
</head>
<body>
    <div class="wrapper">
        <div class="border-outer">
            <div class="border-inner">
                <!-- Decorative Corners -->
                <div class="corner corner-tl"></div>
                <div class="corner corner-tr"></div>
                <div class="corner corner-bl"></div>
                <div class="corner corner-br"></div>

                <!-- Watermark -->
                <div class="watermark">COGNISENSE</div>

                <div class="content">
                    
                    <!-- Header Row: Date | Logo | Cert Number -->
                    <div class="header">
                        <div class="header-left">
                            <div class="cert-id" style="font-weight:bold; color:#1e293b;">
                                Date Issued:
                            </div>
                            <div class="cert-id" style="margin-top:3px;">
                                {{ $issuedDate }}
                            </div>
                        </div>
                        
                        <div class="header-center">
                            @if($logoDataUri)
                                <img class="logo" src="{{ $logoDataUri }}" alt="Cognisense Logo">
                            @endif
                        </div>
                        
                        <div class="header-right">
                            <div class="cert-id" style="font-weight:bold; color:#1e293b;">
                                Certificate No.
                            </div>
                            <div class="cert-id" style="margin-top:3px;">
                                {{ $cert->certificate_no }}
                            </div>
                        </div>
                    </div>

                    <!-- Titles -->
                    <div class="pre-title">Official Recognition</div>
                    <h1 class="main-title">Certificate of Mastery</h1>
                    <div class="subtitle">Validating exceptional proficiency in real-world professional scenarios</div>

                    <div class="presented-to">Is hereby proudly presented to</div>
                    <div class="recipient-name">{{ $cert->full_name }}</div>

                    <!-- Description -->
                    <div class="description">
                        This document certifies that the individual named above has successfully completed comprehensive, AI-evaluated training across <b>13 diverse professional paradigms</b> on the Cognisense platform, establishing a verifiable record of outstanding communication, problem-solving, and operational capability.
                    </div>

                    <!-- Small Metrics Table -->
                    <table class="metrics-table">
                        <tr>
                            <td class="metrics-label">Verification Anchor</td>
                            <td class="metrics-value">{{ $cert->email }}</td>
                            <td class="metrics-label" style="padding-left:30px;">Overall Standing</td>
                            <td class="metrics-value">
                                @if($cert->avg_score !== null)
                                    Graduated with distinction ({{ number_format($cert->avg_score, 2) }}/10)
                                @else
                                    Completed
                                @endif
                            </td>
                        </tr>
                    </table>

                    <!-- Footer / Signatures -->
                    <div class="bottom-section">
                        <!-- Left Signature -->
                        <div class="signature-block">
                            <div class="signature-line"></div>
                            <div class="signature-label">Director of Evaluation</div>
                            <div class="signature-sub">Cognisense Assessment Board</div>
                        </div>

                        <!-- Center Gold Seal -->
                        <div class="seal-block">
                            <div class="seal">
                                <div class="seal-inner">
                                    <span class="seal-star">★</span><br>
                                    Certified<br>Excellence<br>
                                    <span class="seal-star">★</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Signature -->
                        <div class="signature-block">
                            <div class="signature-line"></div>
                            <div class="signature-label">Cognisense Platform</div>
                            <div class="signature-sub">Adaptive Skills Trainer</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>
</html>
