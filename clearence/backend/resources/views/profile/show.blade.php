@extends('layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Your academic information from SIMS')

@section('content')
<div style="max-width:680px;margin:0 auto;display:flex;flex-direction:column;gap:18px;">

    {{-- Identity card --}}
    <div class="glow-card" style="display:flex;align-items:center;gap:18px;padding:20px 24px;">
        <div style="width:60px;height:60px;border-radius:14px;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;flex-shrink:0;">
            {{ strtoupper(substr($user->name,0,1)) }}
        </div>
        <div style="flex:1;min-width:0;">
            <p style="font-size:16px;font-weight:700;color:var(--text);margin:0;">{{ strtoupper($user->name) }}</p>
            <p style="font-size:12px;color:var(--text-muted);margin:3px 0 0;">{{ $user->entry_programme ?? $user->programme ?? ucfirst($user->role) }}</p>
            @if($user->sims_synced_at)
            <p style="font-size:10px;color:var(--green);margin:4px 0 0;display:flex;align-items:center;gap:4px;">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                Synced from SIMS · {{ $user->sims_synced_at->format('d M Y') }}
            </p>
            @else
            <p style="font-size:10px;color:#d97706;margin:4px 0 0;">⚠ Not yet synced from SIMS</p>
            @endif
        </div>
        @if($user->registration_number)
        <div style="text-align:right;flex-shrink:0;">
            <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);margin:0 0 3px;">Reg No</p>
            <p style="font-size:14px;font-weight:800;color:#d97706;font-family:monospace;margin:0;">{{ $user->registration_number }}</p>
        </div>
        @endif
    </div>

    {{-- University Information (read-only from SIMS) --}}
    <div class="glow-card" style="padding:0;overflow:hidden;">
        <div style="padding:12px 20px;background:var(--green);display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:12px;font-weight:700;color:#d1fae5;text-transform:uppercase;letter-spacing:0.08em;margin:0;">University Information</p>
            <span style="font-size:10px;color:rgba(209,250,229,0.6);background:rgba(0,0,0,0.15);padding:2px 8px;border-radius:999px;">From SIMS — Read Only</span>
        </div>
        <div style="padding:16px 20px;">
            @php
            $uniFields = [
                'Campus'          => $user->campus,
                'Registration No' => $user->registration_number,
                'Admission No'    => $user->admission_number,
                'Entry Year'      => $user->entry_year,
                'Programme'       => $user->entry_programme ?? $user->programme,
                'Entry Category'  => $user->entry_category,
                'Year of Study'   => $user->year_of_study,
                'College'         => $user->college,
            ];
            @endphp
            <table style="width:100%;border-collapse:collapse;">
            @foreach($uniFields as $label => $value)
            @if($value)
            <tr>
                <td style="font-size:12px;font-weight:600;color:var(--text-muted);padding:6px 0;width:45%;border-bottom:1px solid #f8fafc;">{{ $label }}</td>
                <td style="font-size:13px;color:var(--text);padding:6px 0;border-bottom:1px solid #f8fafc;font-weight:500;">{{ $value }}</td>
            </tr>
            @endif
            @endforeach
            </table>
        </div>
    </div>

    {{-- Personal Information (read-only from SIMS) --}}
    <div class="glow-card" style="padding:0;overflow:hidden;">
        <div style="padding:12px 20px;background:#065f46;display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:12px;font-weight:700;color:#d1fae5;text-transform:uppercase;letter-spacing:0.08em;margin:0;">Personal Basic Information</p>
            <span style="font-size:10px;color:rgba(209,250,229,0.6);background:rgba(0,0,0,0.15);padding:2px 8px;border-radius:999px;">From SIMS — Read Only</span>
        </div>
        <div style="padding:16px 20px;">
            @php
            $personalFields = [
                'First Name'  => $user->first_name,
                'Middle Name' => $user->middle_name,
                'Last Name'   => $user->last_name,
                'Gender'      => $user->gender,
                'Birth Date'  => $user->birth_date?->format('d-m-Y'),
                'Nationality' => $user->nationality,
                'Disability'  => $user->disability,
            ];
            @endphp
            <table style="width:100%;border-collapse:collapse;">
            @foreach($personalFields as $label => $value)
            @if($value)
            <tr>
                <td style="font-size:12px;font-weight:600;color:var(--text-muted);padding:6px 0;width:45%;border-bottom:1px solid #f8fafc;">{{ $label }}</td>
                <td style="font-size:13px;color:var(--text);padding:6px 0;border-bottom:1px solid #f8fafc;font-weight:500;">{{ $value }}</td>
            </tr>
            @endif
            @endforeach
            </table>
        </div>
    </div>

    {{-- Editable: email + password only --}}
    <div class="glow-card">
        <h3 style="font-size:14px;font-weight:700;color:var(--text);margin:0 0 4px;">Login Credentials</h3>
        <p style="font-size:12px;color:var(--text-muted);margin:0 0 18px;">You can only update your email address and password.</p>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="glow-input" required>
                @error('email')<p style="color:#ef4444;font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">New Password <span style="font-weight:400;">(leave blank to keep current)</span></label>
                <input type="password" name="password" class="glow-input" placeholder="Min. 8 characters">
                @error('password')<p style="color:#ef4444;font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:18px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Confirm Password</label>
                <input type="password" name="password_confirmation" class="glow-input">
            </div>
            <button type="submit" class="btn-glow">Update Credentials</button>
        </form>
    </div>

</div>
@endsection
