@extends('layouts.app')

@section('title', 'Officer Dashboard')
@section('page-title', 'Officer Dashboard')
@section('page-subtitle', 'Manage clearance approval requests for your department')

@section('content')
@php $dept = auth()->user()->department; @endphp

<style>
@@keyframes statSlideUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
.stat-anim {
    opacity:0;
    animation: statSlideUp 0.5s cubic-bezier(0.22,1,0.36,1) forwards;
}
</style>

<!-- Department Header — no border-left strip -->
<div class="glow-card" style="margin-bottom:20px;">
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

<!-- Stats — animated, no border strips -->
<div class="grid grid-cols-2 gap-5 mb-6 lg:grid-cols-4">

    {{-- Total --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:0ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Total</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f0fdf4;">
                <svg class="w-5 h-5" style="color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['total'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">All requests received</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#059669;opacity:0.2;"></div>
    </div>

    {{-- Pending --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:100ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Pending</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#fffbeb;">
                <svg class="w-5 h-5" style="color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['pending'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">Awaiting your review</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#d97706;opacity:0.2;"></div>
    </div>

    {{-- Approved --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:200ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Approved</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#f0fdf4;">
                <svg class="w-5 h-5" style="color:#10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['approved'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">Cleared by your dept</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#10b981;opacity:0.2;"></div>
    </div>

    {{-- Rejected --}}
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out p-6 flex flex-col gap-4"
         style="animation-delay:300ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">Rejected</p>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#fef2f2;">
                <svg class="w-5 h-5" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-4xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats['rejected'] }}">0</p>
            <p class="text-xs text-slate-400 mt-2">Returned to student</p>
        </div>
        <div class="h-1 w-10 rounded-full" style="background:#ef4444;opacity:0.2;"></div>
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
