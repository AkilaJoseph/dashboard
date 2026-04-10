@extends('layouts.app')

@section('title', 'Officer Dashboard')
@section('page-title', 'Officer Dashboard')
@section('page-subtitle', 'Manage clearance approval requests for your department')

@section('content')
@php $dept = auth()->user()->department; @endphp

<!-- Department Header -->
<div class="glow-card" style="margin-bottom:20px;border-left:4px solid #059669;">
    <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:50px;height:50px;border-radius:10px;background:#059669;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0;">
            {{ strtoupper(substr($dept->code ?? $dept->name, 0, 2)) }}
        </div>
        <div>
            <h2 style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:3px;">{{ $dept->name }}</h2>
            <p style="font-size:12px;color:#64748b;">{{ $dept->description }}</p>
            <p style="font-size:11px;color:#94a3b8;margin-top:3px;">Officer: <span style="font-weight:600;color:#059669;">{{ auth()->user()->name }}</span></p>
        </div>
    </div>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:22px;">
    <div class="stat-card" style="border-top:3px solid #059669;">
        <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;margin-bottom:8px;">Total</p>
        <p style="font-size:32px;font-weight:800;color:#059669;">{{ $stats['total'] }}</p>
    </div>
    <div class="stat-card" style="border-top:3px solid #d97706;">
        <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;margin-bottom:8px;">Pending</p>
        <p style="font-size:32px;font-weight:800;color:#d97706;">{{ $stats['pending'] }}</p>
    </div>
    <div class="stat-card" style="border-top:3px solid #10b981;">
        <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;margin-bottom:8px;">Approved</p>
        <p style="font-size:32px;font-weight:800;color:#10b981;">{{ $stats['approved'] }}</p>
    </div>
    <div class="stat-card" style="border-top:3px solid #ef4444;">
        <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;margin-bottom:8px;">Rejected</p>
        <p style="font-size:32px;font-weight:800;color:#ef4444;">{{ $stats['rejected'] }}</p>
    </div>
</div>

<!-- Recent Requests -->
<div class="glow-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">
        <h3 style="font-size:14px;font-weight:700;color:#1e293b;">Recent Requests</h3>
        <a href="{{ route('officer.approvals.index') }}" style="font-size:12px;color:#059669;text-decoration:none;font-weight:600;">View all &rarr;</a>
    </div>

    @if($approvals->isEmpty())
    <div style="text-align:center;padding:40px 0;">
        <div style="width:48px;height:48px;border-radius:50%;background:#f0fdf4;border:1px solid #a7f3d0;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <svg style="width:20px;height:20px;" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <p style="color:#64748b;font-size:13px;">No approval requests for your department yet.</p>
    </div>
    @else
    @foreach($approvals as $approval)
    <a href="{{ route('officer.approvals.show', $approval) }}" style="text-decoration:none;display:block;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:9px;border:1px solid #e2e8f0;background:#fff;margin-bottom:6px;transition:all 0.15s;"
             onmouseover="this.style.borderColor='#a7f3d0';this.style.background='#f0fdf4'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff'">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:34px;height:34px;border-radius:8px;background:#059669;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;">
                    {{ strtoupper(substr($approval->clearance->user->name, 0, 1)) }}
                </div>
                <div>
                    <p style="font-size:13px;font-weight:600;color:#1e293b;margin-bottom:2px;">{{ $approval->clearance->user->name }}</p>
                    <p style="font-size:11px;color:#94a3b8;">{{ $approval->clearance->user->student_id }} &middot; {{ $approval->clearance->academic_year }} &middot; <span style="text-transform:capitalize;">{{ $approval->clearance->clearance_type }}</span></p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                @if($approval->status==='approved')
                <span class="badge badge-approved">Approved</span>
                @elseif($approval->status==='rejected')
                <span class="badge badge-rejected">Rejected</span>
                @else
                <span class="badge badge-pending">Pending</span>
                @endif
                <svg style="width:14px;height:14px;color:#cbd5e1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
    </a>
    @endforeach
    @endif
</div>
@endsection
