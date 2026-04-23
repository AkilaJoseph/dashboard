@extends('layouts.app')

@section('title', 'Certificate Ledger')
@section('page-title', 'Certificate Ledger')
@section('page-subtitle', 'Tamper-evident audit trail of every issued clearance certificate')

@section('content')

    {{-- ── Chain integrity toolbar ─────────────────────────────────────────────── --}}
    <div class="glow-card" style="margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin:0 0 3px;">Chain Status</p>
            <p id="chain-summary" style="font-size:13px;color:#64748b;margin:0;">Press the button to walk every ledger row and verify the chain.</p>
        </div>
        <button id="btn-verify" onclick="verifyChain()"
                style="padding:9px 22px;border-radius:9px;border:none;background:#059669;color:#fff;font-size:13px;font-weight:700;cursor:pointer;flex-shrink:0;">
            Verify Chain Integrity
        </button>
    </div>

    {{-- ── Per-row results (hidden until verify runs) ──────────────────────────── --}}
    <div id="chain-results" style="display:none;margin-bottom:18px;">
        <div class="glow-card" style="padding:0;overflow:hidden;">
            <div style="padding:12px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin:0;">Row-by-Row Verification</p>
            </div>
            <div id="chain-rows" style="padding:12px 16px;display:flex;flex-direction:column;gap:6px;max-height:340px;overflow-y:auto;"></div>
        </div>
    </div>

    {{-- ── Ledger table ─────────────────────────────────────────────────────────── --}}
    <div class="glow-card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 22px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin:0;">
                Ledger Entries
                <span style="font-size:11px;background:#e0f2fe;border:1px solid #bae6fd;color:#0369a1;padding:1px 8px;border-radius:999px;font-weight:700;margin-left:6px;">{{ $entries->total() }}</span>
            </p>
        </div>

        @if($entries->isEmpty())
        <div style="padding:32px;text-align:center;">
            <p style="font-size:13px;color:#94a3b8;">No certificates have been issued yet.</p>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;border-bottom:1px solid #e2e8f0;">#</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;border-bottom:1px solid #e2e8f0;">Student</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;border-bottom:1px solid #e2e8f0;">Reference</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;border-bottom:1px solid #e2e8f0;">Hash (preview)</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;border-bottom:1px solid #e2e8f0;">Signed At</th>
                        <th style="padding:9px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#64748b;border-bottom:1px solid #e2e8f0;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                    @php
                        $clearance = $entry->clearance;
                        $user      = $clearance->user;
                        $refNo     = 'MUST/'
                            . strtoupper(substr($user->student_id ?? 'STU', 0, 8))
                            . '/' . str_pad($clearance->id, 5, '0', STR_PAD_LEFT)
                            . '/' . $clearance->submitted_at?->format('Ymd');
                    @endphp
                    <tr id="row-{{ $entry->sequence }}" style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:10px 14px;font-weight:700;color:#059669;">#{{ $entry->sequence }}</td>
                        <td style="padding:10px 14px;">
                            <p style="font-weight:600;color:#1e293b;margin:0;">{{ $user->name }}</p>
                            <p style="font-size:11px;color:#94a3b8;margin:0;">{{ $user->student_id ?? '—' }}</p>
                        </td>
                        <td style="padding:10px 14px;font-family:monospace;font-size:11px;color:#475569;">{{ $refNo }}</td>
                        <td style="padding:10px 14px;font-family:monospace;font-size:11px;color:#475569;">
                            {{ substr($entry->certificate_hash, 0, 8) }}&hellip;{{ substr($entry->certificate_hash, -8) }}
                        </td>
                        <td style="padding:10px 14px;color:#64748b;">{{ $entry->signed_at->format('d M Y, H:i') }}</td>
                        <td style="padding:10px 14px;">
                            <a href="{{ route('verify', $clearance->id) }}" target="_blank"
                               style="font-size:11px;font-weight:700;color:#059669;text-decoration:none;">
                                Verify &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
        <div style="padding:14px 20px;border-top:1px solid #e2e8f0;">
            {{ $entries->links() }}
        </div>
        @endif
        @endif
    </div>

<script>
const VERIFY_URL = '{{ route("admin.ledger.verify-chain") }}';
const CSRF       = document.querySelector('meta[name="csrf-token"]').content;

async function verifyChain() {
    const btn     = document.getElementById('btn-verify');
    const summary = document.getElementById('chain-summary');
    const results = document.getElementById('chain-results');
    const rowsEl  = document.getElementById('chain-rows');

    btn.disabled     = true;
    btn.textContent  = 'Verifying…';
    summary.textContent = 'Walking all ledger rows…';
    results.style.display = 'none';
    rowsEl.innerHTML = '';

    try {
        const res  = await fetch(VERIFY_URL, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        const data = await res.json();

        // Overall summary
        const ok = data.chain_intact;
        summary.innerHTML = ok
            ? `<span style="color:#059669;font-weight:700;">✓ Chain intact</span> — all ${data.total} row(s) verified successfully.`
            : `<span style="color:#ef4444;font-weight:700;">✗ Chain integrity failure</span> — one or more rows failed verification. See details below.`;

        // Per-row detail
        if (data.rows?.length) {
            data.rows.forEach(row => {
                const isOk  = row.status === 'verified' && row.prev_ok;
                const color = isOk ? '#059669' : '#ef4444';
                const icon  = isOk ? '✓' : '✗';
                const div   = document.createElement('div');
                div.style.cssText = `display:flex;align-items:flex-start;gap:10px;padding:9px 12px;border-radius:8px;border:1px solid ${isOk ? '#a7f3d0' : '#fca5a5'};background:${isOk ? '#f0fdf4' : '#fef2f2'};`;
                div.innerHTML = `
                    <span style="font-size:14px;color:${color};flex-shrink:0;margin-top:1px;">${icon}</span>
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:12px;font-weight:700;color:#1e293b;margin:0 0 2px;">#${row.sequence} — ${row.reference}</p>
                        <p style="font-size:11px;color:#64748b;margin:0;">
                            Hash: <strong style="color:${row.hash_valid ? '#059669' : '#ef4444'}">${row.hash_valid ? 'valid' : 'MISMATCH'}</strong>
                            &nbsp;·&nbsp; Sig: <strong style="color:${row.sig_valid ? '#059669' : '#ef4444'}">${row.sig_valid ? 'valid' : 'INVALID'}</strong>
                            &nbsp;·&nbsp; Prev link: <strong style="color:${row.prev_ok ? '#059669' : '#ef4444'}">${row.prev_ok ? 'ok' : 'BROKEN'}</strong>
                            &nbsp;·&nbsp; Status: <strong>${row.status}</strong>
                        </p>
                    </div>`;

                // Highlight the matching table row
                const tableRow = document.getElementById('row-' + row.sequence);
                if (tableRow) tableRow.style.background = isOk ? '#f0fdf4' : '#fef2f2';

                rowsEl.appendChild(div);
            });
            results.style.display = 'block';
        }
    } catch (err) {
        summary.textContent = 'Error: ' + err.message;
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Verify Chain Integrity';
    }
}
</script>

@endsection
