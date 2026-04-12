<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Clearance Certificate — {{ $clearance->user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            background: #fff;
        }

        .page {
            padding: 30px 40px;
        }

        /* ── Header ── */
        .header {
            border-bottom: 3px solid #064e3b;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .header-inner {
            display: flex; /* will fallback in dompdf - use table */
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .emblem {
            width: 60px; height: 60px;
            border: 2px solid #064e3b;
            border-radius: 50%;
            display: inline-block;
            text-align: center;
            line-height: 56px;
            font-size: 10pt;
            font-weight: bold;
            color: #064e3b;
        }
        .univ-name {
            font-size: 14pt;
            font-weight: bold;
            color: #064e3b;
            line-height: 1.2;
        }
        .univ-sub {
            font-size: 8pt;
            color: #475569;
            margin-top: 3px;
        }
        .cert-title {
            font-size: 16pt;
            font-weight: bold;
            color: #064e3b;
            text-align: center;
            margin: 14px 0 6px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .cert-subtitle {
            text-align: center;
            font-size: 8pt;
            color: #64748b;
            margin-bottom: 18px;
        }

        /* ── Cert No badge ── */
        .cert-no {
            text-align: right;
            font-size: 8pt;
            color: #64748b;
            margin-bottom: 14px;
        }

        /* ── Student info ── */
        .info-box {
            border: 1px solid #d1fae5;
            border-left: 4px solid #059669;
            background: #f0fdf4;
            padding: 12px 16px;
            margin-bottom: 16px;
            border-radius: 4px;
        }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td {
            padding: 4px 8px;
            font-size: 9.5pt;
        }
        .info-table td:first-child {
            font-weight: bold;
            color: #065f46;
            width: 38%;
        }

        /* ── Department table ── */
        .dept-title {
            font-size: 10pt;
            font-weight: bold;
            color: #064e3b;
            margin: 16px 0 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .dept-table { width: 100%; border-collapse: collapse; }
        .dept-table th {
            background: #064e3b;
            color: #fff;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 10px;
            text-align: left;
        }
        .dept-table td {
            font-size: 9pt;
            padding: 7px 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .dept-table tr:nth-child(even) td { background: #f8fafc; }
        .badge-approved {
            background: #d1fae5;
            color: #065f46;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        /* ── Declaration ── */
        .declaration {
            border: 1px solid #e2e8f0;
            background: #fafafa;
            padding: 10px 14px;
            margin: 16px 0;
            font-size: 9pt;
            color: #374151;
            line-height: 1.6;
            border-radius: 4px;
        }

        /* ── Signatures ── */
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        .sig-table td { width: 33.33%; vertical-align: bottom; padding: 0 8px; text-align: center; }
        .sig-line { border-top: 1px solid #1a1a1a; margin-top: 36px; padding-top: 5px; font-size: 8pt; color: #475569; }
        .sig-name { font-size: 9pt; font-weight: bold; color: #1a1a1a; }

        /* ── Footer ── */
        .footer {
            border-top: 2px solid #064e3b;
            padding-top: 8px;
            margin-top: 22px;
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72pt;
            color: rgba(5,150,105,0.04);
            font-weight: bold;
            z-index: -1;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="watermark">CLEARED</div>

    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:70px;">
                    <div class="emblem">MUST</div>
                </td>
                <td style="padding-left:12px;">
                    <div class="univ-name">Mbeya University of Science and Technology</div>
                    <div class="univ-sub">P.O. Box 131, Mbeya, Tanzania &nbsp;|&nbsp; must.ac.tz</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="cert-title">Clearance Certificate</div>
    <div class="cert-subtitle">This certifies that the student named below has been cleared by all required departments</div>

    <div class="cert-no">
        Certificate No: MUST/CLC/{{ str_pad($clearance->id, 5, '0', STR_PAD_LEFT) }}/{{ date('Y') }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Issued: {{ now()->format('d F Y') }}
    </div>

    <!-- Student Info -->
    <div class="info-box">
        <table class="info-table">
            <tr>
                <td>Full Name:</td>
                <td><strong>{{ strtoupper($clearance->user->name) }}</strong></td>
                <td>Student ID:</td>
                <td><strong>{{ $clearance->user->student_id ?? '—' }}</strong></td>
            </tr>
            <tr>
                <td>Registration Number:</td>
                <td>{{ $clearance->user->registration_number ?? '—' }}</td>
                <td>Year of Study:</td>
                <td>{{ $clearance->user->year_of_study ?? '—' }}</td>
            </tr>
            <tr>
                <td>Programme:</td>
                <td>{{ $clearance->user->programme ?? '—' }}</td>
                <td>College:</td>
                <td>{{ $clearance->user->college ?? '—' }}</td>
            </tr>
            <tr>
                <td>Clearance Type:</td>
                <td>{{ ucfirst($clearance->clearance_type) }}</td>
                <td>Academic Year:</td>
                <td>{{ $clearance->academic_year }} &nbsp;/&nbsp; {{ $clearance->semester }}</td>
            </tr>
        </table>
    </div>

    <!-- Department Approvals -->
    <div class="dept-title">Departmental Clearance Details</div>
    <table class="dept-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Department</th>
                <th>Status</th>
                <th>Reviewed By</th>
                <th>Date Approved</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clearance->approvals->sortBy('department.priority') as $i => $approval)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $approval->department->name }}</strong></td>
                <td><span class="badge-approved">CLEARED</span></td>
                <td>{{ $approval->officer?->name ?? '—' }}</td>
                <td>{{ $approval->reviewed_at?->format('d M Y') ?? '—' }}</td>
                <td style="font-size:8pt;color:#64748b;">{{ $approval->comments ?? 'No remarks' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Declaration -->
    <div class="declaration">
        This is to certify that <strong>{{ strtoupper($clearance->user->name) }}</strong>
        (Student ID: <strong>{{ $clearance->user->student_id ?? 'N/A' }}</strong>) has satisfactorily
        fulfilled all departmental clearance requirements for
        <strong>{{ ucfirst($clearance->clearance_type) }} Clearance</strong>
        for the <strong>{{ $clearance->academic_year }}</strong> academic year,
        {{ $clearance->semester }}. All departments listed above have confirmed clearance.
        This certificate is issued without any dues, obligations, or liabilities outstanding
        against the student.
    </div>

    <!-- Signatures -->
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">
                    <div class="sig-name">Registrar</div>
                    Mbeya University of Science and Technology
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <div class="sig-name">Director of Academic Affairs</div>
                    Mbeya University of Science and Technology
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <div class="sig-name">System Administrator</div>
                    CMS — Automated on {{ now()->format('d M Y') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        This certificate was generated automatically by the MUST Clearance Management System on {{ now()->format('d F Y, H:i') }} EAT.
        For verification, contact the Registry Office. &nbsp;|&nbsp; Cert. Ref: MUST/CLC/{{ str_pad($clearance->id, 5, '0', STR_PAD_LEFT) }}/{{ date('Y') }}
    </div>

</div>
</body>
</html>
