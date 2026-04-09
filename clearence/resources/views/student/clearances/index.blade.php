@extends('layouts.app')

@section('title', 'My Clearances')
@section('page-title', 'My Clearance Requests')
@section('page-subtitle', 'View and track all your submitted clearance requests')

@section('content')
<style>
.cl-row{
    display:grid;grid-template-columns:1fr 90px 120px 90px 90px;
    align-items:center;gap:12px;
    padding:13px 18px;border-radius:10px;
    border:1px solid #e2e8f0;background:#fff;
    transition:all 0.2s;margin-bottom:6px;
}
.cl-row:hover{border-color:#a7f3d0;background:#f0fdf4;}
@media(max-width:640px){
    .cl-row{grid-template-columns:1fr 80px;grid-template-rows:auto auto;}
    .cl-row .cl-hide{display:none;}
}
</style>

<div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:20px;">
    <a href="{{ route('student.clearances.create') }}" class="btn-glow" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        New Clearance Request
    </a>
</div>

<div class="glow-card" style="padding:0;overflow:hidden;">

    @if($clearances->isEmpty())
    <div style="text-align:center;padding:64px 24px;">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <svg style="width:28px;height:28px;" fill="none" stroke="rgba(16,185,129,0.5)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <p style="color:rgba(160,200,175,0.5);font-size:14px;margin-bottom:16px;">No clearance requests found.</p>
        <a href="{{ route('student.clearances.create') }}" style="color:#34d399;font-size:12px;font-weight:700;text-decoration:none;letter-spacing:0.05em;">SUBMIT YOUR FIRST REQUEST &rarr;</a>
    </div>

    @else

    <!-- Table Header -->
    <div style="padding:12px 20px 10px;border-bottom:1px solid rgba(16,185,129,0.1);">
        <div class="cl-row" style="background:transparent;border:none;margin:0;padding:0 0 4px;">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;">Academic Year / Semester</span>
            <span class="cl-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;">Type</span>
            <span class="cl-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;">Progress</span>
            <span class="cl-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;">Status</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;">Action</span>
        </div>
    </div>

    <div style="padding:12px 20px 20px;">
        @foreach($clearances as $i => $clearance)
        @php
            $approved = $clearance->approvals->where('status','approved')->count();
            $total    = $clearance->approvals->count();
            $pct      = $total > 0 ? round(($approved/$total)*100) : 0;
        @endphp
        <div class="cl-row">
            <!-- Year/Semester -->
            <div>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px;">
                    <span style="font-size:13px;font-weight:700;color:#e2e8f0;">{{ $clearance->academic_year }}</span>
                    <span style="font-size:10px;color:rgba(160,200,175,0.4);">&mdash;</span>
                    <span style="font-size:12px;color:rgba(160,200,175,0.6);">{{ $clearance->semester }} Sem</span>
                </div>
                <p style="font-size:10px;color:rgba(160,200,175,0.35);font-family:monospace;">{{ $clearance->submitted_at->format('d M Y') }}</p>
            </div>

            <!-- Type -->
            <div class="cl-hide">
                <span style="font-size:10px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:rgba(160,200,175,0.6);padding:3px 10px;border-radius:999px;text-transform:capitalize;white-space:nowrap;">{{ $clearance->clearance_type }}</span>
            </div>

            <!-- Progress -->
            <div class="cl-hide">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;background:rgba(16,185,129,0.08);border-radius:999px;height:4px;overflow:hidden;">
                        <div style="height:4px;border-radius:999px;width:{{ $pct }}%;background:{{ $pct===100 ? 'linear-gradient(90deg,#10b981,#34d399)' : 'linear-gradient(90deg,#f59e0b,#fbbf24)' }};transition:width 1s ease;"></div>
                    </div>
                    <span style="font-size:10px;color:rgba(160,200,175,0.45);font-family:monospace;white-space:nowrap;">{{ $approved }}/{{ $total }}</span>
                </div>
            </div>

            <!-- Status -->
            <div class="cl-hide">
                @if($clearance->status==='approved')
                <span class="badge-approved">Approved</span>
                @elseif($clearance->status==='rejected')
                <span class="badge-rejected">Rejected</span>
                @elseif($clearance->status==='in_progress')
                <span class="badge-progress">In Progress</span>
                @else
                <span class="badge-pending">Pending</span>
                @endif
            </div>

            <!-- Actions -->
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('student.clearances.show', $clearance) }}"
                   style="font-size:11px;font-weight:700;color:#34d399;text-decoration:none;letter-spacing:0.04em;border:1px solid rgba(16,185,129,0.3);padding:4px 12px;border-radius:6px;transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(16,185,129,0.1)'" onmouseout="this.style.background='transparent'">VIEW</a>
                @if($clearance->status === 'approved')
                <a href="{{ route('student.clearances.certificate', $clearance) }}"
                   style="font-size:11px;font-weight:700;color:#fbbf24;text-decoration:none;letter-spacing:0.04em;border:1px solid rgba(245,158,11,0.3);padding:4px 10px;border-radius:6px;transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(245,158,11,0.1)'" onmouseout="this.style.background='transparent'">CERT</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
