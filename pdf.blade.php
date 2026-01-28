<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22px; }
        body { font-family: DejaVu Sans, sans-serif; color:#0B1020; }
        .sheet {
            border: 2px solid #0B1020;
            padding: 18px;
            position: relative;
        }
        .frame {
            border: 3px solid #6F3FFF;
            padding: 16px;
        }
        .top {
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        .brand {
            display:flex; align-items:center; gap:12px;
        }
        .logo {
            width: 150px; height: 42px; object-fit: contain;
        }
        .title {
            text-align:center;
            margin-top: 18px;
        }
        .title h1 {
            margin:0;
            font-size: 40px;
            letter-spacing: 1px;
        }
        .subtitle {
            margin-top: 6px;
            font-size: 14px;
            color:#1F2A44;
        }

        .name {
            text-align:center;
            margin-top: 22px;
            font-size: 30px;
            font-weight: 800;
        }

        .note {
            text-align:center;
            margin: 14px auto 0;
            width: 86%;
            font-size: 15px;
            line-height: 1.6;
            color:#1F2A44;
        }

        .meta {
            margin-top: 18px;
            display:flex;
            justify-content:space-between;
            font-size: 12px;
            color:#1F2A44;
        }

        .badge {
            display:inline-block;
            padding: 6px 10px;
            border: 1px solid #0B1020;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .sigRow {
            margin-top: 26px;
            display:flex;
            justify-content:space-between;
            gap: 18px;
        }
        .sig {
            width: 45%;
            border-top: 1px solid #0B1020;
            padding-top: 6px;
            font-size: 12px;
            color:#1F2A44;
        }

        .watermark {
            position:absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            font-size: 90px;
            font-weight: 800;
            letter-spacing: 6px;
            white-space: nowrap;
        }

        .footer {
            margin-top: 14px;
            font-size: 11px;
            color:#1F2A44;
            text-align:center;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="watermark">COGNISENSE</div>

        <div class="frame">
            <div class="top">
                <div class="brand">
                    @if($logoDataUri)
                        <img class="logo" src="{{ $logoDataUri }}" alt="Cognisense Logo">
                    @endif
                    <div>
                        <div class="badge">Official Certificate</div>
                        <div style="font-size:12px;color:#1F2A44;margin-top:4px;">
                            Certificate No: <b>{{ $cert->certificate_no }}</b>
                        </div>
                    </div>
                </div>

                <div style="text-align:right;font-size:12px;color:#1F2A44;">
                    Issued on<br>
                    <b>{{ $issuedDate }}</b>
                </div>
            </div>

            <div class="title">
                <h1>Certificate of Mastery</h1>
                <div class="subtitle">This certifies successful completion of Cognisense real-world skills training</div>
            </div>

            <div style="text-align:center;margin-top:16px;font-size:14px;color:#1F2A44;">
                Presented to
            </div>

            <div class="name">{{ $cert->full_name }}</div>

            <div class="note">
                This certificate is awarded for completing and demonstrating mastery across all <b>13 Cognisense skill scenarios</b>,
                evidencing professional communication, problem-solving ability, and job-ready performance.
            </div>

            <div class="meta">
                <div>
                    Average Score: <b>{{ $cert->avg_score !== null ? number_format($cert->avg_score,2) : 'N/A' }}/10</b>
                </div>
                <div>
                    Verification: <b>{{ $cert->email }}</b>
                </div>
            </div>

            <div class="sigRow">
                <div class="sig">Authorized Signature</div>
                <div class="sig" style="text-align:right;">Cognisense Program</div>
            </div>

            <div class="footer">
                Cognisense • Adaptive Skills Trainer & Certifier
            </div>
        </div>
    </div>
</body>
</html>
