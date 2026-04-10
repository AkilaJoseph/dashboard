@extends('layouts.app')

@section('title', 'My Clearances')
@section('page-title', 'My Clearance Requests')
@section('page-subtitle', 'View and track all your submitted clearance requests')

@section('content')
<style>
.cl-row{
    display:grid;grid-template-columns:1fr 90px 130px 90px 100px;
    align-items:center;gap:12px;
    padding:13px 18px;border-radius:9px;
    border:1px solid #e2e8f0;background:#fff;
    transition:all 0.2s;margin-bottom:6px;
}
.cl-row:hover{border-color:#a7f3d0;background:#f0fdf4;}
@media(max-width:640px){
    .cl-row{grid-template-columns:1fr 90px;}
    .cl-hide{display:none;}
}
</style>

<div style="display:flex;justify-content:flex-end;margin-bottom:18px;">
    <a href="{{ route('student.clearances.create') }}" class="btn-glow" style="text-decoration:none;display:inline-flex;align-items:center;gap:7px;">
        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        New Clearance Request
    </a>
</div>

<div class="glow-card" style="padding:0;overflow:hidden;">

    @if($clearances->isEmpty())
    <div style="text-align:center;padding:60px 24px;">
        <div style="width:56px;height:56px;border-radius:50%;background:#f0fdf4;border:1px solid #a7f3d0;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg style="width:24px;height:24px;" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <p style="color:#64748b;font-size:13px;margin-bottom:14px;">No clearance requests found.</p>
        <a href="{{ route('student.clearances.create') }}" style="color:#059669;font-size:13px;font-weight:600;text-decoration:none;">Submit your first request &rarr;</a>
    </div>

    @else

    <!-- Header -->
    <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 18px;">
        <div class="cl-row" style="background:transparent;border:none;margin:0;padding:0;">
            <span style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Academic Year / Semester</span>
            <span class="cl-hide" style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Type</span>
            <span class="cl-hide" style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Progress</span>
            <span class="cl-hide" style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Status</span>
            <span style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Action</span>
        </div>
    </div>

    <div style="padding:10px 18px 18px;">
        @foreach($clearances as $clearance)
        @php
            $approved = $clearance->approvals->where('status','approved')->count();
            $total    = $clearance->approvals->count();
            $pct      = $total > 0 ? round(($approved/$total)*100) : 0;
        @endphp
        <div class="cl-row">
            <div>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px;">
                    <span style="font-size:13px;font-weight:600;color:#1e293b;">{{ $clearance->academic_year }}</span>
                    <span style="font-size:11px;color:#94a3b8;">&mdash;</span>
                    <span style="font-size:12px;color:#475569;">{{ $clearance->semester }} Sem</span>
                </div>
                <p style="font-size:11px;color:#94a3b8;">{{ $clearance->submitted_at->format('d M Y') }}</p>
            </div>

            <div class="cl-hide">
                <span style="font-size:11px;background:#f1f5f9;border:1px solid #cbd5e1;color:#475569;padding:3px 10px;border-radius:999px;text-transform:capitalize;white-space:nowrap;">{{ $clearance->clearance_type }}</span>
            </div>

            <div class="cl-hide">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;background:#e2e8f0;border-radius:999px;height:5px;overflow:hidden;">
                        <div style="height:5px;border-radius:999px;width:{{ $pct }}%;background:{{ $pct===100 ? '#059669' : '#d97706' }};transition:width 1s ease;"></div>
                    </div>
                    <span style="font-size:10px;color:#94a3b8;white-space:nowrap;">{{ $approved }}/{{ $total }}</span>
                </div>
            </div>

            <div class="cl-hide">
                @if($clearance->status==='approved')
                <span class="badge badge-approved">Approved</span>
                @elseif($clearance->status==='rejected')
                <span class="badge badge-rejected">Rejected</span>
                @elseif($clearance->status==='in_progress')
                <span class="badge badge-progress">In Progress</span>
                @else
                <span class="badge badge-pending">Pending</span>
                @endif
            </div>

            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <a href="{{ route('student.clearances.show', $clearance) }}"
                   style="font-size:12px;font-weight:600;color:#059669;text-decoration:none;border:1px solid #a7f3d0;background:#f0fdf4;padding:4px 12px;border-radius:6px;transition:all 0.15s;"
                   onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#f0fdf4'">View</a>
                @if($clearance->status === 'approved')
                <a href="{{ route('student.clearances.certificate', $clearance) }}"
                   style="font-size:12px;font-weight:600;color:#92400e;text-decoration:none;border:1px solid #fde68a;background:#fef9c3;padding:4px 10px;border-radius:6px;transition:all 0.15s;"
                   onmouseover="this.style.background='#fde68a'" onmouseout="this.style.background='#fef9c3'">Cert</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
