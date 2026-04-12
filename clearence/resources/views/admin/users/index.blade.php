@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage students, department officers, and administrators')

@section('content')
<style>
.user-row{
    display:grid;grid-template-columns:2fr 1.5fr 90px 1.2fr 80px 100px;
    align-items:center;gap:10px;padding:13px 18px;border-radius:9px;
    border:1px solid #e2e8f0;background:#fff;
    transition:all 0.15s;margin-bottom:6px;
}
.user-row:hover{border-color:#a7f3d0;background:#f0fdf4;}
.role-admin{background:#fffbeb;border:1px solid #fde68a;color:#92400e;}
.role-officer{background:#f5f3ff;border:1px solid #ddd6fe;color:#6d28d9;}
.role-student{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;}
.role-badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;letter-spacing:0.04em;text-transform:capitalize;}
@media(max-width:768px){
    .user-row{grid-template-columns:1fr 80px;}
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
    <div style="background:#f8fafc;border-bottom:2px solid #e2e8f0;padding:10px 20px;">
        <div class="user-row" style="background:transparent;border:none;margin:0;padding:0;">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Name</span>
            <span class="u-hide" style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Email</span>
            <span class="u-hide" style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Role</span>
            <span class="u-hide" style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Dept / Programme</span>
            <span class="u-hide" style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Status</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Actions</span>
        </div>
    </div>

    <div style="padding:12px 18px 18px;">
        @forelse($users as $user)
        <div class="user-row">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0;
                    {{ $user->role==='admin' ? 'background:#d97706;color:#fff;' :
                       ($user->role==='officer' ? 'background:#8b5cf6;color:#fff;' :
                       'background:#3b82f6;color:#fff;') }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p style="font-size:13px;font-weight:600;color:#1e293b;margin-bottom:2px;">{{ $user->name }}</p>
                    @if($user->student_id)
                    <p style="font-size:10px;color:#94a3b8;font-family:monospace;">{{ $user->student_id }}</p>
                    @endif
                </div>
            </div>

            <div class="u-hide" style="font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $user->email }}</div>

            <div class="u-hide">
                <span class="role-badge {{ $user->role==='admin' ? 'role-admin' : ($user->role==='officer' ? 'role-officer' : 'role-student') }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>

            <div class="u-hide" style="font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $user->department?->name ?? $user->programme ?? '—' }}
            </div>

            <div class="u-hide">
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;{{ $user->is_active ? 'background:#d1fae5;border:1px solid #a7f3d0;color:#065f46;' : 'background:#f1f5f9;border:1px solid #cbd5e1;color:#94a3b8;' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div style="display:flex;align-items:center;gap:7px;">
                <a href="{{ route('admin.users.show', $user) }}"
                   style="font-size:11px;font-weight:600;color:#3b82f6;text-decoration:none;border:1px solid #bfdbfe;background:#eff6ff;padding:4px 10px;border-radius:6px;transition:all 0.15s;"
                   onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">View</a>
                <a href="{{ route('admin.users.edit', $user) }}"
                   style="font-size:11px;font-weight:600;color:#059669;text-decoration:none;border:1px solid #a7f3d0;background:#f0fdf4;padding:4px 10px;border-radius:6px;transition:all 0.15s;"
                   onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#f0fdf4'">Edit</a>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="font-size:11px;font-weight:600;color:#ef4444;background:#fff;border:1px solid #fecaca;padding:4px 10px;border-radius:6px;cursor:pointer;transition:all 0.15s;"
                            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">Del</button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:48px;">
            <p style="color:#94a3b8;font-size:13px;">No users found.</p>
        </div>
        @endforelse
    </div>

    <div style="padding:12px 20px;border-top:1px solid #f1f5f9;">
        {{ $users->links() }}
    </div>
</div>
@endsection
