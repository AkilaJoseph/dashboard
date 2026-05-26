@extends('layouts.app')
@section('title', 'My Clearances')
@section('page-title', 'My Clearance Requests')
@section('page-subtitle', 'Track all your submitted clearance requests')

@section('content')
<style>
@keyframes slideUp  { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
@keyframes popIn    { from{opacity:0;transform:scale(0.9)} to{opacity:1;transform:scale(1)} }
@keyframes fillBar  { from{width:0} to{width:var(--pct)} }
@keyframes shimmer  { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
@keyframes pulse-ring { 0%{transform:scale(1);opacity:0.8} 70%{transform:scale(1.6);opacity:0} 100%{opacity:0} }

.cl-card {
    background:#fff; border:1.5px solid #e2e8f0; border-radius:14px;
    padding:0; overflow:hidden; transition:all 0.25s;
    animation: slideUp 0.4s ease both;
    box-shadow:0 2px 8px rgba(0,0,0,0.04);
}
.cl-card:hover {
    border-color:#a7f3d0; transform:translateY(-3px);
    box-shadow:0 8px 28px rgba(5,150,105,0.12);
}

.cl-card-header {
    padding:14px 18px;
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    border-bottom:1px solid #f1f5f9;
}

.type-chip {
    font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;
    padding:3px 10px;border-radius:999px;
    background:#f0fdf4;color:#065f46;border:1px solid #a7f3d0;
}

.cl-card-body { padding:14px 18px; }
.cl-card-footer {
    padding:10px 18px;background:#f8fafc;
    border-top:1px solid #f1f5f9;
    display:flex;align-items:center;justify-content:space-between;
    flex-wrap:wrap;gap:8px;
}

.prog-track {
    width:100%;background:#e2e8f0;border-radius:999px;height:6px;overflow:hidden;margin:8px 0 4px;
}
.prog-bar {
    height:6px;border-radius:999px;
    background-size:200% auto;
    animation: fillBar 1.2s cubic-bezier(.4,0,.2,1) forwards, shimmer 2s linear infinite;
}
.prog-green { background:linear-gradient(90deg,#059669,#10b981,#34d399); }
.prog-amber { background:linear-gradient(90deg,#d97706,#f59e0b,#fbbf24); }
.prog-red   { background:linear-gradient(90deg,#ef4444,#f87171); }

/* Pulse ring on pending dot */
.pulse-wrap { position:relative;display:inline-flex;align-items:center;justify-content:center; }
.pulse-ring {
    position:absolute;width:100%;height:100%;border-radius:50%;
    background:#d97706;
    animation: pulse-ring 1.4s ease-out infinite;
}

/* Stats row */
.summary-stat {
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:16px 0;flex:1;
    border-right:1px solid #f1f5f9;
    transition:background 0.15s;
}
.summary-stat:last-child { border-right:none; }
.summary-stat:hover { background:#f8fafc; }

/* Action buttons */
.act-btn {
    font-size:12px;font-weight:600;text-decoration:none;
    padding:5px 13px;border-radius:7px;
    display:inline-flex;align-items:center;gap:5px;
    transition:all 0.15s;border:1.5px solid transparent;cursor:pointer;background:none;
}
.act-view     { color:#059669;border-color:#a7f3d0;background:#f0fdf4; }
.act-view:hover     { background:#d1fae5;transform:translateY(-1px); }
.act-preview  { color:#6366f1;border-color:#c7d2fe;background:#eef2ff; }
.act-preview:hover  { background:#e0e7ff;transform:translateY(-1px); }
.act-download { color:#92400e;border-color:#fde68a;background:#fef9c3; }
.act-download:hover { background:#fde68a;transform:translateY(-1px); }
</style>

{{-- ══ Summary Bar ══ --}}
@php
    $totals = ['all'=>$clearances->count(), 'approved'=>0, 'pending'=>0, 'rejected'=>0, 'in_progress'=>0];
    foreach($clearances as $c){ $totals[$c->status] = ($totals[$c->status] ?? 0) + 1; }
@endphp
<div class="glow-card" style="padding:0;overflow:hidden;margin-bottom:22px;animation:popIn 0.35s ease both;">
    <div style="display:flex;">
        @foreach([['Total Requests',$totals['all'],'#059669'],['Approved',$totals['approved'],'#059669'],['In Progress',($totals['in_progress']??0),'#3b82f6'],['Pending',$totals['pending'],'#d97706'],['Rejected',$totals['rejected'],'#ef4444']] as [$label,$count,$color])
        <div class="summary-stat">
            <span style="font-size:24px;font-weight:800;color:{{ $color }};">{{ $count }}</span>
            <span style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;margin-top:3px;">{{ $label }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ══ New Request Button ══ --}}
<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
    <a href="{{ route('student.clearances.create') }}" class="btn-glow" style="text-decoration:none;">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
        New Clearance Request
    </a>
</div>

@if($clearances->isEmpty())
{{-- Empty state --}}
<div class="glow-card" style="text-align:center;padding:60px 24px;animation:popIn 0.4s ease both;">
    <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#f0fdf4,#d1fae5);border:2px solid #a7f3d0;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
        <svg width="26" height="26" fill="none" stroke="#059669" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    </div>
    <p style="font-size:15px;font-weight:700;color:#1e293b;margin:0 0 6px;">No clearance requests yet</p>
    <p style="font-size:13px;color:#64748b;margin:0 0 20px;">Submit your first clearance request to get started.</p>
    <a href="{{ route('student.clearances.create') }}" class="btn-glow" style="text-decoration:none;">Submit First Request</a>
</div>

@else
{{-- ══ Cards Grid ══ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:14px;">
    @foreach($clearances as $idx => $clearance)
    @php
        $app  = $clearance->approvals->where('status','approved')->count();
        $tot  = $clearance->approvals->count();
        $rej  = $clearance->approvals->where('status','rejected')->count();
        $pct  = $tot > 0 ? round(($app/$tot)*100) : 0;
        $st   = $clearance->status;
        $progClass = $pct===100 ? 'prog-green' : ($rej>0 ? 'prog-red' : 'prog-amber');
        $delay     = 0.06 * $idx;
    @endphp
    <div class="cl-card" style="animation-delay:{{ $delay }}s;">

        {{-- Card Header --}}
        <div class="cl-card-header" style="background:{{ $st==='approved' ? 'linear-gradient(135deg,#f0fdf4,#ecfdf5)' : ($st==='rejected' ? 'linear-gradient(135deg,#fef2f2,#fff1f1)' : 'linear-gradient(135deg,#f8fafc,#f1f5f9)') }};">
            <div>
                <p style="font-size:14px;font-weight:700;color:#1e293b;margin:0 0 3px;">{{ $clearance->academic_year }}</p>
                <p style="font-size:11px;color:#64748b;margin:0;">{{ $clearance->semester }} Semester &nbsp;·&nbsp; {{ $clearance->submitted_at->format('d M Y') }}</p>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="type-chip">{{ $clearance->clearance_type }}</span>
                @if($st==='approved')
                <span class="badge badge-approved">✓ Approved</span>
                @elseif($st==='rejected')
                <span class="badge badge-rejected">Rejected</span>
                @elseif($st==='in_progress')
                <span class="badge badge-progress">In Progress</span>
                @else
                <span class="badge badge-pending">
                    <span class="pulse-wrap" style="width:7px;height:7px;margin-right:5px;">
                        <span class="pulse-ring"></span>
                        <span style="width:7px;height:7px;border-radius:50%;background:#d97706;display:block;position:relative;z-index:1;"></span>
                    </span>
                    Pending
                </span>
                @endif
            </div>
        </div>

        {{-- Progress --}}
        <div class="cl-card-body">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:11px;font-weight:600;color:#475569;">Departments Cleared</span>
                <span style="font-size:12px;font-weight:700;color:#1e293b;">{{ $app }}/{{ $tot }}</span>
            </div>
            <div class="prog-track">
                <div class="prog-bar {{ $progClass }}" style="--pct:{{ $pct }}%;width:0;"></div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:10px;color:#94a3b8;">{{ $pct }}% complete</span>
                @if($rej > 0)
                <span style="font-size:10px;color:#ef4444;font-weight:600;">{{ $rej }} rejected</span>
                @endif
            </div>

            {{-- Mini dept dots --}}
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:10px;">
                @foreach($clearance->approvals->sortBy('department.priority') as $approval)
                <div title="{{ $approval->department->name }} — {{ ucfirst($approval->status) }}"
                     style="width:8px;height:8px;border-radius:50%;background:{{ $approval->status==='approved' ? '#059669' : ($approval->status==='rejected' ? '#ef4444' : '#cbd5e1') }};transition:transform 0.15s;cursor:default;"
                     onmouseover="this.style.transform='scale(1.8)'" onmouseout="this.style.transform='scale(1)'">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Footer --}}
        <div class="cl-card-footer">
            <span style="font-size:10px;color:#94a3b8;">ID #{{ str_pad($clearance->id,4,'0',STR_PAD_LEFT) }}</span>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <a href="{{ route('student.clearances.show', $clearance) }}" class="act-btn act-view">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    View
                </a>
                <a href="{{ route('student.clearances.show', $clearance) }}#preview" onclick="sessionStorage.setItem('openPreview','1')" class="act-btn act-preview">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg>
                    Preview
                </a>
                <a href="{{ route('student.clearances.certificate', $clearance) }}" class="act-btn act-download">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@push('scripts')
<script>
// Auto-open preview if coming from index preview button
if (sessionStorage.getItem('openPreview') === '1') {
    sessionStorage.removeItem('openPreview');
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof openPreview === 'function') setTimeout(openPreview, 300);
    });
}
</script>
@endpush
@endsection
