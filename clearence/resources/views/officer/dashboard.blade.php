@extends('layouts.app')

@section('title', 'Officer Dashboard')
@section('page-title', 'Officer Dashboard')
@section('page-subtitle', 'Manage clearance approval requests for your department')

@section('content')
@php $dept = auth()->user()->department; @endphp

<style>
.dept-header{
    background:rgba(4,18,10,0.85);border:1px solid rgba(16,185,129,0.35);
    border-radius:16px;backdrop-filter:blur(20px);padding:22px 28px;margin-bottom:24px;
    position:relative;overflow:hidden;animation:fade-in-up 0.4s ease forwards;
}
.dept-header::before{content:'';position:absolute;top:0;left:5%;right:5%;height:1px;background:linear-gradient(90deg,transparent,#34d399,#f59e0b,transparent);}
.dept-badge{
    width:52px;height:52px;border-radius:12px;flex-shrink:0;
    background:linear-gradient(135deg,#059669,#10b981,#34d399);
    display:flex;align-items:center;justify-content:center;
    font-size:14px;font-weight:900;color:#020904;
    box-shadow:0 0 30px rgba(16,185,129,0.5);
}
.req-row{
    display:flex;align-items:center;justify-content:space-between;
    padding:14px 18px;border-radius:12px;
    border:1px solid rgba(16,185,129,0.12);background:rgba(16,185,129,0.02);
    transition:all 0.3s;margin-bottom:8px;text-decoration:none;
}
.req-row:hover{border-color:rgba(16,185,129,0.35);background:rgba(16,185,129,0.06);box-shadow:0 0 20px rgba(16,185,129,0.1);transform:translateX(4px);}
.o-stat{background:rgba(4,18,10,0.85);border-radius:14px;backdrop-filter:blur(16px);padding:18px 20px;position:relative;overflow:hidden;animation:fade-in-up 0.5s ease forwards;}
.o-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
</style>

<!-- Department Header -->
<div class="dept-header">
    <div style="display:flex;align-items:center;gap:18px;">
        <div class="dept-badge">{{ strtoupper(substr($dept->code ?? $dept->name, 0, 2)) }}</div>
        <div>
            <h2 style="font-size:17px;font-weight:800;color:#e2e8f0;margin-bottom:4px;">{{ $dept->name }}</h2>
            <p style="font-size:12px;color:rgba(52,211,153,0.6);margin-bottom:4px;">{{ $dept->description }}</p>
            <p style="font-size:11px;color:rgba(160,200,175,0.4);">Officer: <span style="color:rgba(52,211,153,0.7);font-weight:600;">{{ auth()->user()->name }}</span></p>
        </div>
    </div>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px;">
    <div class="o-stat" style="border:1px solid rgba(16,185,129,0.25);box-shadow:0 0 25px rgba(16,185,129,0.08);">
        <div class="o-stat::before" style="background:linear-gradient(90deg,transparent,#10b981,transparent);"></div>
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.55);margin-bottom:8px;">Total</p>
        <p style="font-size:34px;font-weight:900;background:linear-gradient(135deg,#34d399,#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['total'] }}</p>
    </div>
    <div class="o-stat" style="border:1px solid rgba(245,158,11,0.25);box-shadow:0 0 25px rgba(245,158,11,0.07);">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(251,191,36,0.55);margin-bottom:8px;">Pending</p>
        <p style="font-size:34px;font-weight:900;background:linear-gradient(135deg,#fbbf24,#f59e0b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['pending'] }}</p>
    </div>
    <div class="o-stat" style="border:1px solid rgba(52,211,153,0.35);box-shadow:0 0 25px rgba(52,211,153,0.1);">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(110,231,183,0.55);margin-bottom:8px;">Approved</p>
        <p style="font-size:34px;font-weight:900;background:linear-gradient(135deg,#6ee7b7,#34d399,#fbbf24);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['approved'] }}</p>
    </div>
    <div class="o-stat" style="border:1px solid rgba(239,68,68,0.25);box-shadow:0 0 25px rgba(239,68,68,0.07);">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(248,113,113,0.55);margin-bottom:8px;">Rejected</p>
        <p style="font-size:34px;font-weight:900;background:linear-gradient(135deg,#fca5a5,#ef4444);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['rejected'] }}</p>
    </div>
</div>

<!-- Recent Requests -->
<div class="glow-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h3 style="font-size:15px;font-weight:700;color:#e2e8f0;">Recent Requests</h3>
        <a href="{{ route('officer.approvals.index') }}" style="font-size:12px;color:rgba(52,211,153,0.8);text-decoration:none;font-weight:700;letter-spacing:0.04em;">VIEW ALL &rarr;</a>
    </div>

    @if($approvals->isEmpty())
    <div style="text-align:center;padding:48px 0;">
        <div style="width:52px;height:52px;border-radius:50%;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg style="width:22px;height:22px;" fill="none" stroke="rgba(16,185,129,0.5)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <p style="color:rgba(160,200,175,0.45);font-size:13px;">No approval requests for your department yet.</p>
    </div>
    @else
    @foreach($approvals as $approval)
    <a href="{{ route('officer.approvals.show', $approval) }}" class="req-row">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#020904;flex-shrink:0;">
                {{ strtoupper(substr($approval->clearance->user->name, 0, 1)) }}
            </div>
            <div>
                <p style="font-size:13px;font-weight:700;color:#e2e8f0;margin-bottom:3px;">{{ $approval->clearance->user->name }}</p>
                <p style="font-size:10px;color:rgba(160,200,175,0.45);font-family:monospace;">{{ $approval->clearance->user->student_id }} &middot; {{ $approval->clearance->academic_year }} &middot; <span style="text-transform:capitalize;">{{ $approval->clearance->clearance_type }}</span></p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            @if($approval->status==='approved')
            <span class="badge-approved">Approved</span>
            @elseif($approval->status==='rejected')
            <span class="badge-rejected">Rejected</span>
            @else
            <span class="badge-pending">Pending</span>
            @endif
            <svg style="width:16px;height:16px;color:rgba(16,185,129,0.4);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </a>
    @endforeach
    @endif
</div>
@endsection
