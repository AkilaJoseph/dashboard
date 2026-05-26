@extends('layouts.app')
@section('title', 'Add Department')
@section('page-title', 'Add Department')
@section('page-subtitle', 'Create a new clearance department for MUST')
@section('content')
<div style="max-width:560px;margin:0 auto;">
<div class="glow-card" style="padding:0;overflow:hidden;">
    <div style="padding:18px 24px;background:linear-gradient(135deg,#064e3b,#059669);">
        <h3 style="font-size:14px;font-weight:700;color:#fff;">New Clearance Department</h3>
        <p style="font-size:11px;color:rgba(209,250,229,0.75);margin-top:3px;">Mbeya University of Science and Technology</p>
    </div>
    <form method="POST" action="{{ route('admin.departments.store') }}" style="padding:24px;">
        @csrf
        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #ef4444;border-radius:0 8px 8px 0;padding:12px 16px;margin-bottom:18px;">
            @foreach($errors->all() as $e)<p style="font-size:12px;color:#991b1b;margin-bottom:3px;">{{ $e }}</p>@endforeach
        </div>
        @endif
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Department Name <span style="color:#ef4444;">*</span></label>
            <input type="text" name="name" required value="{{ old('name') }}" class="glow-input" style="width:100%;" placeholder="e.g. Finance &amp; Accounts Office">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Code <span style="color:#ef4444;">*</span></label>
                <input type="text" name="code" required value="{{ old('code') }}" class="glow-input" style="width:100%;text-transform:uppercase;" placeholder="FIN">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Priority Order <span style="color:#ef4444;">*</span></label>
                <input type="number" name="priority" required min="1" value="{{ old('priority',1) }}" class="glow-input" style="width:100%;">
            </div>
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Description</label>
            <textarea name="description" rows="3" class="glow-input" style="width:100%;resize:none;"
                      placeholder="Describe what this department checks during clearance...">{{ old('description') }}</textarea>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid #f1f5f9;">
            <a href="{{ route('admin.departments.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Cancel</a>
            <button type="submit" class="btn-glow">Create Department</button>
        </div>
    </form>
</div>
</div>
@endsection
