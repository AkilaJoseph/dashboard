@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'System Overview')
@section('page-subtitle', 'MUST Clearance Management System &mdash; Administrator Control Center')

@section('content')
<style>
@@keyframes statSlideUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
.stat-anim {
    opacity:0;
    animation: statSlideUp 0.5s cubic-bezier(0.22,1,0.36,1) forwards;
}
.cl-row-admin{
    display:grid;grid-template-columns:2fr 100px 100px 100px 100px;
    align-items:center;gap:10px;padding:12px 18px;border-radius:9px;
    border:1px solid #e2e8f0;background:#fff;
    transition:all 0.15s;margin-bottom:6px;
}
.cl-row-admin:hover{border-color:#a7f3d0;background:#f0fdf4;}
</style>

<!-- Primary Stats Grid — 4 cards, no border strips -->
<div class="grid grid-cols-2 gap-5 mb-5 lg:grid-cols-4">

    {{-- Students --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:0ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Students</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#eff6ff;">
                <svg class="w-5 h-5" style="color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['total_students'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">Registered students</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#3b82f6;opacity:0.2;"></div>
    </div>

    {{-- Officers --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:100ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Officers</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f5f3ff;">
                <svg class="w-5 h-5" style="color:#8b5cf6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['total_officers'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">Department officers</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#8b5cf6;opacity:0.2;"></div>
    </div>

    {{-- Departments --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:200ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Departments</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#fffbeb;">
                <svg class="w-5 h-5" style="color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['total_departments'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">Active departments</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#d97706;opacity:0.2;"></div>
    </div>

    {{-- Clearances --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:300ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Clearances</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f0fdf4;">
                <svg class="w-5 h-5" style="color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['total_clearances'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">All submissions</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#059669;opacity:0.2;"></div>
    </div>

</div>

<!-- Status Cards — 3 cards, no border strips -->
<div class="grid grid-cols-3 gap-5 mb-6">

    {{-- Pending --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex items-center gap-4"
         style="animation-delay:400ms;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fffbeb;">
            <svg class="w-6 h-6" style="color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400 mb-1">Pending</p>
            <p class="text-3xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['pending_clearances'] }}">0</p>
        </div>
    </div>

    {{-- In Progress --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex items-center gap-4"
         style="animation-delay:500ms;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#eff6ff;">
            <svg class="w-6 h-6" style="color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400 mb-1">In Progress</p>
            <p class="text-3xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['inprogress_clearances'] }}">0</p>
        </div>
    </div>

    {{-- Approved --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex items-center gap-4"
         style="animation-delay:600ms;">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f0fdf4;">
            <svg class="w-6 h-6" style="color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400 mb-1">Approved</p>
            <p class="text-3xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['approved_clearances'] }}">0</p>
        </div>
    </div>

</div>

@include('admin.partials.bottleneck-widget')

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
