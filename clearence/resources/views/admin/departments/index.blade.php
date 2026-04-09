@extends('layouts.app')

@section('title', 'Departments')
@section('page-title', 'Clearance Departments')
@section('page-subtitle', 'Manage university departments involved in the clearance process')

@section('content')
<style>
.dept-row{
    display:grid;grid-template-columns:50px 1.5fr 80px 1.5fr 60px 80px 90px;
    align-items:center;gap:10px;padding:13px 18px;border-radius:11px;
    border:1px solid rgba(16,185,129,0.1);background:rgba(16,185,129,0.02);
    transition:all 0.25s;margin-bottom:7px;
}
.dept-row:hover{border-color:rgba(16,185,129,0.28);background:rgba(16,185,129,0.05);}
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
    <div style="padding:14px 20px 8px;border-bottom:1px solid rgba(16,185,129,0.1);">
        <div class="dept-row" style="background:transparent;border:none;margin:0;padding:0;">
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">#</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Department</span>
            <span class="d-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Code</span>
            <span class="d-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Description</span>
            <span class="d-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Officers</span>
            <span class="d-hide" style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Status</span>
            <span style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.4);">Actions</span>
        </div>
    </div>

    <div style="padding:12px 18px 18px;">
        @forelse($departments as $dept)
        <div class="dept-row">
            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;color:#020904;box-shadow:0 0 14px rgba(16,185,129,0.4);">
                {{ $dept->priority }}
            </div>
            <p style="font-size:13px;font-weight:700;color:#e2e8f0;">{{ $dept->name }}</p>
            <div class="d-hide">
                <span style="font-size:11px;font-family:monospace;font-weight:700;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;padding:3px 10px;border-radius:6px;">{{ $dept->code }}</span>
            </div>
            <p class="d-hide" style="font-size:11px;color:rgba(160,200,175,0.5);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($dept->description, 70) }}</p>
            <p class="d-hide" style="font-size:13px;font-weight:700;color:#e2e8f0;text-align:center;">{{ $dept->officers_count ?? 0 }}</p>
            <div class="d-hide">
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;{{ $dept->is_active ? 'background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);color:#34d399;' : 'background:rgba(160,200,175,0.08);border:1px solid rgba(160,200,175,0.15);color:rgba(160,200,175,0.4);' }}">
                    {{ $dept->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <a href="{{ route('admin.departments.edit', $dept) }}"
                   style="font-size:11px;font-weight:700;color:#34d399;text-decoration:none;border:1px solid rgba(16,185,129,0.3);padding:4px 10px;border-radius:6px;transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(16,185,129,0.1)'" onmouseout="this.style.background='transparent'">EDIT</a>
                <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Delete {{ $dept->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="font-size:11px;font-weight:700;color:rgba(248,113,113,0.6);background:transparent;border:1px solid rgba(239,68,68,0.2);padding:4px 10px;border-radius:6px;cursor:pointer;transition:all 0.2s;"
                            onmouseover="this.style.background='rgba(239,68,68,0.08)'" onmouseout="this.style.background='transparent'">DEL</button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:48px;">
            <p style="color:rgba(160,200,175,0.4);font-size:13px;">No departments configured.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
