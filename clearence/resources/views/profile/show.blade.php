@extends('layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Manage your account information')

@section('content')
<div style="max-width:560px;margin:0 auto;">
    <div class="glow-card">
        <!-- Avatar -->
        <div style="text-align:center;margin-bottom:24px;">
            <div style="width:72px;height:72px;border-radius:50%;background:var(--green);display:inline-flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#fff;margin-bottom:10px;">
                {{ strtoupper(substr($user->name,0,1)) }}
            </div>
            <p style="font-size:11px;color:var(--text-muted);margin:0;">{{ ucfirst($user->role) }}</p>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="glow-input" required>
                @error('name')<p style="color:#ef4444;font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="glow-input" required>
                @error('email')<p style="color:#ef4444;font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>

            @if($user->isStudent())
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Student ID</label>
                <input type="text" value="{{ $user->student_id ?? '—' }}" class="glow-input" disabled style="opacity:0.6;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Programme</label>
                <input type="text" value="{{ $user->programme ?? '—' }}" class="glow-input" disabled style="opacity:0.6;">
            </div>
            @endif

            <div style="border-top:1px solid var(--border);padding-top:14px;margin:18px 0 14px;">
                <p style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:12px;">Change Password <span style="font-weight:400;">(leave blank to keep current)</span></p>
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">New Password</label>
                    <input type="password" name="password" class="glow-input" placeholder="Min. 8 characters">
                    @error('password')<p style="color:#ef4444;font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="glow-input">
                </div>
            </div>

            <button type="submit" class="btn-glow" style="width:100%;justify-content:center;">Save Changes</button>
        </form>
    </div>
</div>
@endsection
