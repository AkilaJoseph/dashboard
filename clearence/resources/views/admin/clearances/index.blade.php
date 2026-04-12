@extends('layouts.app')

@section('title', 'Manage Clearances')
@section('page-title', 'Manage Clearances')
@section('page-subtitle', 'View and override all student clearance requests')

@section('content')
<style>
@@keyframes statSlideUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
.stat-anim { opacity:0; animation: statSlideUp 0.5s cubic-bezier(0.22,1,0.36,1) forwards; }
</style>

<!-- Stats -->
<div class="grid grid-cols-2 gap-5 mb-6 lg:grid-cols-5">
    @php
    $cards = [
        ['label'=>'Total',       'key'=>'total',       'color'=>'#059669', 'bg'=>'#f0fdf4', 'desc'=>'All requests'],
        ['label'=>'Pending',     'key'=>'pending',     'color'=>'#d97706', 'bg'=>'#fffbeb', 'desc'=>'Not started'],
        ['label'=>'In Progress', 'key'=>'in_progress', 'color'=>'#3b82f6', 'bg'=>'#eff6ff', 'desc'=>'Partially approved'],
        ['label'=>'Approved',    'key'=>'approved',    'color'=>'#10b981', 'bg'=>'#f0fdf4', 'desc'=>'Fully cleared'],
        ['label'=>'Rejected',    'key'=>'rejected',    'color'=>'#ef4444', 'bg'=>'#fef2f2', 'desc'=>'Has rejection'],
    ];
    @endphp
    @foreach($cards as $i => $c)
    <div class="stat-anim bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-5 flex flex-col gap-3"
         style="animation-delay:{{ $i*80 }}ms;">
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold tracking-widest uppercase text-slate-400">{{ $c['label'] }}</p>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $c['bg'] }};">
                <div class="w-2 h-2 rounded-full" style="background:{{ $c['color'] }};"></div>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-slate-800 leading-none" data-count="{{ $stats[$c['key']] }}">0</p>
        <p class="text-xs text-slate-400">{{ $c['desc'] }}</p>
    </div>
    @endforeach
</div>

<!-- Filters -->
<div class="glow-card" style="margin-bottom:20px;">
    <form method="GET" action="{{ route('admin.clearances.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
            <label style="font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#475569;display:block;margin-bottom:5px;">Search Student</label>
            <input type="text" name="search" value="{{ request('search') }}"
                class="glow-input" placeholder="Name, Student ID, Reg No…">
        </div>
        <div style="min-width:140px;">
            <label style="font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#475569;display:block;margin-bottom:5px;">Status</label>
            <select name="status" class="glow-input" style="appearance:none;padding-right:32px;">
                <option value="all" {{ request('status','all')==='all'?'selected':'' }}>All Statuses</option>
                <option value="pending"     {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                <option value="in_progress" {{ request('status')==='in_progress'?'selected':'' }}>In Progress</option>
                <option value="approved"    {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                <option value="rejected"    {{ request('status')==='rejected'?'selected':'' }}>Rejected</option>
            </select>
        </div>
        <div style="min-width:140px;">
            <label style="font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#475569;display:block;margin-bottom:5px;">Type</label>
            <select name="type" class="glow-input" style="appearance:none;padding-right:32px;">
                <option value="all" {{ request('type','all')==='all'?'selected':'' }}>All Types</option>
                <option value="graduation"  {{ request('type')==='graduation'?'selected':'' }}>Graduation</option>
                <option value="semester"    {{ request('type')==='semester'?'selected':'' }}>Semester</option>
                <option value="withdrawal"  {{ request('type')==='withdrawal'?'selected':'' }}>Withdrawal</option>
                <option value="transfer"    {{ request('type')==='transfer'?'selected':'' }}>Transfer</option>
            </select>
        </div>
        <button type="submit" class="btn-glow">Filter</button>
        @if(request()->hasAny(['search','status','type']))
        <a href="{{ route('admin.clearances.index') }}" style="font-size:12px;color:#64748b;text-decoration:none;padding:9px 14px;border:1px solid #e2e8f0;border-radius:8px;display:inline-flex;align-items:center;">Clear</a>
        @endif
    </form>
</div>

<!-- Table -->
<div class="glow-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h3 style="font-size:14px;font-weight:700;color:#1e293b;">
            Clearance Requests
            <span style="font-size:11px;font-weight:500;color:#94a3b8;margin-left:6px;">{{ $clearances->total() }} total</span>
        </h3>
    </div>

    @if($clearances->isEmpty())
    <div style="text-align:center;padding:50px 0;">
        <p style="color:#64748b;font-size:13px;">No clearance requests found.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table class="glow-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:12px 16px;text-align:left;">#</th>
                    <th style="padding:12px 16px;text-align:left;">Student</th>
                    <th style="padding:12px 16px;text-align:left;">Type</th>
                    <th style="padding:12px 16px;text-align:left;">Academic Year</th>
                    <th style="padding:12px 16px;text-align:left;">Status</th>
                    <th style="padding:12px 16px;text-align:left;">Submitted</th>
                    <th style="padding:12px 16px;text-align:left;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clearances as $clearance)
                <tr>
                    <td style="padding:13px 16px;font-size:11px;color:#94a3b8;font-family:monospace;">{{ $clearance->id }}</td>
                    <td style="padding:13px 16px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:8px;background:#059669;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;">
                                {{ strtoupper(substr($clearance->user->name,0,1)) }}
                            </div>
                            <div>
                                <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0;">{{ $clearance->user->name }}</p>
                                <p style="font-size:11px;color:#94a3b8;margin:0;font-family:monospace;">{{ $clearance->user->student_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding:13px 16px;">
                        <span style="font-size:11px;background:#f1f5f9;border:1px solid #e2e8f0;color:#64748b;padding:2px 8px;border-radius:999px;text-transform:capitalize;">{{ $clearance->clearance_type }}</span>
                    </td>
                    <td style="padding:13px 16px;font-size:12px;color:#475569;">{{ $clearance->academic_year }} · {{ $clearance->semester }}</td>
                    <td style="padding:13px 16px;">
                        @if($clearance->status === 'approved')
                            <span class="badge badge-approved">Approved</span>
                        @elseif($clearance->status === 'rejected')
                            <span class="badge badge-rejected">Rejected</span>
                        @elseif($clearance->status === 'in_progress')
                            <span class="badge badge-progress">In Progress</span>
                        @else
                            <span class="badge badge-pending">Pending</span>
                        @endif
                    </td>
                    <td style="padding:13px 16px;font-size:11px;color:#94a3b8;">{{ $clearance->submitted_at?->format('d M Y') ?? '—' }}</td>
                    <td style="padding:13px 16px;">
                        <a href="{{ route('admin.clearances.show', $clearance) }}"
                           style="font-size:12px;font-weight:600;color:#059669;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                            View &amp; Override
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">
        {{ $clearances->links() }}
    </div>
    @endif
</div>

<script>
(function(){
    function easeOutQuart(t){return 1-Math.pow(1-t,4);}
    function animateCount(el,target,dur){
        if(target===0){el.textContent='0';return;}
        var start=performance.now();
        function step(now){
            var p=Math.min((now-start)/dur,1);
            el.textContent=Math.floor(easeOutQuart(p)*target);
            if(p<1)requestAnimationFrame(step);else el.textContent=target;
        }
        requestAnimationFrame(step);
    }
    document.querySelectorAll('[data-count]').forEach(function(el,i){
        setTimeout(function(){animateCount(el,parseInt(el.dataset.count)||0,700);},i*80+200);
    });
})();
</script>
@endsection
