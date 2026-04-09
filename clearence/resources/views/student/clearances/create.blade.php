@extends('layouts.app')

@section('title', 'Submit Clearance Request')
@section('page-title', 'Submit Clearance Request')
@section('page-subtitle', 'Initiate your clearance process across all MUST departments')

@section('content')
<div style="max-width:640px;margin:0 auto;">

<div class="glow-card" style="padding:0;overflow:hidden;">

    <!-- Card Header -->
    <div style="padding:20px 28px;border-bottom:1px solid rgba(16,185,129,0.12);background:rgba(16,185,129,0.04);">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;box-shadow:0 0 20px rgba(16,185,129,0.4);">
                <svg style="width:18px;height:18px;color:#020904;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h3 style="font-size:15px;font-weight:800;color:#e2e8f0;">New Clearance Request &mdash; MUST</h3>
                <p style="font-size:11px;color:rgba(160,200,175,0.5);">Mbeya University of Science and Technology</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('student.clearances.store') }}" style="padding:28px;">
        @csrf

        @if($errors->any())
        <div style="background:rgba(239,68,68,0.08);border-left:3px solid #ef4444;border-radius:0 8px 8px 0;padding:12px 16px;margin-bottom:20px;">
            @foreach($errors->all() as $error)
            <p style="font-size:12px;color:#f87171;margin-bottom:4px;">&#9670; {{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.8);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:8px;">Academic Year <span style="color:#ef4444;">*</span></label>
                <select name="academic_year" required class="glow-input" style="width:100%;">
                    <option value="">-- Select Year --</option>
                    @php
                        $currentYear = date('Y');
                        for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                            $label    = $y . '/' . ($y + 1);
                            $selected = old('academic_year') === $label ? 'selected' : '';
                            echo "<option value=\"$label\" $selected>$label</option>";
                        }
                    @endphp
                </select>
            </div>

            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.8);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:8px;">Semester <span style="color:#ef4444;">*</span></label>
                <select name="semester" required class="glow-input" style="width:100%;">
                    <option value="">-- Select --</option>
                    <option value="First"  {{ old('semester')==='First'  ? 'selected' : '' }}>First Semester</option>
                    <option value="Second" {{ old('semester')==='Second' ? 'selected' : '' }}>Second Semester</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.8);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:8px;">Clearance Type <span style="color:#ef4444;">*</span></label>
            <select name="clearance_type" required class="glow-input" style="width:100%;">
                <option value="">-- Select Type --</option>
                <option value="graduation"  {{ old('clearance_type')==='graduation'  ? 'selected' : '' }}>Graduation Clearance</option>
                <option value="semester"    {{ old('clearance_type')==='semester'    ? 'selected' : '' }}>End of Semester Clearance</option>
                <option value="withdrawal"  {{ old('clearance_type')==='withdrawal'  ? 'selected' : '' }}>Withdrawal Clearance</option>
                <option value="transfer"    {{ old('clearance_type')==='transfer'    ? 'selected' : '' }}>Transfer Clearance</option>
            </select>
        </div>

        <div style="margin-bottom:24px;">
            <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.8);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:8px;">Additional Notes / Remarks</label>
            <textarea name="reason" rows="4" class="glow-input" style="width:100%;resize:none;"
                      placeholder="Provide any additional information relevant to your clearance request (optional)...">{{ old('reason') }}</textarea>
        </div>

        <!-- Department Preview -->
        <div style="background:rgba(16,185,129,0.04);border:1px solid rgba(16,185,129,0.15);border-radius:12px;padding:18px;margin-bottom:24px;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(245,158,11,0.7);margin-bottom:14px;">&#9670; Your request will be reviewed by these departments:</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                @foreach(\App\Models\Department::where('is_active', true)->orderBy('priority')->get() as $dept)
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:6px;height:6px;border-radius:50%;background:linear-gradient(135deg,#10b981,#34d399);box-shadow:0 0 8px rgba(16,185,129,0.6);flex-shrink:0;"></div>
                    <span style="font-size:11px;color:rgba(160,200,175,0.7);">{{ $dept->name }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;">
            <a href="{{ route('student.clearances.index') }}"
               style="font-size:12px;color:rgba(160,200,175,0.5);text-decoration:none;">&larr; Cancel</a>
            <button type="submit" class="btn-glow">Submit Clearance Request</button>
        </div>
    </form>
</div>

</div>
@endsection
