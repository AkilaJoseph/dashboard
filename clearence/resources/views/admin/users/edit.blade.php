@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', 'Update user information and role assignment')

@section('content')
<div style="max-width:680px;margin:0 auto;">
<div class="glow-card" style="padding:0;overflow:hidden;">

    <div style="padding:18px 26px;border-bottom:1px solid rgba(16,185,129,0.12);background:rgba(16,185,129,0.04);">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:38px;height:38px;border-radius:9px;background:linear-gradient(135deg,#d97706,#f59e0b);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:900;color:#020904;box-shadow:0 0 18px rgba(245,158,11,0.4);">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h3 style="font-size:14px;font-weight:800;color:#e2e8f0;">Edit: {{ $user->name }}</h3>
                <p style="font-size:11px;color:rgba(160,200,175,0.45);">{{ ucfirst($user->role) }} &mdash; MUST</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" style="padding:26px;">
        @csrf @method('PUT')

        @if($errors->any())
        <div style="background:rgba(239,68,68,0.08);border-left:3px solid #ef4444;border-radius:0 8px 8px 0;padding:12px 16px;margin-bottom:20px;">
            @foreach($errors->all() as $error)
            <p style="font-size:12px;color:#f87171;margin-bottom:3px;">&#9670; {{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">Full Name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" required value="{{ old('name',$user->name) }}" class="glow-input" style="width:100%;">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">Email <span style="color:#ef4444;">*</span></label>
                <input type="email" name="email" required value="{{ old('email',$user->email) }}" class="glow-input" style="width:100%;">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">Phone</label>
                <input type="text" name="phone" value="{{ old('phone',$user->phone) }}" class="glow-input" style="width:100%;" placeholder="+255 7XX XXX XXX">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">Role <span style="color:#ef4444;">*</span></label>
                <select name="role" required onchange="toggleFields()" class="glow-input" style="width:100%;" id="roleSelect">
                    <option value="student" {{ old('role',$user->role)==='student' ? 'selected' : '' }}>Student</option>
                    <option value="officer" {{ old('role',$user->role)==='officer' ? 'selected' : '' }}>Department Officer</option>
                    <option value="admin"   {{ old('role',$user->role)==='admin'   ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">Status</label>
                <select name="is_active" class="glow-input" style="width:100%;">
                    <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <!-- Student Fields -->
        <div id="studentFields" style="{{ old('role',$user->role)!=='student' ? 'display:none;' : '' }}">
            <div style="border-top:1px solid rgba(16,185,129,0.1);padding-top:18px;margin-bottom:18px;">
                <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(245,158,11,0.6);margin-bottom:16px;">&#9670; Student Details</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">UE Number / Student ID</label>
                        <input type="text" name="student_id" value="{{ old('student_id',$user->student_id) }}" class="glow-input" style="width:100%;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">Registration Number</label>
                        <input type="text" name="registration_number" value="{{ old('registration_number',$user->registration_number) }}" class="glow-input" style="width:100%;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">Programme</label>
                        <input type="text" name="programme" value="{{ old('programme',$user->programme) }}" class="glow-input" style="width:100%;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">College</label>
                        <select name="college" class="glow-input" style="width:100%;">
                            <option value="">-- Select --</option>
                            @foreach(['College of Information and Communication Technology','College of Engineering and Technology','College of Science','College of Business Studies'] as $c)
                            <option value="{{ $c }}" {{ old('college',$user->college)===$c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">Year of Study</label>
                        <select name="year_of_study" class="glow-input" style="width:100%;">
                            <option value="">-- Select --</option>
                            @foreach(['Year 1','Year 2','Year 3','Year 4','Year 5'] as $yr)
                            <option value="{{ $yr }}" {{ old('year_of_study',$user->year_of_study)===$yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Officer Fields -->
        <div id="officerFields" style="{{ old('role',$user->role)!=='officer' ? 'display:none;' : '' }}">
            <div style="border-top:1px solid rgba(16,185,129,0.1);padding-top:18px;margin-bottom:18px;">
                <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(245,158,11,0.6);margin-bottom:16px;">&#9670; Officer Assignment</p>
                <div>
                    <label style="display:block;font-size:11px;font-weight:700;color:rgba(52,211,153,0.7);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:7px;">Department</label>
                    <select name="department_id" class="glow-input" style="width:100%;">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id',$user->department_id)===$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid rgba(16,185,129,0.1);">
            <a href="{{ route('admin.users.index') }}" style="font-size:12px;color:rgba(160,200,175,0.5);text-decoration:none;">&larr; Cancel</a>
            <button type="submit" class="btn-glow">Save Changes</button>
        </div>
    </form>
</div>
</div>

<script>
function toggleFields() {
    const role = document.getElementById('roleSelect').value;
    document.getElementById('studentFields').style.display = role === 'student' ? 'block' : 'none';
    document.getElementById('officerFields').style.display = role === 'officer' ? 'block' : 'none';
}
</script>
@endsection
