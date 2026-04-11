@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Clearance Reports')
@section('page-subtitle', 'Statistical overview and detailed reports for MUST clearance management')

@section('content')
<style>
/* Stat card entrance animation */
@@keyframes cardSlideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.stat-card-animated {
    opacity: 0;
    animation: cardSlideUp 0.5s cubic-bezier(0.22,1,0.36,1) forwards;
}

/* Bar/table reuse */
.bar-track{width:100%;background:#e2e8f0;border-radius:999px;height:6px;overflow:hidden;}
.bar-fill{height:6px;border-radius:999px;transition:width 1s ease;}
.dept-prog{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;}
.dept-prog:last-child{border-bottom:none;}
.report-row{display:grid;grid-template-columns:1.5fr 90px 90px 100px 90px 90px;align-items:center;gap:10px;padding:11px 16px;border-radius:9px;border:1px solid #e2e8f0;background:#fff;margin-bottom:6px;transition:all 0.15s;}
.report-row:hover{border-color:#a7f3d0;background:#f0fdf4;}
</style>

<!-- ═══════════════════════════════════════════════════
     ANIMATED STAT CARDS — 4 column grid, no border strips
     ═══════════════════════════════════════════════════ -->
<div class="grid grid-cols-2 gap-5 mb-6 lg:grid-cols-4">

    {{-- 1 — Total Requests --}}
    <div class="stat-card-animated bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:0ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Total Requests</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f0fdf4;">
                <svg class="w-5 h-5" style="color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none"
               data-count="{{ $stats['total_clearances'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">All submitted clearances</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#059669;opacity:0.25;"></div>
    </div>

    {{-- 2 — Pending --}}
    <div class="stat-card-animated bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:100ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Pending</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#fffbeb;">
                <svg class="w-5 h-5" style="color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none"
               data-count="{{ $stats['pending'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">Awaiting first review</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#d97706;opacity:0.25;"></div>
    </div>

    {{-- 3 — In Progress --}}
    <div class="stat-card-animated bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:200ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">In Progress</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eff6ff;">
                <svg class="w-5 h-5" style="color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none"
               data-count="{{ $stats['in_progress'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">Partially approved</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#3b82f6;opacity:0.25;"></div>
    </div>

    {{-- 4 — Approved --}}
    <div class="stat-card-animated bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:300ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Approved</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f0fdf4;">
                <svg class="w-5 h-5" style="color:#10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none"
               data-count="{{ $stats['approved'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">Fully cleared</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#10b981;opacity:0.25;"></div>
    </div>

</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:22px;">

    <!-- Clearances by Type -->
    <div class="glow-card">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:16px;">Clearances by Type</p>
        @if($byType->isEmpty())
        <p style="color:#94a3b8;font-size:12px;text-align:center;padding:32px 0;">No data yet.</p>
        @else
        @php $total = $byType->sum(); @endphp
        @foreach(['graduation'=>'Graduation','semester'=>'End of Semester','withdrawal'=>'Withdrawal','transfer'=>'Transfer'] as $key=>$label)
        @php $count = $byType[$key] ?? 0; $pct = $total > 0 ? round(($count/$total)*100) : 0; @endphp
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                <span style="font-size:12px;color:#1e293b;font-weight:600;">{{ $label }}</span>
                <span style="font-size:11px;color:#94a3b8;">{{ $count }} <span style="font-size:10px;">({{ $pct }}%)</span></span>
            </div>
            <div class="bar-track">
                <div class="bar-fill" style="width:{{ $pct }}%;background:#059669;"></div>
            </div>
        </div>
        @endforeach
        @endif
    </div>

    <!-- Department Performance -->
    <div class="glow-card">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:16px;">Department Performance</p>
        @forelse($deptStats as $dept)
        @php $total = $dept->approvals_count; $pct = $total > 0 ? round(($dept->approved_count/$total)*100) : 0; @endphp
        <div class="dept-prog">
            <div style="flex:1;min-width:0;">
                <p style="font-size:11px;font-weight:600;color:#1e293b;margin-bottom:5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $dept->name }}</p>
                <div class="bar-track">
                    <div class="bar-fill" style="width:{{ $pct }}%;background:{{ $pct===100 ? '#059669' : '#d97706' }};"></div>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <p style="font-size:12px;font-weight:700;color:#1e293b;font-family:monospace;">{{ $dept->approved_count }}/{{ $total }}</p>
                <p style="font-size:10px;color:#94a3b8;">{{ $pct }}%</p>
            </div>
        </div>
        @empty
        <p style="color:#94a3b8;font-size:12px;text-align:center;padding:24px 0;">No data yet.</p>
        @endforelse
    </div>
</div>

<!-- Filter by Academic Year -->
<div class="glow-card" style="margin-bottom:22px;">
    <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Filter Clearances by Academic Year</p>
    <form method="GET" action="{{ route('admin.reports.index') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <select name="year" class="glow-input" style="min-width:200px;">
            <option value="">-- All Academic Years --</option>
            @foreach($academicYears as $yr)
            <option value="{{ $yr }}" {{ $selectedYear===$yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-glow">Generate Report</button>
        @if($selectedYear)
        <a href="{{ route('admin.reports.index') }}" style="font-size:12px;color:#64748b;text-decoration:none;">Clear filter</a>
        @endif
    </form>

    @if($filteredClearances !== null)
    <div style="margin-top:20px;">
        <p style="font-size:12px;font-weight:600;color:#475569;margin-bottom:14px;">
            Results for <span style="color:#059669;font-weight:700;">{{ $selectedYear }}</span> &mdash; {{ $filteredClearances->count() }} record(s)
        </p>
        @if($filteredClearances->isEmpty())
        <p style="color:#94a3b8;font-size:12px;">No clearances found for this academic year.</p>
        @else
        <div class="report-row" style="background:transparent;border:none;margin-bottom:4px;">
            @foreach(['Student','Student ID','Semester','Type','Status','Submitted'] as $h)
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">{{ $h }}</span>
            @endforeach
        </div>
        @foreach($filteredClearances as $c)
        <div class="report-row">
            <p style="font-size:12px;font-weight:600;color:#1e293b;">{{ $c->user->name }}</p>
            <p style="font-size:10px;font-family:monospace;color:#94a3b8;">{{ $c->user->student_id }}</p>
            <p style="font-size:11px;color:#64748b;">{{ $c->semester }}</p>
            <span style="font-size:10px;background:#f1f5f9;border:1px solid #cbd5e1;color:#475569;padding:2px 8px;border-radius:999px;text-transform:capitalize;white-space:nowrap;">{{ $c->clearance_type }}</span>
            @if($c->status==='approved')
            <span class="badge badge-approved">Approved</span>
            @elseif($c->status==='rejected')
            <span class="badge badge-rejected">Rejected</span>
            @elseif($c->status==='in_progress')
            <span class="badge badge-progress">In Progress</span>
            @else
            <span class="badge badge-pending">Pending</span>
            @endif
            <p style="font-size:11px;color:#94a3b8;">{{ $c->submitted_at->format('d M Y') }}</p>
        </div>
        @endforeach
        @endif
    </div>
    @endif
</div>

<!-- Recently Cleared Students -->
<div class="glow-card">
    <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:16px;">Recently Cleared Students</p>
    @if($recentApproved->isEmpty())
    <div style="text-align:center;padding:40px 0;">
        <p style="color:#94a3b8;font-size:12px;">No approved clearances yet.</p>
    </div>
    @else
    <div>
        <div style="display:grid;grid-template-columns:1.5fr 1.2fr 90px 100px 90px;gap:10px;padding:0 16px 8px;margin-bottom:4px;">
            @foreach(['Student','Programme','Acad. Year','Type','Completed'] as $h)
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">{{ $h }}</span>
            @endforeach
        </div>
        @foreach($recentApproved as $c)
        <div style="display:grid;grid-template-columns:1.5fr 1.2fr 90px 100px 90px;align-items:center;gap:10px;padding:11px 16px;border-radius:9px;border:1px solid #e2e8f0;background:#fff;margin-bottom:6px;transition:all 0.15s;"
             onmouseover="this.style.borderColor='#a7f3d0';this.style.background='#f0fdf4'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff'">
            <div>
                <p style="font-size:12px;font-weight:600;color:#1e293b;margin-bottom:2px;">{{ $c->user->name }}</p>
                <p style="font-size:10px;font-family:monospace;color:#94a3b8;">{{ $c->user->student_id }}</p>
            </div>
            <p style="font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $c->user->programme ?? '—' }}</p>
            <p style="font-size:12px;font-weight:600;color:#1e293b;">{{ $c->academic_year }}</p>
            <span style="font-size:10px;background:#f1f5f9;border:1px solid #cbd5e1;color:#475569;padding:2px 8px;border-radius:999px;text-transform:capitalize;white-space:nowrap;">{{ $c->clearance_type }}</span>
            <p style="font-size:11px;color:#059669;font-weight:600;">{{ $c->completed_at?->format('d M Y') ?? '—' }}</p>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ─── Count-up animation script ─── --}}
<script>
(function () {
    function easeOutQuart(t) { return 1 - Math.pow(1 - t, 4); }

    function animateCount(el, target, duration) {
        if (target === 0) { el.textContent = '0'; return; }
        const start = performance.now();
        function step(now) {
            const elapsed  = now - start;
            const progress = Math.min(elapsed / duration, 1);
            el.textContent = Math.floor(easeOutQuart(progress) * target);
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target;
        }
        requestAnimationFrame(step);
    }

    // Fire count-up after each card's animation has settled
    document.querySelectorAll('[data-count]').forEach(function (el, i) {
        var target   = parseInt(el.getAttribute('data-count'), 10) || 0;
        var cardDelay = i * 100; // matches animation-delay stagger (ms)
        var countDelay = cardDelay + 300; // start counting shortly after card appears
        setTimeout(function () { animateCount(el, target, 800); }, countDelay);
    });
})();
</script>
@endsection
