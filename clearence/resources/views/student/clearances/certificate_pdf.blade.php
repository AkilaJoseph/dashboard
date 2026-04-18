<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Student Clearance Form — {{ $clearance->user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            background: #fff;
        }

        .page { padding: 28px 38px; }

        /* ── Header ── */
        .header {
            border-bottom: 3px double #064e3b;
            padding-bottom: 12px;
            margin-bottom: 4px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }

        .emblem {
            width: 64px; height: 64px;
            text-align: center;
        }
        .emblem img { width: 64px; height: 64px; }
        .emblem-fallback {
            width: 64px; height: 64px;
            border: 2px solid #064e3b;
            border-radius: 50%;
            text-align: center;
            line-height: 60px;
            font-size: 10pt;
            font-weight: bold;
            color: #064e3b;
        }
        .univ-name {
            font-size: 13pt;
            font-weight: bold;
            color: #064e3b;
            line-height: 1.3;
        }
        .univ-address {
            font-size: 8pt;
            color: #475569;
            margin-top: 3px;
        }

        /* ── Form Title ── */
        .form-title-wrap {
            text-align: center;
            margin: 12px 0 4px;
        }
        .form-title {
            font-size: 15pt;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .form-subtitle {
            font-size: 8pt;
            color: #64748b;
            margin-top: 4px;
        }
        .ref-line {
            text-align: right;
            font-size: 8pt;
            color: #64748b;
            margin: 8px 0 12px;
        }

        /* ── Section heading ── */
        .section-heading {
            font-size: 9.5pt;
            font-weight: bold;
            color: #fff;
            background: #064e3b;
            padding: 5px 10px;
            margin: 14px 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Student Details ── */
        .student-box {
            border: 1px solid #d1fae5;
            border-top: none;
            padding: 10px 14px;
            background: #f0fdf4;
            margin-bottom: 0;
        }
        .student-table { width: 100%; border-collapse: collapse; }
        .student-table td { padding: 4px 6px; font-size: 9.5pt; }
        .student-table td.lbl { font-weight: bold; color: #065f46; width: 22%; }
        .student-table td.val { width: 28%; }

        /* ── Clearance Details Table ── */
        .dept-table { width: 100%; border-collapse: collapse; border: 1px solid #d1fae5; }
        .dept-table th {
            background: #065f46;
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
            border-bottom: 1px solid #e2e8f0;
        }
        .dept-table tr:nth-child(even) td { background: #f8fafc; }

        .badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-approved  { background: #d1fae5; color: #065f46; }
        .badge-pending   { background: #fef9c3; color: #854d0e; }
        .badge-rejected  { background: #fee2e2; color: #991b1b; }

        /* ── Final Status ── */
        .final-status-box {
            border: 2px solid #064e3b;
            padding: 10px 16px;
            margin: 14px 0;
            background: #f0fdf4;
        }
        .final-status-table { width: 100%; border-collapse: collapse; }
        .final-status-table td { padding: 4px 8px; font-size: 9.5pt; vertical-align: top; }
        .final-status-table td.lbl { font-weight: bold; color: #065f46; width: 28%; }

        .overall-badge {
            font-size: 11pt;
            font-weight: bold;
            padding: 3px 14px;
            border-radius: 4px;
        }
        .overall-approved { background: #d1fae5; color: #065f46; border: 1px solid #059669; }
        .overall-pending  { background: #fef9c3; color: #854d0e; border: 1px solid #d97706; }
        .overall-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #dc2626; }

        /* ── Signature ── */
        .sig-outer { margin-top: 20px; }
        .sig-table { width: 100%; border-collapse: collapse; }
        .sig-table td { width: 33.33%; padding: 0 8px; text-align: center; vertical-align: bottom; }
        .sig-line { border-top: 1px solid #333; margin-top: 38px; padding-top: 5px; }
        .sig-name { font-size: 9pt; font-weight: bold; }
        .sig-role { font-size: 7.5pt; color: #475569; margin-top: 2px; }

        /* ── QR + Footer row ── */
        .bottom-table { width: 100%; border-collapse: collapse; margin-top: 20px; border-top: 2px solid #064e3b; padding-top: 10px; }
        .bottom-table td { vertical-align: top; padding-top: 10px; }
        .qr-cell { width: 90px; text-align: center; }
        .qr-cell svg { width: 80px; height: 80px; }
        .qr-label { font-size: 6.5pt; color: #64748b; margin-top: 3px; }
        .footer-cell { padding-left: 12px; }
        .footer-text { font-size: 7.5pt; color: #64748b; line-height: 1.7; }
        .footer-note { font-size: 7pt; color: #94a3b8; margin-top: 6px; font-style: italic; }

        /* ── Watermark ── */
        .watermark {
            position: fixed;
            top: 48%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(5,150,105,0.035);
            font-weight: bold;
            z-index: -1;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="watermark">{{ strtoupper($clearance->status) }}</div>

    {{-- ═══ 1. HEADER ═══ --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:72px;">
                    @php $logoPath = public_path('images/must_logo.png'); @endphp
                    @if(file_exists($logoPath))
                        <div class="emblem"><img src="{{ $logoPath }}" alt="MUST Logo"/></div>
                    @else
                        <div class="emblem-fallback">MUST</div>
                    @endif
                </td>
                <td style="padding-left:12px;">
                    <div class="univ-name">Mbeya University of Science and Technology</div>
                    <div class="univ-address">P.O. Box 131, Mbeya, Tanzania &nbsp;|&nbsp; must.ac.tz &nbsp;|&nbsp; +255 25 240 4572</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Title --}}
    <div class="form-title-wrap">
        <div class="form-title">Student Clearance Form</div>
        <div class="form-subtitle">Official clearance document issued by the Registry Office</div>
    </div>
    <div class="ref-line">
        Form Ref: MUST/CLF/{{ str_pad($clearance->id, 5, '0', STR_PAD_LEFT) }}/{{ date('Y') }}
        &nbsp;|&nbsp; Date Issued: {{ now()->format('d F Y') }}
    </div>

    {{-- ═══ 2. STUDENT DETAILS ═══ --}}
    <div class="section-heading">Student Details</div>
    <div class="student-box">
        <table class="student-table">
            <tr>
                <td class="lbl">Full Name:</td>
                <td class="val"><strong>{{ strtoupper($clearance->user->name) }}</strong></td>
                <td class="lbl">Student ID:</td>
                <td class="val"><strong>{{ $clearance->user->student_id ?? '—' }}</strong></td>
            </tr>
            <tr>
                <td class="lbl">Registration No:</td>
                <td class="val">{{ $clearance->user->registration_number ?? '—' }}</td>
                <td class="lbl">Year of Study:</td>
                <td class="val">{{ $clearance->user->year_of_study ?? '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Course / Programme:</td>
                <td class="val">{{ $clearance->user->programme ?? '—' }}</td>
                <td class="lbl">College:</td>
                <td class="val">{{ $clearance->user->college ?? '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Clearance Type:</td>
                <td class="val">{{ ucfirst($clearance->clearance_type) }}</td>
                <td class="lbl">Academic Year:</td>
                <td class="val">{{ $clearance->academic_year }} / {{ $clearance->semester }}</td>
            </tr>
            @if($clearance->user->email)
            <tr>
                <td class="lbl">Email:</td>
                <td class="val" colspan="3">{{ $clearance->user->email }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ═══ 3. CLEARANCE DETAILS ═══ --}}
    <div class="section-heading">Clearance Details</div>
    <table class="dept-table">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:22%">Department</th>
                <th style="width:14%">Status</th>
                <th style="width:18%">Comment</th>
                <th style="width:18%">Reviewed By</th>
                <th style="width:14%">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clearance->approvals->sortBy('department.priority') as $i => $approval)
            @php
                $status = $approval->status ?? 'pending';
                $badgeClass = match($status) {
                    'approved' => 'badge-approved',
                    'rejected' => 'badge-rejected',
                    default    => 'badge-pending',
                };
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $approval->department->name }}</strong></td>
                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
                <td style="font-size:8pt;color:#374151;">{{ $approval->comments ?? '—' }}</td>
                <td style="font-size:8.5pt;">{{ $approval->officer?->name ?? '—' }}</td>
                <td style="font-size:8.5pt;">{{ $approval->reviewed_at?->format('d M Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══ 4. FINAL STATUS ═══ --}}
    @php
        $overallStatus = $clearance->status ?? 'pending';
        $overallBadge  = match($overallStatus) {
            'approved' => 'overall-approved',
            'rejected' => 'overall-rejected',
            default    => 'overall-pending',
        };
    @endphp
    <div class="section-heading">Final Status</div>
    <div class="final-status-box">
        <table class="final-status-table">
            <tr>
                <td class="lbl">Overall Status:</td>
                <td>
                    <span class="overall-badge {{ $overallBadge }}">
                        {{ strtoupper($overallStatus) }}
                    </span>
                </td>
                <td class="lbl">Submitted On:</td>
                <td>{{ $clearance->submitted_at?->format('d F Y') ?? '—' }}</td>
            </tr>
            @if($clearance->reason)
            <tr>
                <td class="lbl">Final Comment:</td>
                <td colspan="3">{{ $clearance->reason }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ═══ 5. SIGNATURE SECTION ═══ --}}
    <div class="sig-outer">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">Registrar</div>
                        <div class="sig-role">Mbeya University of Science and Technology</div>
                    </div>
                </td>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">Director of Academic Affairs</div>
                        <div class="sig-role">Mbeya University of Science and Technology</div>
                    </div>
                </td>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">System Administrator</div>
                        <div class="sig-role">MUST Clearance Management System</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ═══ 6. QR CODE + FOOTER ═══ --}}
    <table class="bottom-table">
        <tr>
            <td class="qr-cell">
                {!! $qrCode !!}
                <div class="qr-label">Scan to verify</div>
            </td>
            <td class="footer-cell">
                <div class="footer-text">
                    <strong>Verification Code:</strong> {{ $verificationCode }}<br>
                    <strong>System:</strong> MUST Automated Clearance System<br>
                    <strong>Generated:</strong> {{ now()->format('d F Y, H:i') }} EAT<br>
                    <strong>Contact:</strong> Registry Office, must.ac.tz
                </div>
                <div class="footer-note">
                    This is a system-generated document. For verification, present this form together with your student ID
                    at the Registry Office or scan the QR code above.
                </div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
