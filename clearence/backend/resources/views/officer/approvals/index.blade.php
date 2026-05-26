@extends('layouts.app')

@section('title', 'Clearance Approvals')
@section('page-title', 'Clearance Approval Requests')
@section('page-subtitle', 'Review and process student clearance requests for your department')

@section('content')
<style>
.filter-tab{
    padding:7px 16px;border-radius:7px;font-size:12px;font-weight:600;
    text-decoration:none;transition:all 0.15s;border:1px solid transparent;
}
.filter-active{background:#059669;color:#fff;box-shadow:0 2px 6px rgba(5,150,105,0.25);}
.filter-inactive{color:#64748b;border-color:#e2e8f0;background:#fff;}
.filter-inactive:hover{border-color:#a7f3d0;color:#059669;background:#f0fdf4;}
.appr-row{
    display:grid;grid-template-columns:2fr 1.2fr 90px 90px 80px 70px;
    align-items:center;gap:12px;
    padding:12px 18px;border-radius:9px;
    border:1px solid #e2e8f0;background:#fff;
    transition:all 0.15s;margin-bottom:6px;
}
.appr-row:hover{border-color:#a7f3d0;background:#f0fdf4;}
@media(max-width:768px){
    .appr-row{grid-template-columns:1fr 80px;}
    .appr-hide{display:none;}
}
</style>

<!-- Filter Tabs -->
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px;">
    @foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $val=>$label)
    <a href="{{ route('officer.approvals.index', ['status'=>$val]) }}"
       class="filter-tab {{ (request('status','all')===$val) ? 'filter-active' : 'filter-inactive' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="glow-card" style="padding:0;overflow:hidden;">
    @if($approvals->isEmpty())
    <div style="text-align:center;padding:60px 24px;">
        <div style="width:52px;height:52px;border-radius:50%;background:#f0fdf4;border:1px solid #a7f3d0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg style="width:22px;height:22px;" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <p style="color:#64748b;font-size:13px;">No approval requests found.</p>
    </div>
    @else

    <!-- Header -->
    <div style="background:#f8fafc;border-bottom:2px solid #e2e8f0;padding:10px 18px;">
        <div class="appr-row" style="background:transparent;border:none;margin:0;padding:0;">
            <span style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Student</span>
            <span class="appr-hide" style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Programme</span>
            <span class="appr-hide" style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Year</span>
            <span class="appr-hide" style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Type</span>
            <span class="appr-hide" style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Status</span>
            <span style="font-size:10.5px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#94a3b8;">Action</span>
        </div>
    </div>

    <div style="padding:10px 18px 18px;">
        @foreach($approvals as $approval)
        <div class="appr-row">
            <div style="display:flex;align-items:center;gap:11px;">
                <div style="width:34px;height:34px;border-radius:8px;background:#059669;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;">
                    {{ strtoupper(substr($approval->clearance->user->name, 0, 1)) }}
                </div>
                <div>
                    <p style="font-size:13px;font-weight:600;color:#1e293b;margin-bottom:2px;">{{ $approval->clearance->user->name }}</p>
                    <p style="font-size:11px;color:#94a3b8;font-family:monospace;">{{ $approval->clearance->user->student_id }}</p>
                </div>
            </div>
            <div class="appr-hide" style="font-size:12px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $approval->clearance->user->programme ?? '—' }}
            </div>
            <div class="appr-hide">
                <span style="font-size:12px;font-weight:600;color:#1e293b;">{{ $approval->clearance->academic_year }}</span>
            </div>
            <div class="appr-hide">
                <span style="font-size:11px;background:#f1f5f9;border:1px solid #cbd5e1;color:#475569;padding:3px 9px;border-radius:999px;text-transform:capitalize;white-space:nowrap;">{{ $approval->clearance->clearance_type }}</span>
            </div>
            <div class="appr-hide">
                @if($approval->status==='approved')
                <span class="badge badge-approved">Approved</span>
                @elseif($approval->status==='rejected')
                <span class="badge badge-rejected">Rejected</span>
                @else
                <span class="badge badge-pending">Pending</span>
                @endif
            </div>
            <div>
                <a href="{{ route('officer.approvals.show', $approval) }}"
                   style="font-size:12px;font-weight:600;color:#059669;text-decoration:none;border:1px solid #a7f3d0;background:#f0fdf4;padding:5px 12px;border-radius:6px;transition:all 0.15s;white-space:nowrap;"
                   onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#f0fdf4'">
                    {{ $approval->status === 'pending' ? 'Review' : 'View' }}
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div style="padding:10px 18px;border-top:1px solid #f1f5f9;">
        {{ $approvals->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
