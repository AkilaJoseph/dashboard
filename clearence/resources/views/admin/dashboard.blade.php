@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'System Overview')
@section('page-subtitle', 'MUST Clearance Management System &mdash; Administrator Control Center')

@section('content')
<style>
.a-stat{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;transition:box-shadow 0.2s;}
.a-stat:hover{box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.cl-row-admin{
    display:grid;grid-template-columns:2fr 100px 100px 100px 100px;
    align-items:center;gap:10px;padding:12px 18px;border-radius:9px;
    border:1px solid #e2e8f0;background:#fff;
    transition:all 0.15s;margin-bottom:6px;
}
.cl-row-admin:hover{border-color:#a7f3d0;background:#f0fdf4;}
</style>

<!-- Primary Stats Grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:20px;">
    <div class="a-stat" style="border-top:3px solid #3b82f6;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#64748b;">Students</p>
            <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
                <svg style="width:15px;height:15px;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
            </div>
        </div>
        <p style="font-size:32px;font-weight:800;color:#3b82f6;">{{ $stats['total_students'] }}</p>
    </div>
    <div class="a-stat" style="border-top:3px solid #8b5cf6;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#64748b;">Officers</p>
            <div style="width:32px;height:32px;border-radius:8px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;">
                <svg style="width:15px;height:15px;color:#8b5cf6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <p style="font-size:32px;font-weight:800;color:#8b5cf6;">{{ $stats['total_officers'] }}</p>
    </div>
    <div class="a-stat" style="border-top:3px solid #d97706;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#64748b;">Departments</p>
            <div style="width:32px;height:32px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;">
                <svg style="width:15px;height:15px;color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
            </div>
        </div>
        <p style="font-size:32px;font-weight:800;color:#d97706;">{{ $stats['total_departments'] }}</p>
    </div>
    <div class="a-stat" style="border-top:3px solid #059669;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#64748b;">Clearances</p>
            <div style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                <svg style="width:15px;height:15px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <p style="font-size:32px;font-weight:800;color:#059669;">{{ $stats['total_clearances'] }}</p>
    </div>
</div>

<!-- Status Cards -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;">
    <div class="a-stat" style="border-top:3px solid #d97706;display:flex;align-items:center;gap:14px;padding:16px 20px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#fffbeb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg style="width:18px;height:18px;color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#64748b;margin-bottom:4px;">Pending</p>
            <p style="font-size:28px;font-weight:800;color:#d97706;">{{ $stats['pending_clearances'] }}</p>
        </div>
    </div>
    <div class="a-stat" style="border-top:3px solid #3b82f6;display:flex;align-items:center;gap:14px;padding:16px 20px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg style="width:18px;height:18px;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>
        <div>
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#64748b;margin-bottom:4px;">In Progress</p>
            <p style="font-size:28px;font-weight:800;color:#3b82f6;">{{ $stats['inprogress_clearances'] }}</p>
        </div>
    </div>
    <div class="a-stat" style="border-top:3px solid #059669;display:flex;align-items:center;gap:14px;padding:16px 20px;">
        <div style="width:40px;height:40px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg style="width:18px;height:18px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#64748b;margin-bottom:4px;">Approved</p>
            <p style="font-size:28px;font-weight:800;color:#059669;">{{ $stats['approved_clearances'] }}</p>
        </div>
    </div>
</div>

<!-- Recent Clearances -->
<div class="glow-card" style="padding:0;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 22px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
        <h3 style="font-size:14px;font-weight:700;color:#1e293b;">Recent Clearance Requests</h3>
        <a href="{{ route('admin.reports.index') }}" style="font-size:12px;font-weight:600;color:#059669;text-decoration:none;">View Reports &rarr;</a>
    </div>

    @if($recent_clearances->isEmpty())
    <div style="text-align:center;padding:48px;">
        <p style="color:#94a3b8;font-size:13px;">No clearance requests yet.</p>
    </div>
    @else
    <div style="padding:14px 20px 20px;">
        <!-- Header -->
        <div class="cl-row-admin" style="background:transparent;border:none;margin-bottom:4px;padding:0 18px 6px;">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Student</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Year</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Type</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Status</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Submitted</span>
        </div>
        @foreach($recent_clearances as $clearance)
        <div class="cl-row-admin">
            <div>
                <p style="font-size:13px;font-weight:600;color:#1e293b;margin-bottom:2px;">{{ $clearance->user->name }}</p>
                <p style="font-size:10px;color:#94a3b8;font-family:monospace;">{{ $clearance->user->student_id }}</p>
            </div>
            <span style="font-size:12px;color:#475569;font-weight:600;">{{ $clearance->academic_year }}</span>
            <span style="font-size:10px;background:#f1f5f9;border:1px solid #cbd5e1;color:#475569;padding:3px 9px;border-radius:999px;text-transform:capitalize;white-space:nowrap;">{{ $clearance->clearance_type }}</span>
            <div>
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
            <span style="font-size:11px;color:#94a3b8;">{{ $clearance->submitted_at->format('d M Y') }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
