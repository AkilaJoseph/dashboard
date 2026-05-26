@extends('layouts.app')
@section('title', 'Edit Department')
@section('page-title', 'Edit Department')
@section('page-subtitle', 'Update department details and configuration')
@section('content')
<div style="max-width:560px;margin:0 auto;">
<div class="glow-card" style="padding:0;overflow:hidden;">
    <div style="padding:18px 24px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;">
        <div style="width:36px;height:36px;border-radius:8px;background:#059669;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;">{{ $department->priority }}</div>
        <div>
            <h3 style="font-size:14px;font-weight:700;color:#1e293b;">{{ $department->name }}</h3>
            <p style="font-size:11px;color:#64748b;margin-top:2px;">Code: <span style="color:#d97706;font-family:monospace;font-weight:700;">{{ $department->code }}</span></p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.departments.update', $department) }}" style="padding:24px;">
        @csrf @method('PUT')
        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #ef4444;border-radius:0 8px 8px 0;padding:12px 16px;margin-bottom:18px;">
            @foreach($errors->all() as $e)<p style="font-size:12px;color:#991b1b;margin-bottom:3px;">{{ $e }}</p>@endforeach
        </div>
        @endif
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Department Name <span style="color:#ef4444;">*</span></label>
            <input type="text" name="name" required value="{{ old('name',$department->name) }}" class="glow-input" style="width:100%;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Code <span style="color:#ef4444;">*</span></label>
                <input type="text" name="code" required value="{{ old('code',$department->code) }}" class="glow-input" style="width:100%;text-transform:uppercase;">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Priority <span style="color:#ef4444;">*</span></label>
                <input type="number" name="priority" required min="1" value="{{ old('priority',$department->priority) }}" class="glow-input" style="width:100%;">
            </div>
        </div>
        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Description</label>
            <textarea name="description" rows="3" class="glow-input" style="width:100%;resize:none;">{{ old('description',$department->description) }}</textarea>
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Status</label>
            <select name="is_active" class="glow-input" style="width:100%;">
                <option value="1" {{ $department->is_active ? 'selected' : '' }}>Active</option>
                <option value="0" {{ !$department->is_active ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid #f1f5f9;">
            <a href="{{ route('admin.departments.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Cancel</a>
            <button type="submit" class="btn-glow">Save Changes</button>
        </div>
    </form>
</div>
</div>
@endsection
