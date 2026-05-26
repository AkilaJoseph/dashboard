<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification — MUST ACIMS</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f1f5f9; color: #1e293b; min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 40px 16px; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 32px rgba(0,0,0,0.08); max-width: 600px; width: 100%; overflow: hidden; }
        .banner { padding: 28px 32px; }
        .banner-verified    { background: linear-gradient(135deg, #059669, #10b981); }
        .banner-tampered    { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .banner-invalid-signature { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .banner-not-found   { background: linear-gradient(135deg, #64748b, #94a3b8); }
        .banner-key-missing { background: linear-gradient(135deg, #7c3aed, #a78bfa); }
        .banner h1 { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 4px; }
        .banner p  { font-size: 13px; color: rgba(255,255,255,0.85); }
        .body { padding: 28px 32px; }
        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #94a3b8; margin-bottom: 12px; margin-top: 22px; }
        .section-title:first-child { margin-top: 0; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .field { padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; }
        .field .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #94a3b8; margin-bottom: 3px; }
        .field .value { font-size: 13px; font-weight: 600; color: #1e293b; word-break: break-all; }
        .mono { font-family: 'SF Mono', 'Fira Mono', monospace; font-size: 11px; }
        .checks { display: flex; flex-direction: column; gap: 8px; margin-top: 4px; }
        .check-row { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 9px; border: 1px solid; }
        .check-pass { border-color: #a7f3d0; background: #f0fdf4; }
        .check-fail { border-color: #fca5a5; background: #fef2f2; }
        .check-warn { border-color: #fde68a; background: #fefce8; }
        .check-icon { font-size: 16px; flex-shrink: 0; }
        .check-text { font-size: 13px; font-weight: 600; color: #1e293b; }
        .check-sub  { font-size: 11px; color: #64748b; margin-top: 1px; }
        .dept-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .dept-table th { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; padding: 6px 10px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .dept-table td { font-size: 12px; padding: 8px 10px; border-bottom: 1px solid #f1f5f9; }
        .badge { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-pending  { background: #fef9c3; color: #854d0e; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .footer { padding: 18px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="card">

    @if(! $found)
    <div class="banner banner-not-found">
        <h1>No Ledger Entry</h1>
        <p>This clearance certificate has not been issued yet or was generated before the ledger was introduced.</p>
    </div>
    <div class="body">
        <p class="section-title">Clearance</p>
        <div class="grid">
            <div class="field"><div class="label">Student</div><div class="value">{{ $clearance->user->name }}</div></div>
            <div class="field"><div class="label">Student ID</div><div class="value">{{ $clearance->user->student_id ?? '—' }}</div></div>
            <div class="field"><div class="label">Status</div><div class="value">{{ ucfirst($clearance->status) }}</div></div>
        </div>
    </div>

    @else
    @php
        $status       = $result['status'];
        $bannerClass  = 'banner-' . $status;
        $bannerTitles = [
            'verified'          => '✓ Certificate Verified',
            'tampered'          => '✗ Certificate Tampered',
            'invalid-signature' => '⚠ Signature Invalid',
            'key-missing'       => '⚠ Key Not Configured',
        ];
        $bannerDescs = [
            'verified'          => 'The certificate hash and HMAC signature both check out. This document is authentic.',
            'tampered'          => 'The certificate data no longer matches the hash recorded at issuance. The document may have been altered.',
            'invalid-signature' => 'The HMAC signature does not match. The ledger row may have been modified after signing.',
            'key-missing'       => 'The LEDGER_HMAC_KEY is not configured on this server. Signature cannot be verified.',
        ];
        $refNo = 'MUST/'
            . strtoupper(substr($clearance->user->student_id ?? 'STU', 0, 8))
            . '/' . str_pad($clearance->id, 5, '0', STR_PAD_LEFT)
            . '/' . $clearance->submitted_at?->format('Ymd');
    @endphp

    <div class="banner {{ $bannerClass }}">
        <h1>{{ $bannerTitles[$status] ?? ucfirst($status) }}</h1>
        <p>{{ $bannerDescs[$status] ?? '' }}</p>
    </div>

    <div class="body">

        {{-- Verification checks --}}
        <p class="section-title">Verification Checks</p>
        <div class="checks">
            <div class="check-row {{ $result['hash_valid'] ? 'check-pass' : 'check-fail' }}">
                <span class="check-icon">{{ $result['hash_valid'] ? '✓' : '✗' }}</span>
                <div>
                    <div class="check-text">Document Hash</div>
                    <div class="check-sub">{{ $result['hash_valid'] ? 'SHA-256 of the canonical payload matches the stored hash.' : 'Hash mismatch — certificate data has been changed.' }}</div>
                </div>
            </div>
            <div class="check-row {{ $result['sig_valid'] ? 'check-pass' : ($status === 'key-missing' ? 'check-warn' : 'check-fail') }}">
                <span class="check-icon">{{ $result['sig_valid'] ? '✓' : ($status === 'key-missing' ? '?' : '✗') }}</span>
                <div>
                    <div class="check-text">HMAC Signature</div>
                    <div class="check-sub">{{ $result['sig_valid'] ? 'Signature is valid — ledger row has not been altered.' : ($status === 'key-missing' ? 'Cannot check: signing key not configured on this server.' : 'Signature invalid — the ledger row may have been edited.') }}</div>
                </div>
            </div>
        </div>

        {{-- Ledger metadata --}}
        <p class="section-title">Ledger Record</p>
        <div class="grid">
            <div class="field"><div class="label">Sequence</div><div class="value">#{{ $entry->sequence }}</div></div>
            <div class="field"><div class="label">Reference</div><div class="value">{{ $refNo }}</div></div>
            <div class="field"><div class="label">Issued At</div><div class="value">{{ $entry->signed_at->format('d M Y, H:i') }} UTC</div></div>
            <div class="field"><div class="label">Clearance Type</div><div class="value">{{ ucfirst($clearance->clearance_type) }}</div></div>
        </div>
        <div class="field" style="margin-top:10px;">
            <div class="label">Certificate Hash (SHA-256)</div>
            <div class="value mono">{{ $entry->certificate_hash }}</div>
        </div>
        <div class="field" style="margin-top:8px;">
            <div class="label">Previous Hash</div>
            <div class="value mono">{{ $entry->previous_hash }}</div>
        </div>

        {{-- Student --}}
        <p class="section-title">Student</p>
        <div class="grid">
            <div class="field"><div class="label">Full Name</div><div class="value">{{ $clearance->user->name }}</div></div>
            <div class="field"><div class="label">Student ID</div><div class="value">{{ $clearance->user->student_id ?? '—' }}</div></div>
            <div class="field"><div class="label">Programme</div><div class="value">{{ $clearance->user->programme ?? '—' }}</div></div>
            <div class="field"><div class="label">College</div><div class="value">{{ $clearance->user->college ?? '—' }}</div></div>
        </div>

        {{-- Approval chain --}}
        <p class="section-title">Approval Chain</p>
        <table class="dept-table">
            <thead><tr><th>Department</th><th>Status</th><th>Officer</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($clearance->approvals->sortBy('department.priority') as $approval)
                <tr>
                    <td>{{ $approval->department->name }}</td>
                    <td><span class="badge badge-{{ $approval->status }}">{{ ucfirst($approval->status) }}</span></td>
                    <td>{{ $approval->officer?->name ?? '—' }}</td>
                    <td>{{ $approval->reviewed_at?->format('d M Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
    @endif

    <div class="footer">
        MUST Automated Clearance Information Management System &nbsp;·&nbsp; must.ac.tz
        &nbsp;·&nbsp; Ledger verification is cryptographic — results are definitive.
    </div>

</div>
</body>
</html>
