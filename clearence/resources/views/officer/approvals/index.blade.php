@extends('layouts.app')

@section('title', 'Clearance Approvals')
@section('page-title', 'Clearance Approval Requests')
@section('page-subtitle', 'Review and process student clearance requests for your department')

@section('content')
<style>
.filter-tab{
    padding:8px 18px;border-radius:8px;font-size:12px;font-weight:700;letter-spacing:0.05em;
    text-transform:uppercase;text-decoration:none;transition:all 0.25s;border:1px solid transparent;
}
.filter-active{
    background:linear-gradient(135deg,#059669,#10b981,#34d399);color:#020904;
    box-shadow:0 0 20px rgba(16,185,129,0.4);
}
.filter-inactive{color:rgba(160,200,175,0.55);border-color:rgba(16,185,129,0.15);}
.filter-inactive:hover{border-color:rgba(16,185,129,0.35);color:rgba(52,211,153,0.8);}
.appr-row{
    display:grid;grid-template-columns:2fr 1.2fr 90px 90px 80px 70px;
    align-items:center;gap:12px;
    padding:14px 20px;border-radius:12px;
    border:1px solid rgba(16,185,129,0.1);background:rgba(16,185,129,0.02);
    transition:all 0.3s;margin-bottom:8px;
}
.appr-row:hover{border-color:rgba(16,185,129,0.3);background:rgba(16,185,129,0.05);box-shadow:0 0 16px rgba(16,185,129,0.08);}
@media(max-width:768px){
    .appr-row{grid-template-columns:1fr 80px;grid-template-rows:auto auto;}
    .appr-hide{display:none;}
}
</style>

<!-- Filter Tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
    @foreach(['all' => 'All','pending' => 'Pending','approved' => 'Approved','rejected' => 'Rejected'] as $val => $label)
    <a href="{{ route('officer.approvals.index', ['status' => $val]) }}"
       class="filter-tab {{ (request('status','all') === $val) ? 'filter-active' : 'filter-inactive' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="glow-card" style="padding:0;overflow:hidden;">
    @if($approvals->isEmpty())
    <div style="text-align:center;padding:64px 24px;">
        <div style="width:60px;height:60px;border-radius:50%;background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
            <svg style="width:26px;height:26px;" fill="none" stroke="rgba(16,185,129,0.45)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <p style="color:rgba(160,200,175,0.45);font-size:13px;">No approval requests found.</p>
    </div>
    @else

    <!-- Header Row -->
    <div style="padding:16px 20px 8px;border-bottom:1px solid rgba(16,185,129,0.1);">
        <div class="appr-row" style="background:transparent;border:none;margin:0;padding:0;">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.45);">Student</span>
            <span class="appr-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.45);">Programme</span>
            <span class="appr-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.45);">Year</span>
            <span class="appr-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.45);">Type</span>
            <span class="appr-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.45);">Status</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.45);">Action</span>
        </div>
    </div>

    <div style="padding:12px 20px 20px;">
        @foreach($approvals as $approval)
        <div class="appr-row">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:900;color:#020904;flex-shrink:0;box-shadow:0 0 14px rgba(16,185,129,0.35);">
                    {{ strtoupper(substr($approval->clearance->user->name, 0, 1)) }}
                </div>
                <div>
                    <p style="font-size:13px;font-weight:700;color:#e2e8f0;margin-bottom:2px;">{{ $approval->clearance->user->name }}</p>
                    <p style="font-size:10px;color:rgba(160,200,175,0.4);font-family:monospace;">{{ $approval->clearance->user->student_id }}</p>
                </div>
            </div>

            <div class="appr-hide" style="font-size:11px;color:rgba(160,200,175,0.55);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $approval->clearance->user->programme ?? '—' }}
            </div>

            <div class="appr-hide" style="font-size:12px;font-weight:600;color:#e2e8f0;">{{ $approval->clearance->academic_year }}</div>

            <div class="appr-hide">
                <span style="font-size:10px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:rgba(160,200,175,0.6);padding:3px 10px;border-radius:999px;text-transform:capitalize;white-space:nowrap;">{{ $approval->clearance->clearance_type }}</span>
            </div>

            <div class="appr-hide">
                @if($approval->status==='approved')
                <span class="badge-approved">Approved</span>
                @elseif($approval->status==='rejected')
                <span class="badge-rejected">Rejected</span>
                @else
                <span class="badge-pending">Pending</span>
                @endif
            </div>

            <div>
                <a href="{{ route('officer.approvals.show', $approval) }}"
                   style="font-size:11px;font-weight:700;letter-spacing:0.04em;text-decoration:none;border:1px solid rgba(16,185,129,0.3);padding:5px 12px;border-radius:7px;color:#34d399;transition:all 0.2s;white-space:nowrap;"
                   onmouseover="this.style.background='rgba(16,185,129,0.1)'" onmouseout="this.style.background='transparent'">
                    {{ $approval->status === 'pending' ? 'REVIEW' : 'VIEW' }}
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div style="padding:12px 20px;border-top:1px solid rgba(16,185,129,0.1);">
        {{ $approvals->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
