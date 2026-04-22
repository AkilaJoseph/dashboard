@extends('layouts.app')

@section('title', 'My ID Card')
@section('page-title', 'My ID Card')
@section('page-subtitle', 'Show this QR code to a department officer to scan your clearance')

@section('content')
<div style="max-width:480px;margin:0 auto;">

    @if(! $clearance)
    <div class="glow-card" style="text-align:center;padding:32px;">
        <p style="font-size:32px;margin:0 0 12px;">📋</p>
        <p style="font-size:15px;font-weight:700;color:#1e293b;margin:0 0 6px;">No active clearance</p>
        <p style="font-size:13px;color:#64748b;margin:0 0 18px;">Submit a clearance request first, then come back to generate your QR card.</p>
        <a href="{{ route('student.clearances.create') }}" class="btn-glow" style="font-size:13px;">Apply for Clearance</a>
    </div>
    @else

    {{-- ── QR Card ─────────────────────────────────────────────────────────────── --}}
    <div class="glow-card" style="text-align:center;padding:28px 24px;">

        {{-- Header --}}
        <p style="font-size:10px;font-weight:800;letter-spacing:0.12em;text-transform:uppercase;color:#059669;margin:0 0 4px;">MUST · ACIMS</p>
        <p style="font-size:16px;font-weight:700;color:#1e293b;margin:0 0 18px;">Clearance ID Card</p>

        {{-- QR --}}
        <div id="qr-wrap" style="display:inline-block;padding:12px;background:#fff;border:2px solid #e2e8f0;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,0.06);margin-bottom:18px;line-height:0;">
            {!! $qrSvg !!}
        </div>

        {{-- Student info --}}
        <p style="font-size:15px;font-weight:700;color:#1e293b;margin:0 0 3px;">{{ $student->name }}</p>
        @if($student->student_id)
        <p style="font-size:12px;color:#64748b;margin:0 0 3px;">{{ $student->student_id }}</p>
        @endif
        @if($student->programme)
        <p style="font-size:12px;color:#94a3b8;margin:0 0 14px;">{{ $student->programme }}</p>
        @else
        <div style="margin-bottom:14px;"></div>
        @endif

        {{-- Clearance badge --}}
        @php
            $badgeColors = [
                'pending'     => ['bg'=>'#fef9c3','border'=>'#fde047','text'=>'#854d0e'],
                'in_progress' => ['bg'=>'#e0f2fe','border'=>'#7dd3fc','text'=>'#075985'],
                'approved'    => ['bg'=>'#dcfce7','border'=>'#86efac','text'=>'#166534'],
                'rejected'    => ['bg'=>'#fee2e2','border'=>'#fca5a5','text'=>'#991b1b'],
            ];
            $bc = $badgeColors[$clearance->status] ?? $badgeColors['pending'];
        @endphp
        <div style="display:inline-flex;align-items:center;gap:7px;padding:5px 14px;border-radius:999px;border:1px solid {{ $bc['border'] }};background:{{ $bc['bg'] }};margin-bottom:18px;">
            <span style="width:7px;height:7px;border-radius:50%;background:{{ $bc['text'] }};display:inline-block;"></span>
            <span style="font-size:12px;font-weight:700;color:{{ $bc['text'] }};">{{ ucfirst(str_replace('_',' ',$clearance->status)) }} · {{ ucfirst($clearance->clearance_type) }}</span>
        </div>

        {{-- Token expiry timer --}}
        <div style="padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;margin-bottom:6px;">
            <p id="timer-label" style="font-size:11px;color:#94a3b8;margin:0 0 3px;">QR expires in</p>
            <p id="timer-value" style="font-size:22px;font-weight:800;color:#059669;margin:0;letter-spacing:0.04em;">5:00</p>
        </div>

        <p id="refresh-msg" style="font-size:11px;color:#94a3b8;margin:0 0 16px;min-height:16px;"></p>

        <button id="btn-refresh" onclick="window.qrCard?.refresh()"
                style="padding:8px 22px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:12px;font-weight:700;cursor:pointer;color:#475569;">
            Refresh QR Now
        </button>
    </div>

    {{-- ── Offline banner ───────────────────────────────────────────────────────── --}}
    <div id="offline-banner" style="display:none;padding:10px 16px;background:#fef9c3;border:1px solid #fde047;border-radius:10px;text-align:center;margin-top:14px;">
        <p style="font-size:12px;font-weight:600;color:#854d0e;margin:0;">You are offline · Showing last cached QR code</p>
    </div>

    {{-- ── Clearance summary ────────────────────────────────────────────────────── --}}
    <div class="glow-card" style="margin-top:14px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:12px;">Clearance Summary</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            @foreach([
                ['Academic Year', $clearance->academic_year],
                ['Semester',      $clearance->semester],
                ['Type',          ucfirst($clearance->clearance_type)],
                ['Submitted',     $clearance->submitted_at?->format('d M Y') ?? '—'],
            ] as [$lbl, $val])
            <div style="padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin:0 0 3px;">{{ $lbl }}</p>
                <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0;">{{ $val }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div style="margin-top:18px;text-align:center;">
        <a href="{{ route('student.clearances.show', $clearance) }}" style="font-size:13px;color:#64748b;text-decoration:none;">View Full Clearance Details &rarr;</a>
    </div>

    @endif

    <div style="margin-top:18px;">
        <a href="{{ route('student.dashboard') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Back to Dashboard</a>
    </div>

</div>

@if($clearance)
<script type="module">
import QrCard from '{{ Vite::asset('resources/js/qr/my-card.js') }}';

window.qrCard = new QrCard({
    apiUrl:       '{{ route("api.student.qr-token") }}',
    csrfToken:    document.querySelector('meta[name="csrf-token"]').content,
    expiresAt:    '{{ $expiresAt }}',
    clearanceId:  {{ $clearance->id }},
    onNewSvg(svg) {
        document.getElementById('qr-wrap').innerHTML = svg;
    },
    onTimer(label, value) {
        document.getElementById('timer-label').textContent = label;
        document.getElementById('timer-value').textContent = value;
    },
    onStatus(msg) {
        document.getElementById('refresh-msg').textContent = msg;
    },
    onOffline(isOffline) {
        document.getElementById('offline-banner').style.display = isOffline ? 'block' : 'none';
    },
});

window.qrCard.start();
</script>
@endif
@endsection
