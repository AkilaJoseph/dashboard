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
    display:flex;align-items:flex-start;gap:14px;
    padding:16px;border-radius:10px;border:1px solid;margin-bottom:8px;transition:all 0.2s;
}
.dept-approved{border-color:#a7f3d0;background:#f0fdf4;}
.dept-rejected{border-color:#fecaca;background:#fef2f2;}
.dept-pending{border-color:#e2e8f0;background:#f8fafc;}
.dept-icon{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.icon-approved{background:#059669;}
.icon-rejected{background:#ef4444;}
.icon-pending{background:#e2e8f0;border:1px solid #cbd5e1;}
.detail-grid{display:grid;grid-template-columns:repeat(4,1fr);}
@media(max-width:640px){.detail-grid{grid-template-columns:repeat(2,1fr);}}
.detail-cell{padding:14px 18px;border-right:1px solid #f1f5f9;}
.detail-cell:last-child{border-right:none;}
</style>

<div style="max-width:860px;margin:0 auto;">

    <!-- Header Card -->
    <div class="glow-card" style="padding:0;overflow:hidden;margin-bottom:18px;">
        <div style="padding:18px 24px;background:linear-gradient(135deg,#064e3b,#059669);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h3 style="font-size:16px;font-weight:700;color:#fff;">{{ $clearance->academic_year }} &mdash; {{ $clearance->semester }} Semester</h3>
                <p style="font-size:12px;color:rgba(209,250,229,0.8);text-transform:capitalize;margin-top:3px;">{{ $clearance->clearance_type }} Clearance</p>
            </div>
            @if($clearance->status === 'approved')
            <a href="{{ route('student.clearances.certificate', $clearance) }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#fbbf24;color:#1e293b;font-weight:700;font-size:13px;padding:9px 18px;border-radius:8px;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download Certificate
            </a>
            @endif
        </div>

        <!-- Progress -->
        <div style="padding:18px 24px;border-bottom:1px solid #f1f5f9;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:13px;font-weight:600;color:#475569;">Overall Progress</span>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:13px;font-weight:700;color:#1e293b;">{{ $approved }}/{{ $total }} Departments</span>
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
            </div>
            <div style="width:100%;background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden;">
                <div style="height:8px;border-radius:999px;width:{{ $pct }}%;background:{{ $pct===100 ? '#059669' : '#d97706' }};transition:width 1.2s ease;"></div>
            </div>
            <p style="font-size:11px;color:#94a3b8;margin-top:5px;text-align:right;">{{ $pct }}% complete</p>
        </div>

        <!-- Details -->
        <div class="detail-grid">
            <div class="detail-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin-bottom:5px;">Student</p>
                <p style="font-size:13px;font-weight:600;color:#1e293b;">{{ $user->name }}</p>
            </div>
            <div class="detail-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin-bottom:5px;">UE / Student ID</p>
                <p style="font-size:13px;font-weight:700;color:#d97706;font-family:monospace;">{{ $user->student_id ?? 'N/A' }}</p>
            </div>
            <div class="detail-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin-bottom:5px;">Submitted</p>
                <p style="font-size:12px;font-weight:600;color:#1e293b;">{{ $clearance->submitted_at->format('d M Y') }}</p>
                <p style="font-size:10px;color:#94a3b8;">{{ $clearance->submitted_at->format('h:i A') }}</p>
            </div>
            <div class="detail-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin-bottom:5px;">Completed</p>
                <p style="font-size:12px;font-weight:600;color:#1e293b;">{{ $clearance->completed_at ? $clearance->completed_at->format('d M Y') : '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Department Approvals -->
    <div class="glow-card" style="margin-bottom:18px;">
        <h3 style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">
            Departmental Clearance Status
        </h3>

        @foreach($clearance->approvals->sortBy('department.priority') as $i => $approval)
        <div class="dept-row {{ $approval->status==='approved' ? 'dept-approved' : ($approval->status==='rejected' ? 'dept-rejected' : 'dept-pending') }}">
            <div class="dept-icon {{ $approval->status==='approved' ? 'icon-approved' : ($approval->status==='rejected' ? 'icon-rejected' : 'icon-pending') }}">
                @if($approval->status==='approved')
                <svg style="width:16px;height:16px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                @elseif($approval->status==='rejected')
                <svg style="width:16px;height:16px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                @else
                <span style="font-size:11px;font-weight:700;color:#64748b;">{{ $i+1 }}</span>
                @endif
            </div>

            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:4px;">
                    <p style="font-size:13px;font-weight:600;color:#1e293b;">{{ $approval->department->name }}</p>
                    @if($approval->status==='approved')
                    <span class="badge badge-approved">Approved</span>
                    @elseif($approval->status==='rejected')
                    <span class="badge badge-rejected">Rejected</span>
                    @else
                    <span class="badge badge-pending">Pending</span>
                    @endif
                </div>
                @if($approval->officer)
                <p style="font-size:11px;color:#64748b;">Reviewed by: <span style="font-weight:600;color:#374151;">{{ $approval->officer->name }}</span></p>
                @endif
                @if($approval->reviewed_at)
                <p style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $approval->reviewed_at->format('d M Y, h:i A') }}</p>
                @endif
                @if($approval->comments)
                <div style="margin-top:8px;padding:8px 12px;background:#fff;border:1px solid #e2e8f0;border-radius:7px;font-size:11px;color:#475569;font-style:italic;">
                    "{{ $approval->comments }}"
                </div>
                @endif
                @if($approval->status === 'pending')
                <p style="font-size:11px;color:#d97706;margin-top:5px;">Awaiting review from this department...</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($clearance->reason)
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin-bottom:8px;">Student's Notes</p>
        <p style="font-size:13px;color:#475569;line-height:1.6;">{{ $clearance->reason }}</p>
    </div>
    @endif

    <a href="{{ route('student.clearances.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Back to My Clearances</a>
</div>
@endsection
