@extends('layouts.app')

@section('title', 'Submit Clearance Request')
@section('page-title', 'Submit Clearance Request')
@section('page-subtitle', 'Initiate your clearance process across all MUST departments')

@section('content')
<div style="max-width:620px;margin:0 auto;">

<div class="glow-card" style="padding:0;overflow:hidden;">

    <div style="padding:18px 24px;border-bottom:1px solid #f1f5f9;background:linear-gradient(135deg,#064e3b,#059669);">
        <h3 style="font-size:15px;font-weight:700;color:#fff;">New Clearance Request &mdash; MUST</h3>
        <p style="font-size:11px;color:rgba(209,250,229,0.75);margin-top:3px;">Mbeya University of Science and Technology</p>
    </div>

    <form id="clearance-form" method="POST" action="{{ route('student.clearances.store') }}" enctype="multipart/form-data" style="padding:26px;">
        @csrf

        {{-- Offline status indicator — shown/hidden by offline-form.js --}}
        <div id="offline-status" style="display:none;"></div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #ef4444;border-radius:0 8px 8px 0;padding:12px 16px;margin-bottom:20px;">
            @foreach($errors->all() as $error)
            <p style="font-size:12px;color:#991b1b;margin-bottom:3px;">{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Academic Year <span style="color:#ef4444;">*</span></label>
                <select name="academic_year" required class="glow-input">
                    <option value="">-- Select Year --</option>
                    @php
                        $currentYear = date('Y');
                        for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                            $label = $y . '/' . ($y + 1);
                            $sel   = old('academic_year') === $label ? 'selected' : '';
                            echo "<option value=\"$label\" $sel>$label</option>";
                        }
                    @endphp
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Semester <span style="color:#ef4444;">*</span></label>
                <select name="semester" required class="glow-input">
                    <option value="">-- Select --</option>
                    <option value="First"  {{ old('semester')==='First'  ? 'selected' : '' }}>First Semester</option>
                    <option value="Second" {{ old('semester')==='Second' ? 'selected' : '' }}>Second Semester</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Clearance Type <span style="color:#ef4444;">*</span></label>
            <select name="clearance_type" required class="glow-input">
                <option value="">-- Select Type --</option>
                <option value="graduation"  {{ old('clearance_type')==='graduation'  ? 'selected' : '' }}>Graduation Clearance</option>
                <option value="semester"    {{ old('clearance_type')==='semester'    ? 'selected' : '' }}>End of Semester Clearance</option>
                <option value="withdrawal"  {{ old('clearance_type')==='withdrawal'  ? 'selected' : '' }}>Withdrawal Clearance</option>
                <option value="transfer"    {{ old('clearance_type')==='transfer'    ? 'selected' : '' }}>Transfer Clearance</option>
            </select>
        </div>

        <div style="margin-bottom:22px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Additional Notes / Remarks</label>
            <textarea name="reason" rows="4" class="glow-input" style="resize:none;"
                      placeholder="Provide any additional information relevant to your clearance request (optional)...">{{ old('reason') }}</textarea>
        </div>

        <!-- Supporting Documents -->
        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">
                Supporting Documents
                <span style="font-size:10px;font-weight:400;text-transform:none;color:#94a3b8;letter-spacing:0;">(optional — up to 5 files)</span>
            </label>
            <input type="file" name="files[]" id="clearance-files" multiple
                   accept=".pdf,.jpg,.jpeg,.png"
                   class="glow-input"
                   style="padding:7px 10px;cursor:pointer;"
                   onchange="updateFileList(this)">
            <p style="font-size:11px;color:#94a3b8;margin-top:5px;">Accepted: PDF, JPG, PNG &nbsp;&middot;&nbsp; Max 5&nbsp;MB per file &nbsp;&middot;&nbsp; Max 5 files</p>
            <ul id="file-list" style="list-style:none;padding:0;margin:8px 0 0;"></ul>
            @error('files')
                <p style="font-size:11px;color:#dc2626;margin-top:4px;">{{ $message }}</p>
            @enderror
            @error('files.*')
                <p style="font-size:11px;color:#dc2626;margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Departments preview -->
        <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:10px;padding:16px;margin-bottom:24px;">
            <p style="font-size:11px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:12px;">Your request will be reviewed by these departments:</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                @foreach(\App\Models\Department::where('is_active', true)->orderBy('priority')->get() as $dept)
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:6px;height:6px;border-radius:50%;background:#059669;flex-shrink:0;"></div>
                    <span style="font-size:12px;color:#374151;">{{ $dept->name }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Declaration -->
        <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:10px;padding:14px 16px;margin-bottom:22px;">
            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                <input type="checkbox" name="declaration" value="1"
                       {{ old('declaration') ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:#059669;flex-shrink:0;margin-top:2px;">
                <span style="font-size:12px;color:#374151;line-height:1.6;">
                    I declare that all information provided in this clearance request is accurate and complete to the best of my knowledge. I understand that providing false information may result in rejection of my clearance or disciplinary action.
                    <span style="color:#ef4444;font-weight:700;">*</span>
                </span>
            </label>
            @error('declaration')
                <p style="font-size:11px;color:#dc2626;margin-top:6px;margin-left:26px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid #f1f5f9;">
            <a href="{{ route('student.clearances.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Cancel</a>
            <button type="submit" class="btn-glow">Submit Clearance Request</button>
        </div>
    </form>
</div>

</div>

@push('scripts')
<script>
function updateFileList(input) {
    const list = document.getElementById('file-list');
    list.innerHTML = '';
    const icons = { 'application/pdf': '📄', 'image/jpeg': '🖼', 'image/png': '🖼', 'image/jpg': '🖼' };
    Array.from(input.files).slice(0, 5).forEach(function(f) {
        const li = document.createElement('li');
        li.style.cssText = 'font-size:12px;color:#374151;padding:4px 0;display:flex;align-items:center;gap:6px;';
        const size = f.size >= 1048576 ? (f.size/1048576).toFixed(1)+' MB' : Math.round(f.size/1024)+' KB';
        li.innerHTML = '<span>' + (icons[f.type] || '📎') + '</span><span style="font-weight:600;">' + f.name + '</span><span style="color:#94a3b8;">(' + size + ')</span>';
        list.appendChild(li);
    });
}
</script>
@endpush
@endsection
