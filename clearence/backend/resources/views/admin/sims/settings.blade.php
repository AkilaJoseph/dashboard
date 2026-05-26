@extends('layouts.app')
@section('title','SIMS Connection')
@section('page-title','SIMS Integration')
@section('page-subtitle','Configure web scraping connection to sims.must.ac.tz')

@section('content')
<div style="max-width:680px;margin:0 auto;display:flex;flex-direction:column;gap:18px;">

    {{-- Status --}}
    <div class="glow-card" style="padding:16px 20px;display:flex;align-items:center;gap:14px;">
        @if($isConfigured)
        <div style="width:42px;height:42px;border-radius:10px;background:#d1fae5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="19" height="19" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        </div>
        <div>
            <p style="font-size:13px;font-weight:700;color:#065f46;margin:0;">SIMS Credentials Saved</p>
            <p style="font-size:11px;color:var(--text-muted);margin:2px 0 0;">{{ $simsUrl }} — scraping enabled</p>
        </div>
        @else
        <div style="width:42px;height:42px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="19" height="19" fill="none" stroke="#d97706" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
            <p style="font-size:13px;font-weight:700;color:#92400e;margin:0;">Not Configured</p>
            <p style="font-size:11px;color:var(--text-muted);margin:2px 0 0;">Enter SIMS credentials below to enable student sync.</p>
        </div>
        @endif
    </div>

    {{-- Form --}}
    <div class="glow-card">
        <h3 style="font-size:14px;font-weight:700;color:var(--text);margin:0 0 18px;padding-bottom:12px;border-bottom:1px solid var(--border);">SIMS Web Scraping Configuration</h3>

        <form method="POST" action="{{ route('admin.sims.settings.save') }}">
            @csrf

            {{-- URLs --}}
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin:0 0 10px;">URLs</p>

            <div style="margin-bottom:13px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">SIMS Base URL *</label>
                <input type="url" name="sims_url" value="{{ old('sims_url', $simsUrl) }}" class="glow-input" placeholder="https://sims.must.ac.tz" required>
                @error('sims_url')<p style="color:#ef4444;font-size:11px;margin-top:3px;">{{ $message }}</p>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:13px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Login Submit URL <span style="font-weight:400;">(POST target)</span></label>
                    <input type="url" name="sims_login_url" value="{{ old('sims_login_url', $loginUrl) }}" class="glow-input" placeholder="https://sims.must.ac.tz/logincheck">
                    <p style="font-size:10px;color:var(--text-muted);margin-top:3px;">Leave blank to use base URL + /logincheck</p>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Profile Page Path *</label>
                    <input type="text" name="sims_profile_path" value="{{ old('sims_profile_path', $profilePath) }}" class="glow-input" placeholder="/studentprofile/" required>
                    <p style="font-size:10px;color:var(--text-muted);margin-top:3px;">Appended as <code style="background:#f1f5f9;padding:1px 4px;border-radius:3px;">{path}?regNo=...</code></p>
                </div>
            </div>

            <div style="height:1px;background:var(--border);margin:16px 0;"></div>

            {{-- Credentials --}}
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin:0 0 10px;">Admin Login Credentials</p>
            <p style="font-size:11px;color:#d97706;background:#fef3c7;border:1px solid #fde68a;padding:8px 12px;border-radius:6px;margin-bottom:12px;">
                ⚠ Use a dedicated SIMS admin account, not your personal credentials.
            </p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:13px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">SIMS Username *</label>
                    <input type="text" name="sims_username" value="{{ old('sims_username', $simsUsername) }}" class="glow-input" placeholder="admin@must.ac.tz" required autocomplete="off">
                    @error('sims_username')<p style="color:#ef4444;font-size:11px;margin-top:3px;">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">SIMS Password *</label>
                    <input type="password" name="sims_password" value="{{ old('sims_password', $simsPassword) }}" class="glow-input" placeholder="••••••••" required autocomplete="new-password">
                    @error('sims_password')<p style="color:#ef4444;font-size:11px;margin-top:3px;">{{ $message }}</p>@enderror
                </div>
            </div>

            <div style="height:1px;background:var(--border);margin:16px 0;"></div>

            {{-- Form field names --}}
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin:0 0 10px;">Login Form Field Names
                <span style="font-weight:400;text-transform:none;letter-spacing:0;">(check SIMS page source if unsure)</span>
            </p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Username Field Name *</label>
                    <input type="text" name="sims_username_field" value="{{ old('sims_username_field', $usernameField) }}" class="glow-input" placeholder="username" required>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Password Field Name *</label>
                    <input type="text" name="sims_password_field" value="{{ old('sims_password_field', $passwordField) }}" class="glow-input" placeholder="password" required>
                </div>
            </div>

            <button type="submit" class="btn-glow">Save & Enable SIMS Scraping</button>
        </form>
    </div>

    {{-- How it works --}}
    <div class="glow-card" style="background:#f0fdf4;border-color:#a7f3d0;">
        <p style="font-size:12px;font-weight:700;color:#065f46;margin:0 0 10px;">How Web Scraping Works</p>
        <ol style="font-size:12px;color:#374151;line-height:2.1;padding-left:18px;margin:0;">
            <li>System GETs the SIMS login page to collect the session cookie + hidden form fields</li>
            <li>POSTs your credentials to the login URL</li>
            <li>GETs <code style="background:#d1fae5;padding:1px 5px;border-radius:3px;">{base_url}{profile_path}?regNo=REG_NO</code> with the session cookie</li>
            <li>Parses all table rows on the profile page into label → value pairs</li>
            <li>Maps known labels (First Name, Reg No, Programme, etc.) to student fields</li>
        </ol>
    </div>

    <a href="{{ route('admin.sims.sync') }}" class="btn-glow" style="align-self:flex-start;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        Go to Student Sync →
    </a>
</div>
@endsection
