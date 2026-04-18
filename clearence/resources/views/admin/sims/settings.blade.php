@extends('layouts.app')
@section('title','SIMS Connection')
@section('page-title','SIMS Integration')
@section('page-subtitle','Configure connection to the Student Information Management System')

@section('content')
<div style="max-width:640px;margin:0 auto;display:flex;flex-direction:column;gap:18px;">

    {{-- Status card --}}
    <div class="glow-card" style="padding:16px 20px;display:flex;align-items:center;gap:14px;">
        @if($apiUrl)
        <div style="width:40px;height:40px;border-radius:10px;background:#d1fae5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="18" height="18" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        </div>
        <div>
            <p style="font-size:13px;font-weight:700;color:#065f46;margin:0;">SIMS Connected</p>
            <p style="font-size:11px;color:var(--text-muted);margin:2px 0 0;">{{ $apiUrl }}</p>
        </div>
        @else
        <div style="width:40px;height:40px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
            <p style="font-size:13px;font-weight:700;color:#92400e;margin:0;">Not Configured</p>
            <p style="font-size:11px;color:var(--text-muted);margin:2px 0 0;">Enter the SIMS API URL below to connect.</p>
        </div>
        @endif
    </div>

    {{-- Settings form --}}
    <div class="glow-card">
        <h3 style="font-size:14px;font-weight:700;color:var(--text);margin:0 0 18px;padding-bottom:12px;border-bottom:1px solid var(--border);">API Connection Settings</h3>
        <form method="POST" action="{{ route('admin.sims.settings.save') }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">SIMS API Base URL</label>
                <input type="url" name="sims_api_url" value="{{ old('sims_api_url', $apiUrl) }}" class="glow-input" placeholder="https://sims.must.ac.tz" required>
                <p style="font-size:10px;color:var(--text-muted);margin-top:4px;">The system will call <code style="background:#f1f5f9;padding:1px 5px;border-radius:3px;">{base_url}/api/student?regNo=...</code></p>
                @error('sims_api_url')<p style="color:#ef4444;font-size:11px;margin-top:4px;">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:18px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">API Key / Bearer Token <span style="font-weight:400;">(optional)</span></label>
                <input type="text" name="sims_api_key" value="{{ old('sims_api_key', $apiKey) }}" class="glow-input" placeholder="sk-xxxxxxxxxxxxxxxx">
            </div>
            <button type="submit" class="btn-glow">Save Connection Settings</button>
        </form>
    </div>

    {{-- Info --}}
    <div class="glow-card" style="background:#f0fdf4;border-color:#a7f3d0;">
        <p style="font-size:12px;font-weight:700;color:#065f46;margin:0 0 8px;">How it works</p>
        <ul style="font-size:12px;color:#374151;line-height:2;padding-left:18px;margin:0;">
            <li>Admin enters the SIMS API base URL above</li>
            <li>When syncing a student, the system calls: <code style="background:#d1fae5;padding:1px 5px;border-radius:3px;">GET {base_url}/api/student?regNo=REG_NO</code></li>
            <li>Student data is imported automatically — students <strong>cannot edit</strong> their own info</li>
            <li>Admin can re-sync any student at any time from the Users page</li>
        </ul>
    </div>

    <a href="{{ route('admin.sims.sync') }}" class="btn-glow" style="align-self:flex-start;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        Go to Student Sync
    </a>
</div>
@endsection
