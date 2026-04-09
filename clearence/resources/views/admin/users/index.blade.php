@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage students, department officers, and administrators')

@section('content')
<style>
.user-row{
    display:grid;grid-template-columns:2fr 1.5fr 90px 1.2fr 80px 100px;
    align-items:center;gap:10px;padding:13px 18px;border-radius:11px;
    border:1px solid rgba(16,185,129,0.1);background:rgba(16,185,129,0.02);
    transition:all 0.25s;margin-bottom:7px;
}
.user-row:hover{border-color:rgba(16,185,129,0.28);background:rgba(16,185,129,0.05);}
.role-admin{background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;}
.role-officer{background:rgba(139,92,246,0.12);border:1px solid rgba(139,92,246,0.3);color:#c4b5fd;}
.role-student{background:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.3);color:#93c5fd;}
.role-badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;letter-spacing:0.04em;text-transform:capitalize;}
@media(max-width:768px){
    .user-row{grid-template-columns:1fr 80px;grid-template-rows:auto auto;}
    .u-hide{display:none;}
}
</style>

<div style="display:flex;justify-content:flex-end;margin-bottom:18px;">
    <a href="{{ route('admin.users.create') }}" class="btn-glow" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Add New User
    </a>
</div>

<div class="glow-card" style="padding:0;overflow:hidden;">
    <!-- Header -->
    <div style="padding:14px 20px 8px;border-bottom:1px solid rgba(16,185,129,0.1);">
        <div class="user-row" style="background:transparent;border:none;margin:0;padding:0;">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Name</span>
            <span class="u-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Email</span>
            <span class="u-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Role</span>
            <span class="u-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Dept / Programme</span>
            <span class="u-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Status</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Actions</span>
        </div>
    </div>

    <div style="padding:12px 18px 18px;">
        @forelse($users as $user)
        <div class="user-row">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:900;flex-shrink:0;
                    {{ $user->role==='admin' ? 'background:linear-gradient(135deg,#d97706,#f59e0b);color:#020904;box-shadow:0 0 14px rgba(245,158,11,0.4);' :
                       ($user->role==='officer' ? 'background:linear-gradient(135deg,#7c3aed,#8b5cf6);color:white;box-shadow:0 0 12px rgba(139,92,246,0.4);' :
                       'background:linear-gradient(135deg,#1d4ed8,#3b82f6);color:white;box-shadow:0 0 12px rgba(59,130,246,0.3);') }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p style="font-size:13px;font-weight:700;color:#e2e8f0;margin-bottom:2px;">{{ $user->name }}</p>
                    @if($user->student_id)
                    <p style="font-size:10px;color:rgba(160,200,175,0.4);font-family:monospace;">{{ $user->student_id }}</p>
                    @endif
                </div>
            </div>

            <div class="u-hide" style="font-size:11px;color:rgba(160,200,175,0.5);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $user->email }}</div>

            <div class="u-hide">
                <span class="role-badge {{ $user->role==='admin' ? 'role-admin' : ($user->role==='officer' ? 'role-officer' : 'role-student') }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>

            <div class="u-hide" style="font-size:11px;color:rgba(160,200,175,0.55);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $user->department?->name ?? $user->programme ?? '—' }}
            </div>

            <div class="u-hide">
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;{{ $user->is_active ? 'background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);color:#34d399;' : 'background:rgba(160,200,175,0.08);border:1px solid rgba(160,200,175,0.15);color:rgba(160,200,175,0.4);' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div style="display:flex;align-items:center;gap:8px;">
                <a href="{{ route('admin.users.edit', $user) }}"
                   style="font-size:11px;font-weight:700;color:#34d399;text-decoration:none;border:1px solid rgba(16,185,129,0.3);padding:4px 10px;border-radius:6px;transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(16,185,129,0.1)'" onmouseout="this.style.background='transparent'">EDIT</a>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="font-size:11px;font-weight:700;color:rgba(248,113,113,0.6);background:transparent;border:1px solid rgba(239,68,68,0.2);padding:4px 10px;border-radius:6px;cursor:pointer;transition:all 0.2s;"
                            onmouseover="this.style.background='rgba(239,68,68,0.08)';this.style.color='#f87171';" onmouseout="this.style.background='transparent';this.style.color='rgba(248,113,113,0.6)';">DEL</button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:48px;">
            <p style="color:rgba(160,200,175,0.4);font-size:13px;">No users found.</p>
        </div>
        @endforelse
    </div>

    <div style="padding:12px 20px;border-top:1px solid rgba(16,185,129,0.08);">
        {{ $users->links() }}
    </div>
</div>
@endsection
