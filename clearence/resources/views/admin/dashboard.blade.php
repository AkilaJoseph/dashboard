@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'System Overview')
@section('page-subtitle', 'MUST Clearance Management System &mdash; Administrator Control Center')

@section('content')
<style>
.a-stat{
    background:rgba(4,18,10,0.85);border-radius:16px;backdrop-filter:blur(16px);
    padding:20px;position:relative;overflow:hidden;animation:fade-in-up 0.5s ease forwards;
    transition:transform 0.3s,box-shadow 0.3s;
}
.a-stat:hover{transform:translateY(-4px);}
.a-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.s-blue{border:1px solid rgba(59,130,246,0.3);box-shadow:0 0 28px rgba(59,130,246,0.08);}
.s-blue::before{background:linear-gradient(90deg,transparent,#3b82f6,transparent);}
.s-purple{border:1px solid rgba(139,92,246,0.3);box-shadow:0 0 28px rgba(139,92,246,0.08);}
.s-purple::before{background:linear-gradient(90deg,transparent,#8b5cf6,transparent);}
.s-gold{border:1px solid rgba(245,158,11,0.3);box-shadow:0 0 28px rgba(245,158,11,0.08);}
.s-gold::before{background:linear-gradient(90deg,transparent,#f59e0b,transparent);}
.s-green{border:1px solid rgba(16,185,129,0.35);box-shadow:0 0 28px rgba(16,185,129,0.1);}
.s-green::before{background:linear-gradient(90deg,transparent,#10b981,transparent);}
.s-amber{border:1px solid rgba(245,158,11,0.25);box-shadow:0 0 24px rgba(245,158,11,0.07);}
.s-amber::before{background:linear-gradient(90deg,transparent,#f59e0b,transparent);}
.s-emerald{border:1px solid rgba(52,211,153,0.4);box-shadow:0 0 28px rgba(52,211,153,0.12);}
.s-emerald::before{background:linear-gradient(90deg,transparent,#34d399,#f59e0b,transparent);}
.s-red{border:1px solid rgba(239,68,68,0.25);box-shadow:0 0 24px rgba(239,68,68,0.07);}
.s-red::before{background:linear-gradient(90deg,transparent,#ef4444,transparent);}
.stat-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.cl-row-admin{
    display:grid;grid-template-columns:2fr 100px 100px 100px 100px;
    align-items:center;gap:10px;padding:12px 18px;border-radius:10px;
    border:1px solid rgba(16,185,129,0.1);background:rgba(16,185,129,0.02);
    transition:all 0.25s;margin-bottom:7px;
}
.cl-row-admin:hover{border-color:rgba(16,185,129,0.3);background:rgba(16,185,129,0.05);}
</style>

<!-- Primary Stats Grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:20px;">
    <div class="a-stat s-blue">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(96,165,250,0.6);">Students</p>
            <div class="stat-icon" style="background:rgba(59,130,246,0.12);">
                <svg style="width:16px;height:16px;color:#60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
            </div>
        </div>
        <p style="font-size:34px;font-weight:900;background:linear-gradient(135deg,#60a5fa,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['total_students'] }}</p>
    </div>
    <div class="a-stat s-purple">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(167,139,250,0.6);">Officers</p>
            <div class="stat-icon" style="background:rgba(139,92,246,0.12);">
                <svg style="width:16px;height:16px;color:#a78bfa;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <p style="font-size:34px;font-weight:900;background:linear-gradient(135deg,#c4b5fd,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['total_officers'] }}</p>
    </div>
    <div class="a-stat s-gold">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(251,191,36,0.6);">Departments</p>
            <div class="stat-icon" style="background:rgba(245,158,11,0.12);">
                <svg style="width:16px;height:16px;color:#fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
            </div>
        </div>
        <p style="font-size:34px;font-weight:900;background:linear-gradient(135deg,#fbbf24,#f59e0b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['total_departments'] }}</p>
    </div>
    <div class="a-stat s-green">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.6);">Clearances</p>
            <div class="stat-icon" style="background:rgba(16,185,129,0.12);">
                <svg style="width:16px;height:16px;color:#34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <p style="font-size:34px;font-weight:900;background:linear-gradient(135deg,#34d399,#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['total_clearances'] }}</p>
    </div>
</div>

<!-- Status Cards -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;">
    <div class="a-stat s-amber" style="display:flex;align-items:center;gap:14px;padding:16px 20px;">
        <div class="stat-icon" style="width:44px;height:44px;border-radius:12px;background:rgba(245,158,11,0.12);box-shadow:0 0 18px rgba(245,158,11,0.25);">
            <svg style="width:20px;height:20px;color:#fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:rgba(251,191,36,0.55);margin-bottom:4px;">Pending</p>
            <p style="font-size:26px;font-weight:900;background:linear-gradient(135deg,#fbbf24,#f59e0b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['pending_clearances'] }}</p>
        </div>
    </div>
    <div class="a-stat" style="border:1px solid rgba(59,130,246,0.25);display:flex;align-items:center;gap:14px;padding:16px 20px;">
        <div class="stat-icon" style="width:44px;height:44px;border-radius:12px;background:rgba(59,130,246,0.1);">
            <svg style="width:20px;height:20px;color:#60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>
        <div>
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:rgba(96,165,250,0.55);margin-bottom:4px;">In Progress</p>
            <p style="font-size:26px;font-weight:900;background:linear-gradient(135deg,#60a5fa,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['inprogress_clearances'] }}</p>
        </div>
    </div>
    <div class="a-stat s-emerald" style="display:flex;align-items:center;gap:14px;padding:16px 20px;">
        <div class="stat-icon" style="width:44px;height:44px;border-radius:12px;background:rgba(52,211,153,0.1);box-shadow:0 0 18px rgba(52,211,153,0.25);">
            <svg style="width:20px;height:20px;color:#34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:rgba(110,231,183,0.55);margin-bottom:4px;">Approved</p>
            <p style="font-size:26px;font-weight:900;background:linear-gradient(135deg,#6ee7b7,#34d399,#fbbf24);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['approved_clearances'] }}</p>
        </div>
    </div>
</div>

<!-- Recent Clearances -->
<div class="glow-card" style="padding:0;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid rgba(16,185,129,0.1);">
        <h3 style="font-size:14px;font-weight:800;color:#e2e8f0;">Recent Clearance Requests</h3>
        <a href="{{ route('admin.reports.index') }}" style="font-size:12px;font-weight:700;color:rgba(52,211,153,0.7);text-decoration:none;letter-spacing:0.04em;">VIEW REPORTS &rarr;</a>
    </div>

    @if($recent_clearances->isEmpty())
    <div style="text-align:center;padding:48px;">
        <p style="color:rgba(160,200,175,0.4);font-size:13px;">No clearance requests yet.</p>
    </div>
    @else
    <div style="padding:14px 20px 20px;">
        <!-- Header -->
        <div class="cl-row-admin" style="background:transparent;border:none;margin-bottom:4px;padding:0 18px 6px;">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Student</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Year</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Type</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Status</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Submitted</span>
        </div>
        @foreach($recent_clearances as $clearance)
        <div class="cl-row-admin">
            <div>
                <p style="font-size:13px;font-weight:700;color:#e2e8f0;margin-bottom:2px;">{{ $clearance->user->name }}</p>
                <p style="font-size:10px;color:rgba(160,200,175,0.4);font-family:monospace;">{{ $clearance->user->student_id }}</p>
            </div>
            <span style="font-size:12px;color:#e2e8f0;font-weight:600;">{{ $clearance->academic_year }}</span>
            <span style="font-size:10px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:rgba(160,200,175,0.6);padding:3px 10px;border-radius:999px;text-transform:capitalize;white-space:nowrap;">{{ $clearance->clearance_type }}</span>
            <div>
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
            <span style="font-size:11px;color:rgba(160,200,175,0.4);">{{ $clearance->submitted_at->format('d M Y') }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
