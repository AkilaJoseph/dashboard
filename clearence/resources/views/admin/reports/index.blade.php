@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Clearance Reports')
@section('page-subtitle', 'Statistical overview and detailed reports for MUST clearance management')

@section('content')
<style>
.r-stat{background:rgba(4,18,10,0.85);border-radius:14px;backdrop-filter:blur(16px);padding:18px 20px;position:relative;overflow:hidden;transition:transform 0.3s;}
.r-stat:hover{transform:translateY(-3px);}
.bar-track{width:100%;background:rgba(16,185,129,0.08);border-radius:999px;height:6px;overflow:hidden;}
.bar-fill{height:6px;border-radius:999px;transition:width 1s ease;}
.dept-prog{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(16,185,129,0.07);}
.dept-prog:last-child{border-bottom:none;}
.report-row{display:grid;grid-template-columns:1.5fr 90px 90px 100px 90px 90px;align-items:center;gap:10px;padding:11px 16px;border-radius:9px;border:1px solid rgba(16,185,129,0.09);background:rgba(16,185,129,0.02);margin-bottom:6px;transition:all 0.2s;}
.report-row:hover{border-color:rgba(16,185,129,0.25);background:rgba(16,185,129,0.05);}
</style>

<!-- Summary Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:22px;">
    <div class="r-stat" style="border:1px solid rgba(59,130,246,0.3);">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(96,165,250,0.6);margin-bottom:8px;">Total Students</p>
        <p style="font-size:30px;font-weight:900;background:linear-gradient(135deg,#60a5fa,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['total_students'] }}</p>
    </div>
    <div class="r-stat" style="border:1px solid rgba(16,185,129,0.3);">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.6);margin-bottom:8px;">Total Clearances</p>
        <p style="font-size:30px;font-weight:900;background:linear-gradient(135deg,#34d399,#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['total_clearances'] }}</p>
    </div>
    <div class="r-stat" style="border:1px solid rgba(52,211,153,0.4);">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(110,231,183,0.6);margin-bottom:8px;">Approved</p>
        <p style="font-size:30px;font-weight:900;background:linear-gradient(135deg,#6ee7b7,#34d399,#fbbf24);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['approved'] }}</p>
    </div>
    <div class="r-stat" style="border:1px solid rgba(59,130,246,0.25);">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(96,165,250,0.55);margin-bottom:8px;">In Progress</p>
        <p style="font-size:30px;font-weight:900;background:linear-gradient(135deg,#60a5fa,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['in_progress'] }}</p>
    </div>
    <div class="r-stat" style="border:1px solid rgba(245,158,11,0.3);">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(251,191,36,0.6);margin-bottom:8px;">Pending</p>
        <p style="font-size:30px;font-weight:900;background:linear-gradient(135deg,#fbbf24,#f59e0b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['pending'] }}</p>
    </div>
    <div class="r-stat" style="border:1px solid rgba(239,68,68,0.25);">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(248,113,113,0.55);margin-bottom:8px;">Rejected</p>
        <p style="font-size:30px;font-weight:900;background:linear-gradient(135deg,#fca5a5,#ef4444);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $stats['rejected'] }}</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:22px;">

    <!-- Clearances by Type -->
    <div class="glow-card">
        <h3 style="font-size:13px;font-weight:800;color:#e2e8f0;margin-bottom:16px;">Clearances by Type</h3>
        @if($byType->isEmpty())
        <p style="color:rgba(160,200,175,0.4);font-size:12px;text-align:center;padding:32px 0;">No data yet.</p>
        @else
        @php $total = $byType->sum(); @endphp
        @foreach(['graduation'=>'Graduation','semester'=>'End of Semester','withdrawal'=>'Withdrawal','transfer'=>'Transfer'] as $key=>$label)
        @php $count = $byType[$key] ?? 0; $pct = $total > 0 ? round(($count/$total)*100) : 0; @endphp
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                <span style="font-size:12px;color:#e2e8f0;font-weight:600;">{{ $label }}</span>
                <span style="font-size:11px;color:rgba(160,200,175,0.5);">{{ $count }} <span style="font-size:10px;">({{ $pct }}%)</span></span>
            </div>
            <div class="bar-track">
                <div class="bar-fill" style="width:{{ $pct }}%;background:linear-gradient(90deg,#059669,#10b981,#34d399);box-shadow:0 0 8px rgba(16,185,129,0.4);"></div>
            </div>
        </div>
        @endforeach
        @endif
    </div>

    <!-- Department Performance -->
    <div class="glow-card">
        <h3 style="font-size:13px;font-weight:800;color:#e2e8f0;margin-bottom:16px;">Department Performance</h3>
        @forelse($deptStats as $dept)
        @php $total = $dept->approvals_count; $pct = $total > 0 ? round(($dept->approved_count/$total)*100) : 0; @endphp
        <div class="dept-prog">
            <div style="flex:1;min-width:0;">
                <p style="font-size:11px;font-weight:700;color:#e2e8f0;margin-bottom:5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $dept->name }}</p>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ $pct }}%;background:{{ $pct===100 ? 'linear-gradient(90deg,#059669,#10b981,#34d399)' : 'linear-gradient(90deg,#f59e0b,#fbbf24)' }};box-shadow:{{ $pct===100 ? '0 0 8px rgba(16,185,129,0.4)' : '0 0 6px rgba(245,158,11,0.35)' }};"></div>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <p style="font-size:12px;font-weight:700;color:#e2e8f0;font-family:monospace;">{{ $dept->approved_count }}/{{ $total }}</p>
                <p style="font-size:10px;color:rgba(160,200,175,0.35);">{{ $pct }}%</p>
            </div>
        </div>
        @empty
        <p style="color:rgba(160,200,175,0.4);font-size:12px;text-align:center;padding:24px 0;">No data yet.</p>
        @endforelse
    </div>
