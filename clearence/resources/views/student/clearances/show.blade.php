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
@endphp

<style>
.dept-row{
    display:flex;align-items:flex-start;gap:16px;
    padding:18px;border-radius:12px;border:1px solid;
    margin-bottom:10px;transition:all 0.3s;
}
.dept-approved{border-color:rgba(16,185,129,0.35);background:rgba(16,185,129,0.06);}
.dept-approved:hover{box-shadow:0 0 20px rgba(16,185,129,0.15);}
.dept-rejected{border-color:rgba(239,68,68,0.3);background:rgba(239,68,68,0.05);}
.dept-rejected:hover{box-shadow:0 0 20px rgba(239,68,68,0.1);}
.dept-pending{border-color:rgba(16,185,129,0.1);background:rgba(16,185,129,0.02);}
.dept-pending:hover{border-color:rgba(16,185,129,0.2);}
.dept-icon{
    width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.icon-approved{background:linear-gradient(135deg,#059669,#10b981);box-shadow:0 0 20px rgba(16,185,129,0.5);}
.icon-rejected{background:linear-gradient(135deg,#dc2626,#ef4444);}
.icon-pending{background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);}
.detail-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0;}
@media(max-width:640px){.detail-grid{grid-template-columns:repeat(2,1fr);}}
.detail-cell{padding:16px 20px;border-right:1px solid rgba(16,185,129,0.1);}
.detail-cell:last-child{border-right:none;}
</style>

<div style="max-width:860px;margin:0 auto;">

    <!-- Header Card -->
    <div class="glow-card" style="padding:0;overflow:hidden;margin-bottom:20px;">

        <div style="padding:22px 28px;background:rgba(16,185,129,0.06);border-bottom:1px solid rgba(16,185,129,0.15);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h3 style="font-size:17px;font-weight:800;color:#e2e8f0;">{{ $clearance->academic_year }} &mdash; {{ $clearance->semester }} Semester</h3>
                <p style="font-size:12px;color:rgba(52,211,153,0.6);text-transform:capitalize;margin-top:4px;">{{ $clearance->clearance_type }} Clearance</p>
            </div>
            @if($clearance->status === 'approved')
            <a href="{{ route('student.clearances.certificate', $clearance) }}"
               style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#d97706,#f59e0b,#fbbf24);color:#020904;font-weight:800;font-size:13px;padding:10px 20px;border-radius:10px;text-decoration:none;box-shadow:0 0 25px rgba(245,158,11,0.4);letter-spacing:0.03em;">
                <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download Certificate
            </a>
            @endif
        </div>

        <!-- Progress Bar -->
        <div style="padding:20px 28px;border-bottom:1px solid rgba(16,185,129,0.1);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:12px;font-weight:600;color:rgba(160,200,175,0.7);">Overall Progress</span>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:13px;font-weight:700;color:#e2e8f0;font-family:monospace;">{{ $approved }}/{{ $total }} Departments</span>
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
            </div>
            <div style="width:100%;background:rgba(16,185,129,0.08);border-radius:999px;height:8px;overflow:hidden;">
                <div class="{{ $pct===100 ? 'glow-progress' : '' }}"
                     style="height:8px;border-radius:999px;width:{{ $pct }}%;background:{{ $pct===100 ? 'linear-gradient(90deg,#059669,#10b981,#34d399)' : 'linear-gradient(90deg,#f59e0b,#fbbf24)' }};transition:width 1.5s ease;box-shadow:{{ $pct===100 ? '0 0 12px rgba(16,185,129,0.6)' : '0 0 10px rgba(245,158,11,0.4)' }};"></div>
            </div>
            <p style="font-size:11px;color:rgba(160,200,175,0.35);margin-top:6px;text-align:right;">{{ $pct }}% complete</p>
        </div>

        <!-- Details Grid -->
        <div class="detail-grid" style="border-top:1px solid rgba(16,185,129,0.08);">
            <div class="detail-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(52,211,153,0.5);margin-bottom:6px;">Student</p>
                <p style="font-size:13px;font-weight:700;color:#e2e8f0;">{{ $user->name }}</p>
            </div>
            <div class="detail-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(52,211,153,0.5);margin-bottom:6px;">UE / Student ID</p>
                <p style="font-size:13px;font-weight:700;color:#fbbf24;font-family:monospace;">{{ $user->student_id ?? 'N/A' }}</p>
            </div>
            <div class="detail-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(52,211,153,0.5);margin-bottom:6px;">Submitted</p>
                <p style="font-size:12px;font-weight:600;color:#e2e8f0;">{{ $clearance->submitted_at->format('d M Y') }}</p>
                <p style="font-size:10px;color:rgba(160,200,175,0.4);">{{ $clearance->submitted_at->format('h:i A') }}</p>
            </div>
            <div class="detail-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(52,211,153,0.5);margin-bottom:6px;">Completed</p>
                <p style="font-size:12px;font-weight:600;color:#e2e8f0;">{{ $clearance->completed_at ? $clearance->completed_at->format('d M Y') : '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Department Approvals -->
    <div class="glow-card" style="margin-bottom:20px;">
        <h3 style="font-size:14px;font-weight:800;color:#e2e8f0;margin-bottom:20px;letter-spacing:0.03em;">
            <span style="color:#34d399;">&#9670;</span> Departmental Clearance Status
        </h3>

        @foreach($clearance->approvals->sortBy('department.priority') as $i => $approval)
        <div class="dept-row {{ $approval->status==='approved' ? 'dept-approved' : ($approval->status==='rejected' ? 'dept-rejected' : 'dept-pending') }}">

            <!-- Icon -->
            <div class="dept-icon {{ $approval->status==='approved' ? 'icon-approved' : ($approval->status==='rejected' ? 'icon-rejected' : 'icon-pending') }}">
                @if($approval->status==='approved')
                <svg style="width:18px;height:18px;color:#020904;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                @elseif($approval->status==='rejected')
                <svg style="width:18px;height:18px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                @else
                <span style="font-size:12px;font-weight:900;color:rgba(52,211,153,0.6);">{{ $i + 1 }}</span>
                @endif
            </div>

            <!-- Info -->
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:6px;">
                    <p style="font-size:13px;font-weight:700;color:#e2e8f0;">{{ $approval->department->name }}</p>
                    @if($approval->status==='approved')
                    <span class="badge-approved">Approved</span>
                    @elseif($approval->status==='rejected')
                    <span class="badge-rejected">Rejected</span>
                    @else
                    <span class="badge-pending">Pending</span>
                    @endif
                </div>

                @if($approval->officer)
                <p style="font-size:11px;color:rgba(160,200,175,0.5);">Reviewed by: <span style="color:rgba(52,211,153,0.7);font-weight:600;">{{ $approval->officer->name }}</span></p>
                @endif

                @if($approval->reviewed_at)
                <p style="font-size:10px;color:rgba(160,200,175,0.35);margin-top:2px;">{{ $approval->reviewed_at->format('d M Y, h:i A') }}</p>
                @endif

                @if($approval->comments)
                <div style="margin-top:8px;padding:8px 12px;background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.12);border-radius:8px;font-size:11px;color:rgba(160,200,175,0.7);font-style:italic;">
                    "{{ $approval->comments }}"
                </div>
                @endif

                @if($approval->status === 'pending')
                <p style="font-size:11px;color:rgba(245,158,11,0.6);margin-top:6px;">&#9679; Awaiting review from this department...</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($clearance->reason)
    <div class="glow-card" style="margin-bottom:20px;">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.5);margin-bottom:10px;">Student's Notes</p>
        <p style="font-size:13px;color:rgba(160,200,175,0.7);line-height:1.6;">{{ $clearance->reason }}</p>
    </div>
    @endif

    <a href="{{ route('student.clearances.index') }}" style="font-size:12px;color:rgba(160,200,175,0.5);text-decoration:none;">&larr; Back to My Clearances</a>
</div>
@endsection
