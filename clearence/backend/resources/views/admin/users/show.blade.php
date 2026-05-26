@extends('layouts.app')

@section('title', $user->name)
@section('page-title', $user->name)
@section('page-subtitle', ucfirst($user->role) . ' Account Details')

@section('content')

<div class="grid grid-cols-1 gap-5 mb-5 lg:grid-cols-3">

    <!-- Profile Card -->
    <div class="glow-card" style="lg:col-span:1;">
        <div style="text-align:center;padding-bottom:16px;border-bottom:1px solid #f1f5f9;margin-bottom:16px;">
            <div style="width:60px;height:60px;border-radius:14px;background:#059669;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900;color:#fff;margin:0 auto 12px;">
                {{ strtoupper(substr($user->name,0,1)) }}
            </div>
            <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:4px;">{{ $user->name }}</h3>
            <p style="font-size:12px;color:#64748b;margin-bottom:8px;">{{ $user->email }}</p>
            <div style="display:flex;justify-content:center;gap:6px;flex-wrap:wrap;">
                @if($user->role === 'admin')
                    <span style="font-size:11px;background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:2px 10px;border-radius:999px;font-weight:700;">Admin</span>
                @elseif($user->role === 'officer')
                    <span style="font-size:11px;background:#ede9fe;border:1px solid #ddd6fe;color:#4c1d95;padding:2px 10px;border-radius:999px;font-weight:700;">Officer</span>
                @else
                    <span style="font-size:11px;background:#d1fae5;border:1px solid #a7f3d0;color:#065f46;padding:2px 10px;border-radius:999px;font-weight:700;">Student</span>
                @endif
                @if($user->is_active)
                    <span style="font-size:11px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:2px 10px;border-radius:999px;font-weight:600;">Active</span>
                @else
                    <span style="font-size:11px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:2px 10px;border-radius:999px;font-weight:600;">Inactive</span>
                @endif
            </div>
        </div>

        <!-- Info fields -->
        <div style="display:grid;gap:10px;">
            @if($user->phone)
            <div>
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">Phone</p>
                <p style="font-size:13px;color:#1e293b;">{{ $user->phone }}</p>
            </div>
            @endif

            @if($user->isStudent())
            <div>
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">Student ID</p>
                <p style="font-size:13px;color:#1e293b;font-family:monospace;font-weight:600;">{{ $user->student_id ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">Registration Number</p>
                <p style="font-size:13px;color:#1e293b;font-family:monospace;">{{ $user->registration_number ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">Programme</p>
                <p style="font-size:13px;color:#1e293b;">{{ $user->programme ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">College</p>
                <p style="font-size:13px;color:#1e293b;">{{ $user->college ?? '—' }}</p>
            </div>
            <div>
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">Year of Study</p>
                <p style="font-size:13px;color:#1e293b;">{{ $user->year_of_study ?? '—' }}</p>
            </div>
            @elseif($user->isOfficer() && $user->department)
            <div>
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">Department</p>
                <p style="font-size:13px;color:#1e293b;">{{ $user->department->name }}</p>
            </div>
            @endif

            <div>
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">Registered</p>
                <p style="font-size:13px;color:#1e293b;">{{ $user->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:8px;margin-top:18px;padding-top:16px;border-top:1px solid #f1f5f9;">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-glow" style="flex:1;justify-content:center;font-size:12px;padding:8px 14px;">
                Edit User
            </a>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Delete this user? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" style="font-size:12px;padding:8px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;cursor:pointer;font-weight:600;">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Clearance History (students only) -->
    <div style="grid-column: span 2;">
        @if($user->isStudent())
        <div class="glow-card">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#059669;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #d1fae5;">
                Clearance History
                <span style="font-size:10px;font-weight:500;color:#94a3b8;letter-spacing:0;text-transform:none;margin-left:6px;">{{ $clearances->count() }} request(s)</span>
            </p>

            @if($clearances->isEmpty())
            <div style="text-align:center;padding:40px 0;">
                <p style="color:#94a3b8;font-size:13px;">No clearance requests submitted yet.</p>
            </div>
            @else
            <div style="display:grid;gap:10px;">
                @foreach($clearances as $clearance)
                @php
                    $approved = $clearance->approvals->where('status','approved')->count();
                    $total    = $clearance->approvals->count();
                    $pct      = $total > 0 ? round(($approved/$total)*100) : 0;
                @endphp
                <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:13px;font-weight:600;color:#1e293b;">{{ $clearance->academic_year }} · {{ $clearance->semester }}</span>
                            <span style="font-size:10px;background:#f1f5f9;border:1px solid #e2e8f0;color:#64748b;padding:2px 8px;border-radius:999px;text-transform:capitalize;">{{ $clearance->clearance_type }}</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            @if($clearance->status === 'approved')
                                <span class="badge badge-approved">Approved</span>
                            @elseif($clearance->status === 'rejected')
                                <span class="badge badge-rejected">Rejected</span>
                            @elseif($clearance->status === 'in_progress')
                                <span class="badge badge-progress">In Progress</span>
                            @else
                                <span class="badge badge-pending">Pending</span>
                            @endif
                            <a href="{{ route('admin.clearances.show', $clearance) }}"
                               style="font-size:11px;font-weight:600;color:#059669;text-decoration:none;">
                                Override →
                            </a>
                        </div>
                    </div>
                    <!-- Department progress -->
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                        @foreach($clearance->approvals->sortBy('department.priority') as $approval)
                        <span style="font-size:10px;padding:2px 8px;border-radius:999px;font-weight:600;
                            @if($approval->status==='approved') background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;
                            @elseif($approval->status==='rejected') background:#fee2e2;color:#991b1b;border:1px solid #fecaca;
                            @else background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;
                            @endif">
                            {{ $approval->department->code ?? substr($approval->department->name,0,3) }}
                        </span>
                        @endforeach
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="flex:1;background:#e2e8f0;border-radius:999px;height:5px;overflow:hidden;">
                            <div style="height:5px;border-radius:999px;width:{{ $pct }}%;background:{{ $pct===100?'#059669':'#d97706' }};"></div>
                        </div>
                        <span style="font-size:11px;color:#94a3b8;font-family:monospace;">{{ $approved }}/{{ $total }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        @elseif($user->isOfficer())
        <div class="glow-card">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#059669;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #d1fae5;">Officer Activity</p>
            @php
            $reviewed = $user->approvals()->count();
            $appApproved = $user->approvals()->where('status','approved')->count();
            $appRejected = $user->approvals()->where('status','rejected')->count();
            @endphp
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                @foreach([
                    ['Total Reviewed', $reviewed, '#059669', '#f0fdf4'],
                    ['Approved',       $appApproved, '#10b981', '#f0fdf4'],
                    ['Rejected',       $appRejected, '#ef4444', '#fef2f2'],
                ] as $stat)
                <div style="background:{{ $stat[3] }};border-radius:10px;padding:14px 16px;text-align:center;">
                    <p style="font-size:22px;font-weight:800;color:{{ $stat[2] }};margin-bottom:4px;">{{ $stat[1] }}</p>
                    <p style="font-size:11px;color:#64748b;">{{ $stat[0] }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="glow-card">
            <p style="font-size:13px;color:#64748b;">Admin accounts have full system access.</p>
        </div>
        @endif
    </div>

</div>

<a href="{{ route('admin.users.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    Back to Users
</a>
@endsection
