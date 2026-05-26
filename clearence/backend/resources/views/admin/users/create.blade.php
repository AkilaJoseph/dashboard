@extends('layouts.app')

@section('title', 'Add New User')
@section('page-title', 'Add New User')
@section('page-subtitle', 'Register a student, officer, or administrator into the system')

@section('content')
<div style="max-width:680px;margin:0 auto;">
<div class="glow-card" style="padding:0;overflow:hidden;">

    <div style="padding:18px 26px;background:linear-gradient(135deg,#064e3b,#059669);display:flex;align-items:center;gap:12px;">
        <div style="width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;">
            <svg style="width:16px;height:16px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
            <h3 style="font-size:14px;font-weight:700;color:#fff;">New User Registration</h3>
            <p style="font-size:11px;color:rgba(209,250,229,0.75);margin-top:2px;">Mbeya University of Science and Technology</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" style="padding:26px;" id="userForm">
        @csrf

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #ef4444;border-radius:0 8px 8px 0;padding:12px 16px;margin-bottom:20px;">
            @foreach($errors->all() as $error)
            <p style="font-size:12px;color:#991b1b;margin-bottom:3px;">{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">
            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Full Name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" required value="{{ old('name') }}" class="glow-input" style="width:100%;" placeholder="e.g. Daudi Kasimu Juma">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Email Address <span style="color:#ef4444;">*</span></label>
                <input type="email" name="email" required value="{{ old('email') }}" class="glow-input" style="width:100%;" placeholder="user@must.ac.tz">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="glow-input" style="width:100%;" placeholder="+255 7XX XXX XXX">
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Role <span style="color:#ef4444;">*</span></label>
                <select name="role" id="roleSelect" required onchange="toggleFields()" class="glow-input" style="width:100%;">
                    <option value="">-- Select Role --</option>
                    <option value="student" {{ old('role')==='student' ? 'selected' : '' }}>Student</option>
                    <option value="officer" {{ old('role')==='officer' ? 'selected' : '' }}>Department Officer</option>
                    <option value="admin"   {{ old('role')==='admin'   ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Password <span style="color:#ef4444;">*</span></label>
                <input type="password" name="password" required minlength="6" class="glow-input" style="width:100%;" placeholder="Min. 6 characters">
            </div>
        </div>

        <!-- Student Fields -->
        <div id="studentFields" style="{{ old('role')!=='student' ? 'display:none;' : '' }}">
            <div style="border-top:1px solid #f1f5f9;padding-top:18px;margin-bottom:18px;">
                <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Student Details</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">UE Number / Student ID</label>
                        <input type="text" name="student_id" value="{{ old('student_id') }}" class="glow-input" style="width:100%;" placeholder="UE/BETS/25/14498">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Registration Number</label>
                        <input type="text" name="registration_number" value="{{ old('registration_number') }}" class="glow-input" style="width:100%;" placeholder="22100934340012">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Programme</label>
                        <input type="text" name="programme" value="{{ old('programme') }}" class="glow-input" style="width:100%;" placeholder="B.Eng in Telecommunication Systems">
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">College</label>
                        <select name="college" class="glow-input" style="width:100%;">
                            <option value="">-- Select College --</option>
                            @foreach(['College of Information and Communication Technology','College of Engineering and Technology','College of Science','College of Business Studies'] as $col)
                            <option value="{{ $col }}" {{ old('college')===$col ? 'selected' : '' }}>{{ $col }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Year of Study</label>
                        <select name="year_of_study" class="glow-input" style="width:100%;">
                            <option value="">-- Select Year --</option>
                            @foreach(['Year 1','Year 2','Year 3','Year 4','Year 5'] as $yr)
                            <option value="{{ $yr }}" {{ old('year_of_study')===$yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Officer Fields -->
        <div id="officerFields" style="{{ old('role')!=='officer' ? 'display:none;' : '' }}">
            <div style="border-top:1px solid #f1f5f9;padding-top:18px;margin-bottom:18px;">
                <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Officer Assignment</p>
                <div>
                    <label style="display:block;font-size:11px;font-weight:700;color:#374151;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;">Assign Department <span style="color:#ef4444;">*</span></label>
                    <select name="department_id" class="glow-input" style="width:100%;">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid #f1f5f9;">
            <a href="{{ route('admin.users.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Cancel</a>
            <button type="submit" class="btn-glow">Create User</button>
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
toggleFields();
</script>
@endsection
