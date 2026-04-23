@extends('layouts.app')
@section('title', 'Clearance Details')
@section('page-title', 'Clearance Details')
@section('page-subtitle', 'Track your departmental approval progress in real time')

@section('content')
@php
    $approved = $clearance->approvals->where('status','approved')->count();
    $rejected = $clearance->approvals->where('status','rejected')->count();
    $total    = $clearance->approvals->count();
    $pct      = $total > 0 ? round(($approved/$total)*100) : 0;
    $user     = $clearance->user;
    $verCode  = 'MUST/'.strtoupper(substr($user->student_id??'STU',0,8)).'/'.str_pad($clearance->id,5,'0',STR_PAD_LEFT).'/'.$clearance->submitted_at->format('Ymd');
@endphp

<style>
/* ── Animations ── */
@keyframes slideUp   { from{opacity:0;transform:translateY(22px)} to{opacity:1;transform:translateY(0)} }
@keyframes slideIn   { from{opacity:0;transform:translateX(-14px)} to{opacity:1;transform:translateX(0)} }
@keyframes popIn     { from{opacity:0;transform:scale(0.88)} to{opacity:1;transform:scale(1)} }
@keyframes pulse-dot { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.5);opacity:.5} }
@keyframes shimmer   { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
@keyframes fill-bar  { from{width:0} to{width:{{ $pct }}%} }
@keyframes modalIn   { from{opacity:0;transform:translateY(40px) scale(0.97)} to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes overlayIn { from{opacity:0} to{opacity:1} }
@keyframes spin      { to{transform:rotate(360deg)} }
@keyframes checkPop  { 0%{transform:scale(0)} 60%{transform:scale(1.2)} 100%{transform:scale(1)} }

.card-anim   { animation: slideUp 0.4s ease both; }
.dept-item   { animation: slideIn 0.35s ease both; }

.dept-row {
    display:flex; align-items:flex-start; gap:14px;
    padding:15px 16px; border-radius:12px; border:1.5px solid;
    margin-bottom:8px; transition:all 0.2s; cursor:default;
}
.dept-row:hover { transform:translateX(4px); box-shadow:0 4px 16px rgba(0,0,0,0.06); }
.dept-approved { border-color:#a7f3d0; background:linear-gradient(135deg,#f0fdf4,#ecfdf5); }
.dept-rejected { border-color:#fecaca; background:linear-gradient(135deg,#fef2f2,#fff1f1); }
.dept-pending  { border-color:#e2e8f0; background:linear-gradient(135deg,#f8fafc,#f1f5f9); }

.dept-icon {
    width:38px; height:38px; border-radius:50%;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
    transition: transform 0.2s;
}
.dept-row:hover .dept-icon { transform:scale(1.1); }
.icon-approved { background:linear-gradient(135deg,#059669,#10b981); box-shadow:0 3px 10px rgba(5,150,105,0.3); }
.icon-rejected { background:linear-gradient(135deg,#ef4444,#f87171); box-shadow:0 3px 10px rgba(239,68,68,0.3); }
.icon-pending  { background:#f1f5f9; border:1.5px solid #cbd5e1; }

.pending-pulse {
    width:9px; height:9px; border-radius:50%; background:#d97706;
    animation: pulse-dot 1.5s ease-in-out infinite;
    display:inline-block; margin-right:6px;
}

/* ── Stat pills ── */
.stat-pill {
    display:flex; flex-direction:column; align-items:center;
    padding:14px 20px; border-radius:12px; flex:1; transition:transform 0.2s;
}
.stat-pill:hover { transform:translateY(-2px); }

/* ── Progress bar ── */
.prog-fill {
    height:10px; border-radius:999px;
    animation: fill-bar 1.4s cubic-bezier(.4,0,.2,1) forwards;
    background: linear-gradient(90deg, #059669, #10b981, #34d399);
    background-size: 200% auto;
    animation: fill-bar 1.4s cubic-bezier(.4,0,.2,1) forwards,
               shimmer 2s linear infinite;
}
.prog-fill-warn {
    background: linear-gradient(90deg, #d97706, #f59e0b, #fbbf24);
    background-size: 200% auto;
    animation: fill-bar 1.4s cubic-bezier(.4,0,.2,1) forwards,
               shimmer 2s linear infinite;
}

/* ── Preview Modal ── */
.modal-overlay {
    position:fixed; inset:0; background:rgba(0,0,0,0.65);
    z-index:10000; display:none; align-items:flex-start; justify-content:center;
    padding:24px 16px; overflow-y:auto;
    animation: overlayIn 0.2s ease;
    backdrop-filter: blur(4px);
}
.modal-overlay.open { display:flex; }
.modal-box {
    background:#fff; border-radius:16px; width:100%; max-width:820px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.22);
    animation: modalIn 0.3s cubic-bezier(.34,1.56,.64,1) both;
    overflow:hidden; margin:auto;
}
.modal-topbar {
    background:linear-gradient(135deg,#064e3b,#059669);
    padding:14px 20px; display:flex; align-items:center; justify-content:space-between;
}
.modal-close {
    background:rgba(255,255,255,0.15); border:none; color:#fff;
    width:32px; height:32px; border-radius:8px; cursor:pointer;
    display:flex; align-items:center; justify-content:center; transition:background 0.15s;
}
.modal-close:hover { background:rgba(255,255,255,0.28); }

/* ── PDF Preview Styles (inside modal) ── */
.pdf-preview {
    padding:32px 36px; font-family:'Segoe UI',sans-serif; font-size:13px; color:#1a1a1a;
    max-height:75vh; overflow-y:auto;
}
.pdf-header { border-bottom:3px double #064e3b; padding-bottom:14px; margin-bottom:4px; display:flex; align-items:center; gap:14px; }
.pdf-emblem { width:58px;height:58px;border:2px solid #064e3b;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:12px;color:#064e3b;flex-shrink:0; }
.pdf-title { text-align:center;font-size:18px;font-weight:800;color:#064e3b;text-transform:uppercase;letter-spacing:3px;margin:14px 0 2px; }
.pdf-subtitle { text-align:center;font-size:11px;color:#64748b;margin-bottom:6px; }
.pdf-ref { text-align:right;font-size:10px;color:#64748b;margin-bottom:14px; }
.pdf-section { background:#064e3b;color:#d1fae5;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;padding:6px 12px;margin:16px 0 0; }
.pdf-box { border:1px solid #d1fae5;border-top:none;background:#f0fdf4;padding:12px 14px; }
.pdf-table { width:100%;border-collapse:collapse; }
.pdf-table th { background:#065f46;color:#fff;font-size:10px;text-transform:uppercase;letter-spacing:0.5px;padding:8px 10px;text-align:left; }
.pdf-table td { font-size:12px;padding:8px 10px;border-bottom:1px solid #f1f5f9; }
.pdf-table tr:nth-child(even) td { background:#f8fafc; }
.pdf-badge { padding:2px 9px;border-radius:3px;font-size:10px;font-weight:700;text-transform:uppercase; }
.pdf-approved { background:#d1fae5;color:#065f46; }
.pdf-pending  { background:#fef9c3;color:#854d0e; }
.pdf-rejected { background:#fee2e2;color:#991b1b; }
.pdf-final { border:2px solid #064e3b;padding:12px 16px;margin:16px 0;background:#f0fdf4; }
.pdf-sig-wrap { display:flex;gap:0;margin-top:22px; }
.pdf-sig { flex:1;text-align:center;padding:0 8px; }
.pdf-sig-line { border-top:1px solid #333;margin-top:34px;padding-top:5px;font-size:10px;color:#475569; }
.pdf-footer-bar { border-top:2px solid #064e3b;padding-top:10px;margin-top:18px;display:flex;align-items:center;gap:12px; }
.pdf-qr { width:68px;height:68px;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:8px;color:#94a3b8;flex-shrink:0;border-radius:4px; }

/* ── Btn group ── */
.btn-preview {
    display:inline-flex;align-items:center;gap:8px;
    background:rgba(255,255,255,0.18);color:#fff;font-weight:700;font-size:13px;
    padding:9px 18px;border-radius:8px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.35);
    transition:all 0.2s;cursor:pointer;
}
.btn-preview:hover { background:rgba(255,255,255,0.28); transform:translateY(-1px); }
.btn-download-gold {
    display:inline-flex;align-items:center;gap:8px;
    background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#1e293b;font-weight:700;font-size:13px;
    padding:9px 18px;border-radius:8px;text-decoration:none;
    box-shadow:0 3px 12px rgba(251,191,36,0.35); transition:all 0.2s;
}
.btn-download-gold:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(251,191,36,0.45); }
</style>

<div style="max-width:860px;margin:0 auto;">

    {{-- ══ Header Card ══ --}}
    <div class="glow-card card-anim" style="padding:0;overflow:hidden;margin-bottom:18px;border:none;box-shadow:0 4px 20px rgba(0,0,0,0.08);" data-delay="0">
        <div style="padding:20px 24px;background:linear-gradient(135deg,#064e3b 0%,#059669 60%,#10b981 100%);position:relative;overflow:hidden;">
            {{-- decorative circles --}}
            <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.06);pointer-events:none;"></div>
            <div style="position:absolute;bottom:-20px;left:60px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.04);pointer-events:none;"></div>
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;position:relative;">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <span style="font-size:10px;font-weight:700;color:rgba(209,250,229,0.65);text-transform:uppercase;letter-spacing:0.1em;background:rgba(255,255,255,0.1);padding:2px 8px;border-radius:999px;">{{ ucfirst($clearance->clearance_type) }}</span>
                    </div>
                    <h3 style="font-size:17px;font-weight:800;color:#fff;margin:0 0 3px;letter-spacing:-0.3px;">{{ $clearance->academic_year }} — {{ $clearance->semester }} Semester</h3>
                    <p style="font-size:12px;color:rgba(209,250,229,0.7);margin:0;">Submitted {{ $clearance->submitted_at->format('d M Y') }}</p>
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <button onclick="openPreview()" class="btn-preview">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Preview Form
                    </button>
                    <a href="{{ route('student.clearances.certificate', $clearance) }}" class="btn-download-gold">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- Stat pills --}}
        <div style="display:flex;gap:0;border-bottom:1px solid #f1f5f9;">
            <div class="stat-pill" style="background:#f0fdf4;border-right:1px solid #f1f5f9;">
                <span style="font-size:22px;font-weight:800;color:#059669;" id="count-approved">0</span>
                <span style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.07em;margin-top:2px;">Approved</span>
            </div>
            <div class="stat-pill" style="background:#fef9c3;border-right:1px solid #f1f5f9;">
                <span style="font-size:22px;font-weight:800;color:#d97706;" id="count-pending">0</span>
                <span style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.07em;margin-top:2px;">Pending</span>
            </div>
            <div class="stat-pill" style="background:#fef2f2;">
                <span style="font-size:22px;font-weight:800;color:#ef4444;" id="count-rejected">0</span>
                <span style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.07em;margin-top:2px;">Rejected</span>
            </div>
        </div>

        {{-- Progress --}}
        <div style="padding:16px 24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;font-weight:600;color:#475569;">Overall Progress</span>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:12px;font-weight:700;color:#1e293b;">{{ $approved }}/{{ $total }} Departments</span>
                    @if($clearance->status==='approved')
                    <span class="badge badge-approved">
                        <span style="animation:checkPop 0.4s ease 0.3s both;display:inline-block;">✓</span> &nbsp;Fully Approved
                    </span>
                    @elseif($clearance->status==='rejected')
                    <span class="badge badge-rejected">Rejected</span>
                    @elseif($clearance->status==='in_progress')
                    <span class="badge badge-progress">In Progress</span>
                    @else
                    <span class="badge badge-pending"><span class="pending-pulse"></span>Pending</span>
                    @endif
                </div>
            </div>
            <div style="width:100%;background:#e2e8f0;border-radius:999px;height:10px;overflow:hidden;">
                <div class="{{ $pct===100 ? 'prog-fill' : 'prog-fill-warn' }}" style="width:0;height:10px;border-radius:999px;"></div>
            </div>
            <p style="font-size:10px;color:#94a3b8;margin-top:5px;text-align:right;">{{ $pct }}% complete</p>
        </div>

        {{-- Student Details --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid #f1f5f9;">
            @php $cells=[['Student',$user->name],['Student ID',$user->student_id??'N/A'],['Submitted',$clearance->submitted_at->format('d M Y')],['Completed',$clearance->completed_at?$clearance->completed_at->format('d M Y'):'—']]; @endphp
            @foreach($cells as $i=>[$label,$val])
            <div style="padding:13px 18px;{{ $i<3 ? 'border-right:1px solid #f1f5f9;' : '' }}">
                <p style="font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin:0 0 4px;">{{ $label }}</p>
                <p style="font-size:13px;font-weight:600;color:{{ $i===1 ? '#d97706' : '#1e293b' }};margin:0;{{ $i===1 ? 'font-family:monospace;' : '' }}">{{ $val }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Prediction widget: only while clearance is not yet finalised --}}
    @if(in_array($clearance->status, ['pending', 'in_progress']))
        @include('student.clearances.partials.prediction-widget')
    @endif

    {{-- ══ Department Cards ══ --}}
    <div class="glow-card card-anim" style="margin-bottom:18px;animation-delay:0.1s;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">
            <h3 style="font-size:14px;font-weight:700;color:#1e293b;margin:0;">Departmental Clearance Status</h3>
            <span style="font-size:11px;color:#94a3b8;">{{ $total }} departments</span>
        </div>

        @foreach($clearance->approvals->sortBy('department.priority') as $i => $approval)
        @php $delay = 0.05 * ($i+1); @endphp
        <div class="dept-row dept-item {{ $approval->status==='approved' ? 'dept-approved' : ($approval->status==='rejected' ? 'dept-rejected' : 'dept-pending') }}"
             style="animation-delay:{{ $delay }}s;">
            <div class="dept-icon {{ $approval->status==='approved' ? 'icon-approved' : ($approval->status==='rejected' ? 'icon-rejected' : 'icon-pending') }}">
                @if($approval->status==='approved')
                <svg style="width:16px;height:16px;color:#fff;animation:checkPop 0.4s ease {{ $delay }}s both;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                @elseif($approval->status==='rejected')
                <svg style="width:16px;height:16px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                @else
                <span style="font-size:11px;font-weight:700;color:#64748b;">{{ $i+1 }}</span>
                @endif
            </div>

            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:3px;">
                    <p style="font-size:13px;font-weight:700;color:#1e293b;margin:0;">{{ $approval->department->name }}</p>
                    @if($approval->status==='approved')
                    <span class="badge badge-approved">✓ Approved</span>
                    @elseif($approval->status==='rejected')
                    <span class="badge badge-rejected">✗ Rejected</span>
                    @else
                    <span class="badge badge-pending"><span class="pending-pulse"></span>Pending</span>
                    @endif
                </div>
                @if($approval->officer)
                <p style="font-size:11px;color:#64748b;margin:0;">Reviewed by <strong style="color:#374151;">{{ $approval->officer->name }}</strong></p>
                @endif
                @if($approval->reviewed_at)
                <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;">{{ $approval->reviewed_at->format('d M Y, h:i A') }}</p>
                @endif
                @if($approval->comments)
                <div style="margin-top:8px;padding:8px 12px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;font-size:11px;color:#475569;font-style:italic;">
                    "{{ $approval->comments }}"
                </div>
                @endif
                @if($approval->status === 'pending')
                <p style="font-size:11px;color:#d97706;margin:6px 0 0;display:flex;align-items:center;gap:5px;">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    Awaiting review from this department...
                </p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($clearance->reason)
    <div class="glow-card card-anim" style="margin-bottom:18px;animation-delay:0.2s;">
        <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin:0 0 8px;">Student Notes</p>
        <p style="font-size:13px;color:#475569;line-height:1.7;margin:0;">{{ $clearance->reason }}</p>
    </div>
    @endif

    <a href="{{ route('student.clearances.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:color 0.15s;" onmouseover="this.style.color='#059669'" onmouseout="this.style.color='#64748b'">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to My Clearances
    </a>
</div>

{{-- ══════════════════════════════════════════
     PDF PREVIEW MODAL
══════════════════════════════════════════ --}}
<div class="modal-overlay" id="previewModal" onclick="if(event.target===this)closePreview()">
    <div class="modal-box">

        {{-- Modal top bar --}}
        <div class="modal-topbar">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg width="16" height="16" fill="none" stroke="#d1fae5" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span style="font-size:13px;font-weight:700;color:#d1fae5;">Clearance Form Preview</span>
                <span style="font-size:10px;color:rgba(209,250,229,0.55);background:rgba(255,255,255,0.1);padding:2px 7px;border-radius:999px;">as it will appear in PDF</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <a href="{{ route('student.clearances.certificate', $clearance) }}" class="btn-download-gold" style="font-size:12px;padding:7px 14px;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </a>
                <button class="modal-close" onclick="closePreview()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Paper preview --}}
        <div class="pdf-preview">

            {{-- Header --}}
            <div class="pdf-header">
                @php $logoPath = public_path('images/must_logo.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="/images/must_logo.png" alt="MUST" style="width:58px;height:58px;border-radius:50%;object-fit:cover;border:2px solid #064e3b;flex-shrink:0;">
                @else
                    <div class="pdf-emblem">MUST</div>
                @endif
                <div>
                    <p style="font-size:14px;font-weight:800;color:#064e3b;margin:0;line-height:1.2;">Mbeya University of Science and Technology</p>
                    <p style="font-size:10px;color:#64748b;margin:3px 0 0;">P.O. Box 131, Mbeya, Tanzania &nbsp;|&nbsp; must.ac.tz &nbsp;|&nbsp; +255 25 240 4572</p>
                </div>
            </div>

            <div class="pdf-title">Student Clearance Form</div>
            <div class="pdf-subtitle">Official clearance document issued by the Registry Office</div>
            <div class="pdf-ref">
                Form Ref: MUST/CLF/{{ str_pad($clearance->id,5,'0',STR_PAD_LEFT) }}/{{ date('Y') }}
                &nbsp;|&nbsp; Date Issued: {{ now()->format('d F Y') }}
            </div>

            {{-- Student Details --}}
            <div class="pdf-section">Student Details</div>
            <div class="pdf-box">
                <table class="pdf-table">
                    <tr>
                        <td style="font-weight:700;color:#065f46;width:25%;">Full Name</td>
                        <td style="width:25%;font-weight:700;">{{ strtoupper($user->name) }}</td>
                        <td style="font-weight:700;color:#065f46;width:25%;">Student ID</td>
                        <td style="width:25%;font-weight:700;color:#d97706;font-family:monospace;">{{ $user->student_id ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:700;color:#065f46;">Registration No</td>
                        <td>{{ $user->registration_number ?? '—' }}</td>
                        <td style="font-weight:700;color:#065f46;">Year of Study</td>
                        <td>{{ $user->year_of_study ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:700;color:#065f46;">Programme</td>
                        <td>{{ $user->entry_programme ?? $user->programme ?? '—' }}</td>
                        <td style="font-weight:700;color:#065f46;">College</td>
                        <td>{{ $user->college ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight:700;color:#065f46;">Clearance Type</td>
                        <td>{{ ucfirst($clearance->clearance_type) }}</td>
                        <td style="font-weight:700;color:#065f46;">Academic Year</td>
                        <td>{{ $clearance->academic_year }} / {{ $clearance->semester }}</td>
                    </tr>
                </table>
            </div>

            {{-- Clearance Details --}}
            <div class="pdf-section" style="margin-top:14px;">Clearance Details</div>
            <table class="pdf-table" style="border:1px solid #d1fae5;">
                <thead>
                    <tr>
                        <th style="width:4%">#</th>
                        <th style="width:22%">Department</th>
                        <th style="width:13%">Status</th>
                        <th style="width:22%">Comment</th>
                        <th style="width:20%">Reviewed By</th>
                        <th style="width:14%">Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($clearance->approvals->sortBy('department.priority') as $i => $approval)
                @php
                    $st  = $approval->status ?? 'pending';
                    $cls = match($st){ 'approved'=>'pdf-approved','rejected'=>'pdf-rejected',default=>'pdf-pending' };
                @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td><strong>{{ $approval->department->name }}</strong></td>
                    <td><span class="pdf-badge {{ $cls }}">{{ ucfirst($st) }}</span></td>
                    <td style="font-size:11px;color:#374151;">{{ $approval->comments ?? '—' }}</td>
                    <td style="font-size:11px;">{{ $approval->officer?->name ?? '—' }}</td>
                    <td style="font-size:11px;">{{ $approval->reviewed_at?->format('d M Y') ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>

            {{-- Final Status --}}
            @php
                $os  = $clearance->status ?? 'pending';
                $ocls = match($os){ 'approved'=>'background:#d1fae5;color:#065f46;border:1.5px solid #059669;','rejected'=>'background:#fee2e2;color:#991b1b;border:1.5px solid #dc2626;',default=>'background:#fef9c3;color:#854d0e;border:1.5px solid #d97706;' };
            @endphp
            <div class="pdf-final">
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <div>
                        <span style="font-size:11px;font-weight:700;color:#065f46;display:block;margin-bottom:4px;">Overall Status</span>
                        <span style="font-size:13px;font-weight:800;padding:4px 14px;border-radius:5px;{{ $ocls }}">
                            {{ strtoupper($os) }}
                        </span>
                    </div>
                    <div>
                        <span style="font-size:11px;font-weight:700;color:#065f46;display:block;margin-bottom:4px;">Submitted On</span>
                        <span style="font-size:12px;font-weight:600;">{{ $clearance->submitted_at->format('d F Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="pdf-sig-wrap">
                @foreach(['Registrar','Director of Academic Affairs','System Administrator'] as $sig)
                <div class="pdf-sig">
                    <div class="pdf-sig-line">
                        <p style="font-size:11px;font-weight:700;color:#1a1a1a;margin:0;">{{ $sig }}</p>
                        <p style="font-size:9px;color:#64748b;margin:2px 0 0;">Mbeya University of Science and Technology</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Footer / QR --}}
            <div class="pdf-footer-bar">
                <div class="pdf-qr">
                    <span style="text-align:center;line-height:1.4;">QR<br>Code</span>
                </div>
                <div>
                    <p style="font-size:10px;margin:0 0 3px;"><strong>Verification Code:</strong> {{ $verCode }}</p>
                    <p style="font-size:10px;margin:0 0 3px;"><strong>System:</strong> MUST Automated Clearance System</p>
                    <p style="font-size:10px;margin:0 0 3px;"><strong>Generated:</strong> {{ now()->format('d F Y, H:i') }} EAT</p>
                    <p style="font-size:9px;color:#94a3b8;font-style:italic;margin:5px 0 0;">This is a system-generated document. For verification, present this form with your student ID at the Registry Office.</p>
                </div>
            </div>

        </div>{{-- /pdf-preview --}}
    </div>
</div>

@push('scripts')
<script>
// ── Counter animation ──
function animateCount(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    let n = 0;
    const step = Math.ceil(target / 20);
    const t = setInterval(() => {
        n = Math.min(n + step, target);
        el.textContent = n;
        if (n >= target) clearInterval(t);
    }, 40);
}
document.addEventListener('DOMContentLoaded', () => {
    animateCount('count-approved', {{ $approved }});
    animateCount('count-pending',  {{ $total - $approved - $rejected }});
    animateCount('count-rejected', {{ $rejected }});
});

// ── Modal ──
function openPreview() {
    const m = document.getElementById('previewModal');
    m.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closePreview() {
    const m = document.getElementById('previewModal');
    m.classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closePreview(); });
</script>
@endpush
@endsection
