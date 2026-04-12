<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration — MUST CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #064e3b 0%, #065f46 40%, #059669 100%);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 16px;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 560px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #064e3b, #059669);
            padding: 28px 32px 22px;
            text-align: center;
        }
        .card-body { padding: 28px 32px 32px; }
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 5px;
        }
        .form-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 9px 13px;
            font-size: 13px;
            color: #1e293b;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
            font-family: inherit;
        }
        .form-input:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5,150,105,0.12);
        }
        .form-input::placeholder { color: #94a3b8; }
        .form-input.error { border-color: #ef4444; }
        .err-msg { font-size: 11px; color: #dc2626; margin-top: 4px; }
        .btn-submit {
            width: 100%;
            background: #059669;
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 11px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            letter-spacing: 0.02em;
            font-family: inherit;
        }
        .btn-submit:hover {
            background: #10b981;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(5,150,105,0.3);
        }
        .divider { height: 1px; background: #f1f5f9; margin: 20px 0; }
        .section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #059669;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #d1fae5;
        }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 480px) { .grid-2 { grid-template-columns: 1fr; } }
        select.form-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
    </style>
</head>
<body>
<div class="card">

    <!-- Header -->
    <div class="card-header">
        <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,0.15);border:2px solid rgba(209,250,229,0.5);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <span style="color:#d1fae5;font-weight:900;font-size:14px;letter-spacing:-0.5px;">MUST</span>
        </div>
        <h1 style="font-size:18px;font-weight:800;color:#fff;margin:0 0 4px;">Student Registration</h1>
        <p style="font-size:12px;color:rgba(209,250,229,0.7);margin:0;">Mbeya University of Science and Technology</p>
    </div>

    <div class="card-body">

        @if($errors->any())
        <div style="background:#fee2e2;border-left:4px solid #ef4444;border-radius:0 8px 8px 0;padding:11px 14px;margin-bottom:18px;font-size:12px;color:#991b1b;">
            <strong>Please fix the following errors:</strong>
            <ul style="margin:6px 0 0 16px;padding:0;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Personal Info -->
            <p class="section-label">Personal Information</p>

            <div style="margin-bottom:14px;">
                <label class="form-label">Full Name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="form-input {{ $errors->has('name') ? 'error' : '' }}"
                    placeholder="e.g. Daudi Kasimu Juma" required>
                @error('name')<p class="err-msg">{{ $message }}</p>@enderror
            </div>

            <div class="grid-2" style="margin-bottom:14px;">
                <div>
                    <label class="form-label">Email Address <span style="color:#ef4444;">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                        placeholder="you@must.ac.tz" required>
                    @error('email')<p class="err-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="form-input"
                        placeholder="+255 7XX XXX XXX">
                </div>
            </div>

            <div class="divider"></div>

            <!-- Academic Info -->
            <p class="section-label">Academic Information</p>

            <div class="grid-2" style="margin-bottom:14px;">
                <div>
                    <label class="form-label">Student ID (UE Number) <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="student_id" value="{{ old('student_id') }}"
                        class="form-input {{ $errors->has('student_id') ? 'error' : '' }}"
                        placeholder="e.g. UE2021001"
                        style="font-family:monospace;" required>
                    @error('student_id')<p class="err-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="registration_number" value="{{ old('registration_number') }}"
                        class="form-input"
                        placeholder="e.g. 22100934340012"
                        style="font-family:monospace;">
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label class="form-label">Programme <span style="color:#ef4444;">*</span></label>
                <input type="text" name="programme" value="{{ old('programme') }}"
                    class="form-input {{ $errors->has('programme') ? 'error' : '' }}"
                    placeholder="e.g. B.Eng Telecommunication Systems" required>
                @error('programme')<p class="err-msg">{{ $message }}</p>@enderror
            </div>

            <div class="grid-2" style="margin-bottom:14px;">
                <div>
                    <label class="form-label">College <span style="color:#ef4444;">*</span></label>
                    <select name="college" class="form-input {{ $errors->has('college') ? 'error' : '' }}" required>
                        <option value="">Select college…</option>
                        @foreach([
                            'College of Engineering and Technology',
                            'College of Science and Mathematics',
                            'College of Business Studies and Economics',
                            'College of Agriculture',
                            'College of ICT',
                            'College of Health Sciences',
                        ] as $col)
                        <option value="{{ $col }}" {{ old('college') === $col ? 'selected' : '' }}>{{ $col }}</option>
                        @endforeach
                    </select>
                    @error('college')<p class="err-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Year of Study <span style="color:#ef4444;">*</span></label>
                    <select name="year_of_study" class="form-input {{ $errors->has('year_of_study') ? 'error' : '' }}" required>
                        <option value="">Select year…</option>
                        @foreach(['Year 1','Year 2','Year 3','Year 4','Year 5','Postgraduate'] as $yr)
                        <option value="{{ $yr }}" {{ old('year_of_study') === $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                    @error('year_of_study')<p class="err-msg">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="divider"></div>

            <!-- Password -->
            <p class="section-label">Set Password</p>

            <div class="grid-2" style="margin-bottom:20px;">
                <div>
                    <label class="form-label">Password <span style="color:#ef4444;">*</span></label>
                    <input type="password" name="password"
                        class="form-input {{ $errors->has('password') ? 'error' : '' }}"
                        placeholder="Min. 6 characters" required>
                    @error('password')<p class="err-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Confirm Password <span style="color:#ef4444;">*</span></label>
                    <input type="password" name="password_confirmation"
                        class="form-input"
                        placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Create Student Account
            </button>
        </form>

        <p style="text-align:center;font-size:12px;color:#64748b;margin-top:16px;">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#059669;font-weight:600;text-decoration:none;">Sign in here</a>
        </p>
    </div>
</div>
</body>
</html>
