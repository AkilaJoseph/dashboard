@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')
@section('page-subtitle', 'Track your clearance progress across all MUST departments')

@section('content')
@php $user = auth()->user(); @endphp

<style>
.profile-card{
    background:#fff;border:1px solid #e2e8f0;border-radius:14px;
    box-shadow:0 1px 4px rgba(0,0,0,0.06);overflow:hidden;
    margin-bottom:22px;
}
.avatar{
    width:56px;height:56px;border-radius:10px;
    background:#059669;
    display:flex;align-items:center;justify-content:center;
    font-size:20px;font-weight:900;color:#fff;flex-shrink:0;
}

/* ── Animated stat cards ── */
@@keyframes statSlideUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
.stat-anim {
    opacity:0;
    animation: statSlideUp 0.5s cubic-bezier(0.22,1,0.36,1) forwards;
}

.clearance-row{
    display:flex;align-items:center;justify-content:space-between;
    padding:14px 16px;border-radius:10px;
    border:1px solid #e2e8f0;background:#fff;
    transition:all 0.2s;margin-bottom:8px;
}
.clearance-row:hover{border-color:#a7f3d0;background:#f0fdf4;transform:translateX(3px);}
.info-panel{
    background:#f0fdf4;border:1px solid #a7f3d0;border-radius:12px;
    padding:18px 22px;margin-top:18px;
}
</style>

<!-- Profile Card -->
<div class="profile-card">
    <div style="padding:20px 24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
            <div style="display:flex;align-items:center;gap:16px;">
                <div class="avatar">{{ strtoupper(substr($user->name,0,1)) }}</div>
                <div>
                    <h2 style="font-size:17px;font-weight:700;color:#1e293b;margin-bottom:3px;">{{ $user->name }}</h2>
                    <p style="font-size:12px;color:#64748b;margin-bottom:8px;">{{ $user->programme ?? 'Programme not set' }}</p>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @if($user->student_id)
                        <span style="font-size:10px;font-family:monospace;background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:2px 9px;border-radius:999px;font-weight:600;">{{ $user->student_id }}</span>
                        @endif
                        @if($user->registration_number)
                        <span style="font-size:10px;font-family:monospace;background:#d1fae5;border:1px solid #a7f3d0;color:#065f46;padding:2px 9px;border-radius:999px;font-weight:600;">Reg: {{ $user->registration_number }}</span>
                        @endif
                        @if($user->college)
                        <span style="font-size:10px;background:#ede9fe;border:1px solid #ddd6fe;color:#4c1d95;padding:2px 9px;border-radius:999px;font-weight:600;">{{ $user->college }}</span>
                        @endif
                        @if($user->year_of_study)
                        <span style="font-size:10px;background:#e0f2fe;border:1px solid #bae6fd;color:#0c4a6e;padding:2px 9px;border-radius:999px;font-weight:600;">{{ $user->year_of_study }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <a href="{{ route('student.clearances.create') }}" class="btn-glow" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Clearance
            </a>
        </div>
    </div>
</div>

<!-- Stats Grid — animated cards -->
<div class="grid grid-cols-2 gap-5 mb-6 lg:grid-cols-4">

    {{-- 1 — Total Requests --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
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
               data-count="{{ $stats['total'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">All submitted clearances</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#059669;opacity:0.2;"></div>
    </div>

    {{-- 2 — Pending --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
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
        <div class="h-1 w-10 rounded-full" style="background:#d97706;opacity:0.2;"></div>
    </div>

    {{-- 3 — In Progress --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
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
        <div class="h-1 w-10 rounded-full" style="background:#3b82f6;opacity:0.2;"></div>
    </div>

    {{-- 4 — Approved --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
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
        <div class="h-1 w-10 rounded-full" style="background:#10b981;opacity:0.2;"></div>
    </div>

</div>

<!-- Recent Clearances -->
<div class="glow-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <h3 style="font-size:14px;font-weight:700;color:#1e293b;">Recent Clearance Requests</h3>
        <a href="{{ route('student.clearances.index') }}" style="font-size:12px;color:#059669;text-decoration:none;font-weight:600;">View all &rarr;</a>
    </div>

    @if($clearances->isEmpty())
    <div style="text-align:center;padding:40px 0;">
        <div style="width:52px;height:52px;border-radius:50%;background:#f0fdf4;border:1px solid #a7f3d0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg style="width:22px;height:22px;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <p style="color:#64748b;font-size:13px;margin-bottom:12px;">No clearance requests yet.</p>
        <a href="{{ route('student.clearances.create') }}" style="color:#059669;font-size:12px;font-weight:600;text-decoration:none;">Submit your first request &rarr;</a>
    </div>
    @else
    <div>
        @foreach($clearances as $clearance)
        @php
            $approved = $clearance->approvals->where('status','approved')->count();
            $total    = $clearance->approvals->count();
            $pct      = $total > 0 ? round(($approved/$total)*100) : 0;
        @endphp
        <a href="{{ route('student.clearances.show', $clearance) }}" style="text-decoration:none;display:block;">
            <div class="clearance-row">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                        <span style="font-size:13px;font-weight:600;color:#1e293b;">{{ $clearance->academic_year }} &mdash; {{ $clearance->semester }}</span>
                        @if($clearance->status==='approved')
                        <span class="badge-approved">Approved</span>
                        @elseif($clearance->status==='rejected')
                        <span class="badge-rejected">Rejected</span>
                        @elseif($clearance->status==='in_progress')
                        <span class="badge-progress">In Progress</span>
                        @else
                        <span class="badge-pending">Pending</span>
                        @endif
                        <span style="font-size:10px;background:#f1f5f9;border:1px solid #cbd5e1;color:#64748b;padding:2px 8px;border-radius:999px;text-transform:capitalize;">{{ $clearance->clearance_type }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="flex:1;max-width:200px;background:#e2e8f0;border-radius:999px;height:5px;overflow:hidden;">
                            <div style="height:5px;border-radius:999px;width:{{ $pct }}%;background:{{ $pct===100 ? '#059669' : '#d97706' }};transition:width 1s ease;"></div>
                        </div>
                        <span style="font-size:11px;color:#94a3b8;font-family:monospace;">{{ $approved }}/{{ $total }} depts</span>
                    </div>
                </div>
                <div style="margin-left:16px;color:#cbd5e1;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>

<!-- How It Works -->
<div class="info-panel">
    <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;color:#065f46;text-transform:uppercase;margin-bottom:14px;">How the Clearance Process Works</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
        @foreach(['Submit a clearance request specifying the academic year and type.','Each department reviews and approves your request online.','Track progress in real time — no physical office visits required.','Once all departments approve, download your official certificate.'] as $i => $step)
        <div style="display:flex;gap:10px;align-items:flex-start;">
            <div style="width:22px;height:22px;border-radius:50%;background:#059669;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;">{{ $i+1 }}</div>
            <p style="font-size:12px;color:#374151;line-height:1.55;">{{ $step }}</p>
        </div>
        @endforeach
    </div>
</div>

<script>
(function () {
    function easeOutQuart(t) { return 1 - Math.pow(1 - t, 4); }
    function animateCount(el, target, duration) {
        if (target === 0) { el.textContent = '0'; return; }
        var start = performance.now();
        function step(now) {
            var elapsed  = now - start;
            var progress = Math.min(elapsed / duration, 1);
            el.textContent = Math.floor(easeOutQuart(progress) * target);
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target;
        }
        requestAnimationFrame(step);
    }
    document.querySelectorAll('[data-count]').forEach(function (el, i) {
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        setTimeout(function () { animateCount(el, target, 800); }, i * 100 + 300);
    });
})();
</script>
@endsection