</div>

<!-- Filter by Academic Year -->
<div class="glow-card" style="margin-bottom:22px;">
    <h3 style="font-size:13px;font-weight:800;color:#e2e8f0;margin-bottom:16px;">Filter Clearances by Academic Year</h3>
    <form method="GET" action="{{ route('admin.reports.index') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <select name="year" class="glow-input" style="min-width:200px;">
            <option value="">-- All Academic Years --</option>
            @foreach($academicYears as $yr)
            <option value="{{ $yr }}" {{ $selectedYear===$yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-glow">Generate Report</button>
        @if($selectedYear)
        <a href="{{ route('admin.reports.index') }}" style="font-size:12px;color:rgba(160,200,175,0.5);text-decoration:none;">Clear</a>
        @endif
    </form>

    @if($filteredClearances !== null)
    <div style="margin-top:20px;">
        <p style="font-size:12px;font-weight:600;color:rgba(160,200,175,0.7);margin-bottom:14px;">
            Results for <span style="color:#34d399;font-weight:800;">{{ $selectedYear }}</span> &mdash; {{ $filteredClearances->count() }} record(s)
        </p>
        @if($filteredClearances->isEmpty())
        <p style="color:rgba(160,200,175,0.4);font-size:12px;">No clearances found for this academic year.</p>
        @else
        <div class="report-row" style="background:transparent;border:none;margin-bottom:4px;">
            @foreach(['Student','Student ID','Semester','Type','Status','Submitted'] as $h)
            <span style="font-size:10px;font-weight:700;letter-spacing:0.09em;text-transform:uppercase;color:rgba(52,211,153,0.4);">{{ $h }}</span>
            @endforeach
        </div>
        @foreach($filteredClearances as $c)
        <div class="report-row">
            <p style="font-size:12px;font-weight:700;color:#e2e8f0;">{{ $c->user->name }}</p>
            <p style="font-size:10px;font-family:monospace;color:rgba(160,200,175,0.5);">{{ $c->user->student_id }}</p>
            <p style="font-size:11px;color:rgba(160,200,175,0.6);">{{ $c->semester }}</p>
            <span style="font-size:10px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:rgba(160,200,175,0.6);padding:2px 8px;border-radius:999px;text-transform:capitalize;">{{ $c->clearance_type }}</span>
            @if($c->status==='approved')
            <span class="badge-approved">Approved</span>
            @elseif($c->status==='rejected')
            <span class="badge-rejected">Rejected</span>
            @elseif($c->status==='in_progress')
            <span class="badge-progress">In Progress</span>
            @else
            <span class="badge-pending">Pending</span>
            @endif
            <p style="font-size:11px;color:rgba(160,200,175,0.4);">{{ $c->submitted_at->format('d M Y') }}</p>
        </div>
        @endforeach
        @endif
    </div>
    @endif
</div>

<!-- Recently Cleared Students -->
<div class="glow-card">
    <h3 style="font-size:13px;font-weight:800;color:#e2e8f0;margin-bottom:16px;">Recently Cleared Students</h3>
    @if($recentApproved->isEmpty())
    <div style="text-align:center;padding:40px 0;">
        <p style="color:rgba(160,200,175,0.4);font-size:12px;">No approved clearances yet.</p>
    </div>
    @else
    <div>
        <div style="display:grid;grid-template-columns:1.5fr 1.2fr 90px 100px 90px;gap:10px;padding:0 16px 8px;margin-bottom:4px;">
            @foreach(['Student','Programme','Acad. Year','Type','Completed'] as $h)
            <span style="font-size:10px;font-weight:700;letter-spacing:0.09em;text-transform:uppercase;color:rgba(52,211,153,0.4);">{{ $h }}</span>
            @endforeach
        </div>
        @foreach($recentApproved as $c)
        <div style="display:grid;grid-template-columns:1.5fr 1.2fr 90px 100px 90px;align-items:center;gap:10px;padding:10px 16px;border-radius:9px;border:1px solid rgba(16,185,129,0.09);background:rgba(16,185,129,0.02);margin-bottom:6px;transition:all 0.2s;"
             onmouseover="this.style.borderColor='rgba(16,185,129,0.25)'" onmouseout="this.style.borderColor='rgba(16,185,129,0.09)'">
            <div>
                <p style="font-size:12px;font-weight:700;color:#e2e8f0;margin-bottom:2px;">{{ $c->user->name }}</p>
                <p style="font-size:10px;font-family:monospace;color:rgba(160,200,175,0.35);">{{ $c->user->student_id }}</p>
            </div>
            <p style="font-size:11px;color:rgba(160,200,175,0.5);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $c->user->programme ?? '—' }}</p>
            <p style="font-size:12px;font-weight:600;color:#e2e8f0;">{{ $c->academic_year }}</p>
            <span style="font-size:10px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:rgba(160,200,175,0.6);padding:2px 8px;border-radius:999px;text-transform:capitalize;">{{ $c->clearance_type }}</span>
            <p style="font-size:11px;color:rgba(52,211,153,0.6);">{{ $c->completed_at?->format('d M Y') ?? '—' }}</p>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
