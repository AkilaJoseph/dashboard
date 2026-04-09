@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')
@section('page-subtitle', 'Track your clearance progress across all MUST departments')

@section('content')
@php $user = auth()->user(); @endphp

<style>
.profile-card{
    background:rgba(4,18,10,0.85);
    border:1px solid rgba(16,185,129,0.35);
    border-radius:18px;
    backdrop-filter:blur(20px);
    box-shadow:0 0 40px rgba(16,185,129,0.12),0 20px 40px rgba(0,0,0,0.4);
    overflow:hidden;
    position:relative;
    margin-bottom:24px;
    animation:fade-in-up 0.5s ease forwards;
}
.profile-card::before{
    content:'';position:absolute;top:0;left:5%;right:5%;height:1px;
    background:linear-gradient(90deg,transparent,#34d399,#f59e0b,transparent);
}
.avatar{
    width:60px;height:60px;border-radius:50%;
    background:linear-gradient(135deg,#059669,#10b981,#34d399);
    display:flex;align-items:center;justify-content:center;
    font-size:22px;font-weight:900;color:#020904;
    box-shadow:0 0 25px rgba(16,185,129,0.6),0 0 50px rgba(16,185,129,0.2);
    flex-shrink:0;
    animation:emblem-pulse 4s ease-in-out infinite;
}
@keyframes emblem-pulse{
    0%,100%{box-shadow:0 0 20px rgba(16,185,129,0.5),0 0 40px rgba(16,185,129,0.15);}
    50%{box-shadow:0 0 40px rgba(16,185,129,0.8),0 0 70px rgba(16,185,129,0.3);}
}
.stat-glow-card{
    background:rgba(4,18,10,0.85);
    border-radius:16px;
    backdrop-filter:blur(16px);
    padding:20px 22px;
    position:relative;
    overflow:hidden;
    transition:transform 0.3s,box-shadow 0.3s;
    animation:fade-in-up 0.6s ease forwards;
}
.stat-glow-card:hover{transform:translateY(-4px);}
.stat-glow-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.stat-green{border:1px solid rgba(16,185,129,0.35);box-shadow:0 0 30px rgba(16,185,129,0.1);}
.stat-green::before{background:linear-gradient(90deg,transparent,#10b981,transparent);}
.stat-green:hover{box-shadow:0 0 50px rgba(16,185,129,0.25);}
.stat-amber{border:1px solid rgba(245,158,11,0.3);box-shadow:0 0 30px rgba(245,158,11,0.08);}
.stat-amber::before{background:linear-gradient(90deg,transparent,#f59e0b,transparent);}
.stat-amber:hover{box-shadow:0 0 50px rgba(245,158,11,0.2);}
.stat-blue{border:1px solid rgba(59,130,246,0.3);box-shadow:0 0 30px rgba(59,130,246,0.08);}
.stat-blue::before{background:linear-gradient(90deg,transparent,#3b82f6,transparent);}
.stat-blue:hover{box-shadow:0 0 50px rgba(59,130,246,0.2);}
.stat-emerald{border:1px solid rgba(52,211,153,0.4);box-shadow:0 0 30px rgba(52,211,153,0.12);}
.stat-emerald::before{background:linear-gradient(90deg,transparent,#34d399,#f59e0b,transparent);}
.stat-emerald:hover{box-shadow:0 0 50px rgba(52,211,153,0.3);}
.num-green{font-size:36px;font-weight:900;background:linear-gradient(135deg,#34d399,#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.num-amber{font-size:36px;font-weight:900;background:linear-gradient(135deg,#fbbf24,#f59e0b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.num-blue{font-size:36px;font-weight:900;background:linear-gradient(135deg,#60a5fa,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.num-emerald{font-size:36px;font-weight:900;background:linear-gradient(135deg,#6ee7b7,#34d399,#fbbf24);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.clearance-row{
    display:flex;align-items:center;justify-content:space-between;
    padding:16px;border-radius:12px;
    border:1px solid rgba(16,185,129,0.15);
    background:rgba(16,185,129,0.03);
    transition:all 0.3s;
    margin-bottom:10px;
}
.clearance-row:hover{
    border-color:rgba(16,185,129,0.4);
    background:rgba(16,185,129,0.07);
    box-shadow:0 0 20px rgba(16,185,129,0.1);
    transform:translateX(4px);
}
.info-panel{
    background:rgba(16,185,129,0.04);
    border:1px solid rgba(16,185,129,0.15);
    border-radius:14px;
    padding:20px 24px;
    margin-top:20px;
}
</style>

<!-- Profile Card -->
<div class="profile-card">
    <div style="padding:24px 28px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
            <div style="display:flex;align-items:center;gap:18px;">
                <div class="avatar">{{ strtoupper(substr($user->name,0,1)) }}</div>
                <div>
                    <h2 style="font-size:18px;font-weight:800;color:#e2e8f0;margin-bottom:4px;">{{ $user->name }}</h2>
                    <p style="font-size:12px;color:rgba(52,211,153,0.7);margin-bottom:8px;">{{ $user->programme ?? 'Programme not set' }}</p>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        @if($user->student_id)
                        <span style="font-size:10px;font-family:monospace;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;padding:3px 10px;border-radius:999px;">{{ $user->student_id }}</span>
                        @endif
                        @if($user->registration_number)
                        <span style="font-size:10px;font-family:monospace;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);color:#34d399;padding:3px 10px;border-radius:999px;">Reg: {{ $user->registration_number }}</span>
                        @endif
                        @if($user->college)
                        <span style="font-size:10px;background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;padding:3px 10px;border-radius:999px;">{{ $user->college }}</span>
                        @endif
                        @if($user->year_of_study)
                        <span style="font-size:10px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:rgba(160,200,175,0.7);padding:3px 10px;border-radius:999px;">{{ $user->year_of_study }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <a href="{{ route('student.clearances.create') }}" class="btn-glow" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Clearance
            </a>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
    <div class="stat-glow-card stat-green">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.6);margin-bottom:8px;">Total Requests</p>
        <p class="num-green">{{ $stats['total'] }}</p>
    </div>
    <div class="stat-glow-card stat-amber">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(251,191,36,0.6);margin-bottom:8px;">Pending</p>
        <p class="num-amber">{{ $stats['pending'] }}</p>
    </div>
    <div class="stat-glow-card stat-blue">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(96,165,250,0.6);margin-bottom:8px;">In Progress</p>
        <p class="num-blue">{{ $stats['in_progress'] }}</p>
    </div>
    <div class="stat-glow-card stat-emerald">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(110,231,183,0.6);margin-bottom:8px;">Approved</p>
        <p class="num-emerald">{{ $stats['approved'] }}</p>
    </div>
</div>

<!-- Recent Clearances -->
<div class="glow-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h3 style="font-size:15px;font-weight:700;color:#e2e8f0;">Recent Clearance Requests</h3>
        <a href="{{ route('student.clearances.index') }}" style="font-size:12px;color:rgba(52,211,153,0.8);text-decoration:none;font-weight:600;letter-spacing:0.04em;">VIEW ALL &rarr;</a>
    </div>

    @if($clearances->isEmpty())
    <div style="text-align:center;padding:48px 0;">
        <div style="width:56px;height:56px;border-radius:50%;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg style="width:24px;height:24px;color:rgba(16,185,129,0.4);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <p style="color:rgba(160,200,175,0.5);font-size:13px;margin-bottom:14px;">No clearance requests yet.</p>
        <a href="{{ route('student.clearances.create') }}" style="color:#34d399;font-size:12px;font-weight:700;text-decoration:none;letter-spacing:0.05em;">SUBMIT YOUR FIRST REQUEST &rarr;</a>
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
                        <span style="font-size:13px;font-weight:700;color:#e2e8f0;">{{ $clearance->academic_year }} &mdash; {{ $clearance->semester }}</span>
                        @if($clearance->status==='approved')
                        <span class="badge-approved">Approved</span>
                        @elseif($clearance->status==='rejected')
                        <span class="badge-rejected">Rejected</span>
                        @elseif($clearance->status==='in_progress')
                        <span class="badge-progress">In Progress</span>
                        @else
                        <span class="badge-pending">Pending</span>
                        @endif
                        <span style="font-size:10px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:rgba(160,200,175,0.6);padding:2px 8px;border-radius:999px;text-transform:capitalize;">{{ $clearance->clearance_type }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="flex:1;max-width:200px;background:rgba(16,185,129,0.1);border-radius:999px;height:4px;overflow:hidden;">
                            <div class="{{ $pct===100 ? 'glow-progress' : '' }}" style="height:4px;border-radius:999px;width:{{ $pct }}%;background:{{ $pct===100 ? 'linear-gradient(90deg,#10b981,#34d399)' : 'linear-gradient(90deg,#f59e0b,#fbbf24)' }};transition:width 1s ease;"></div>
                        </div>
                        <span style="font-size:11px;color:rgba(160,200,175,0.5);font-family:monospace;">{{ $approved }}/{{ $total }} depts</span>
                    </div>
                </div>
                <div style="margin-left:16px;color:rgba(16,185,129,0.5);">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>

<!-- How It Works -->
<div class="info-panel">
    <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;color:rgba(245,158,11,0.7);text-transform:uppercase;margin-bottom:14px;">&#9670; How the Clearance Process Works</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
        @foreach(['Submit a clearance request specifying the academic year and type.','Each department reviews and approves your request online.','Track progress in real time — no physical office visits required.','Once all departments approve, download your official certificate.'] as $i => $step)
        <div style="display:flex;gap:12px;align-items:flex-start;">
            <div style="width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;color:#020904;flex-shrink:0;">{{ $i+1 }}</div>
            <p style="font-size:12px;color:rgba(160,200,175,0.6);line-height:1.5;">{{ $step }}</p>
        </div>
        @endforeach
    </div>
</div>

@endsection
