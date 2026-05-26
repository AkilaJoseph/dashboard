@extends('layouts.app')

@section('title', 'Departments')
@section('page-title', 'Clearance Departments')
@section('page-subtitle', 'Manage university departments involved in the clearance process')

@section('content')
<style>
.dept-row{
    display:grid;grid-template-columns:50px 1.5fr 80px 1.5fr 60px 80px 90px;
    align-items:center;gap:10px;padding:13px 18px;border-radius:9px;
    border:1px solid #e2e8f0;background:#fff;
    transition:all 0.15s;margin-bottom:6px;
}
.dept-row:hover{border-color:#a7f3d0;background:#f0fdf4;}
@media(max-width:768px){
    .dept-row{grid-template-columns:50px 1fr 90px;}
    .d-hide{display:none;}
}
</style>

<div style="display:flex;justify-content:flex-end;margin-bottom:18px;">
    <a href="{{ route('admin.departments.create') }}" class="btn-glow" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Add Department
    </a>
</div>

<div class="glow-card" style="padding:0;overflow:hidden;">
    <!-- Header -->
    <div style="background:#f8fafc;border-bottom:2px solid #e2e8f0;padding:10px 20px;">
        <div class="dept-row" style="background:transparent;border:none;margin:0;padding:0;">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">#</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Department</span>
            <span class="d-hide" style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Code</span>
            <span class="d-hide" style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Description</span>
            <span class="d-hide" style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Officers</span>
            <span class="d-hide" style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Status</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;">Actions</span>
        </div>
    </div>

    <div style="padding:12px 18px 18px;">
        @forelse($departments as $dept)
        <div class="dept-row">
            <div style="width:32px;height:32px;border-radius:8px;background:#059669;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;">
                {{ $dept->priority }}
            </div>
            <p style="font-size:13px;font-weight:600;color:#1e293b;">{{ $dept->name }}</p>
            <div class="d-hide">
                <span style="font-size:11px;font-family:monospace;font-weight:700;background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:3px 9px;border-radius:6px;">{{ $dept->code }}</span>
            </div>
            <p class="d-hide" style="font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($dept->description, 70) }}</p>
            <p class="d-hide" style="font-size:13px;font-weight:700;color:#1e293b;text-align:center;">{{ $dept->officers_count ?? 0 }}</p>
            <div class="d-hide">
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;{{ $dept->is_active ? 'background:#d1fae5;border:1px solid #a7f3d0;color:#065f46;' : 'background:#f1f5f9;border:1px solid #cbd5e1;color:#94a3b8;' }}">
                    {{ $dept->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:7px;">
                <a href="{{ route('admin.departments.edit', $dept) }}"
                   style="font-size:11px;font-weight:600;color:#059669;text-decoration:none;border:1px solid #a7f3d0;background:#f0fdf4;padding:4px 10px;border-radius:6px;transition:all 0.15s;"
                   onmouseover="this.style.background='#d1fae5'" onmouseout="this.style.background='#f0fdf4'">Edit</a>
                <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Delete {{ $dept->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="font-size:11px;font-weight:600;color:#ef4444;background:#fff;border:1px solid #fecaca;padding:4px 10px;border-radius:6px;cursor:pointer;transition:all 0.15s;"
                            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">Del</button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:48px;">
            <p style="color:#94a3b8;font-size:13px;">No departments configured.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
