<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUST Clearance &mdash; Sign In</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f0fdf4;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-wrap {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 560px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.05);
        }

        /* Left — brand */
        .brand-panel {
            width: 44%;
            background: linear-gradient(155deg, #064e3b 0%, #065f46 40%, #059669 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }
        .brand-panel::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 240px; height: 240px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }

        .emblem {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.22);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        .emblem span { font-size: 18px; font-weight: 900; color: #d1fae5; letter-spacing: -0.5px; }

        .brand-panel h1 { font-size: 21px; font-weight: 800; color: #fff; line-height: 1.25; margin-bottom: 10px; }
        .brand-panel > div > p { font-size: 13px; color: rgba(209,250,229,0.75); line-height: 1.65; }

        .brand-features { margin-top: 28px; display: flex; flex-direction: column; gap: 11px; }
        .brand-feature  { display: flex; align-items: center; gap: 10px; font-size: 12px; color: rgba(209,250,229,0.7); }
        .fdot { width: 6px; height: 6px; border-radius: 50%; background: #34d399; flex-shrink: 0; }

        .brand-footer { font-size: 10px; color: rgba(209,250,229,0.35); letter-spacing: 0.06em; }

        /* Right — form */
        .form-panel {
            flex: 1;
            background: #ffffff;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-panel h2  { font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 5px; }
        .form-panel .sub { font-size: 13px; color: #64748b; margin-bottom: 28px; }

        label {
            display: block; font-size: 11.5px; font-weight: 700;
            color: #374151; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 6px;
        }

        input[type=email], input[type=password] {
            width: 100%; border: 1.5px solid #e2e8f0; border-radius: 8px;
            padding: 10px 14px; font-size: 13.5px; color: #1e293b;
            background: #f8fafc; outline: none; margin-bottom: 16px;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            font-family: inherit;
        }
        input::placeholder { color: #94a3b8; }
        input:focus { border-color: #059669; background: #fff; box-shadow: 0 0 0 3px rgba(5,150,105,0.12); }

        .remember { display: flex; align-items: center; gap: 8px; margin-bottom: 22px; }
        .remember input { width: 15px; height: 15px; accent-color: #059669; margin: 0; }
        .remember span  { font-size: 13px; color: #64748b; font-weight: 400; letter-spacing: 0; text-transform: none; }

        .btn-signin {
            width: 100%; padding: 12px; border: none; border-radius: 9px; cursor: pointer;
            background: #059669; color: #fff; font-weight: 700; font-size: 14px;
            letter-spacing: 0.04em; font-family: inherit;
            transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
        }
        .btn-signin:hover { background: #047857; box-shadow: 0 4px 14px rgba(5,150,105,0.35); transform: translateY(-1px); }

        .alert-err {
            background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #ef4444;
            border-radius: 0 8px 8px 0; padding: 10px 14px;
            margin-bottom: 18px; font-size: 12.5px; color: #991b1b;
        }

        .demo { margin-top: 24px; padding: 14px 16px; background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 10px; }
        .demo-title { font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #065f46; margin-bottom: 10px; }
        .demo-row   { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
        .demo-row:last-child { margin-bottom: 0; }
        .demo-badge { font-size: 10px; font-weight: 700; padding: 2px 9px; border-radius: 999px; }
        .demo-badge.admin   { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .demo-badge.officer { background: #ede9fe; color: #4c1d95; border: 1px solid #ddd6fe; }
        .demo-badge.student { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .demo-cred  { font-size: 11px; color: #64748b; font-family: 'Consolas', monospace; }

        @media (max-width: 640px) {
            .brand-panel { display: none; }
            .login-wrap  { max-width: 420px; }
            .form-panel  { padding: 36px 28px; }
        }
    </style>
</head>
<body>

<div class="login-wrap">

    <!-- Brand Panel -->
    <div class="brand-panel">
        <div>
            <div class="emblem"><span>MUST</span></div>
            <h1>Automated Clearance Management System</h1>
            <p>Mbeya University of Science and Technology — streamlining the student clearance process across all departments.</p>
            <div class="brand-features">
                <div class="brand-feature"><span class="fdot"></span>Real-time departmental approval tracking</div>
                <div class="brand-feature"><span class="fdot"></span>Digital clearance certificates</div>
                <div class="brand-feature"><span class="fdot"></span>6 MUST departments integrated</div>
                <div class="brand-feature"><span class="fdot"></span>No physical office visits required</div>
            </div>
        </div>
        <p class="brand-footer">P.O. Box 131, Mbeya, Tanzania &nbsp;&middot;&nbsp; must.ac.tz</p>
    </div>

    <!-- Form Panel -->
    <div class="form-panel">
        <h2>Welcome Back</h2>
        <p class="sub">Sign in to access your clearance portal</p>

        @if($errors->any())
        <div class="alert-err">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="you@must.ac.tz" required autocomplete="email">

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Enter your password" required autocomplete="current-password">

            <div class="remember">
                <input type="checkbox" name="remember" id="remember">
                <span>Keep me signed in</span>
            </div>

            <button type="submit" class="btn-signin">Sign In to Portal</button>
        </form>

        <div class="demo">
            <p class="demo-title">&#9654; Demo Access Credentials</p>
            <div class="demo-row">
                <span class="demo-badge admin">Admin</span>
                <span class="demo-cred">admin@must.ac.tz / password</span>
            </div>
            <div class="demo-row">
                <span class="demo-badge officer">Officer</span>
                <span class="demo-cred">lib@must.ac.tz / password</span>
            </div>
            <div class="demo-row">
                <span class="demo-badge student">Student</span>
                <span class="demo-cred">student1@must.ac.tz / password</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
