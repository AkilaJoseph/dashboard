<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUST Clearance &mdash; Sign In</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        :root{
            --green:#10b981;--green-bright:#34d399;--gold:#f59e0b;--gold-bright:#fbbf24;
            --bg:#020904;
        }
        body{background:var(--bg);min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;color:#e2e8f0;overflow:hidden;}

        /* Matrix Canvas */
        #matrix{position:fixed;top:0;left:0;width:100%;height:100%;z-index:0;opacity:0.18;}

        /* Radial ambient glow */
        .ambient{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:1;}
        .ambient-green{width:700px;height:700px;background:radial-gradient(circle,rgba(16,185,129,0.18) 0%,transparent 70%);top:-200px;left:-200px;}
        .ambient-gold{width:500px;height:500px;background:radial-gradient(circle,rgba(245,158,11,0.12) 0%,transparent 70%);bottom:-150px;right:-100px;}

        /* Wrapper */
        .wrapper{position:relative;z-index:2;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}

        /* Card */
        .card{
            width:100%;max-width:420px;
            background:rgba(4,18,10,0.88);
            border:1px solid rgba(16,185,129,0.3);
            border-radius:20px;
            backdrop-filter:blur(24px);
            box-shadow:0 0 60px rgba(16,185,129,0.15),0 0 120px rgba(16,185,129,0.06),0 30px 60px rgba(0,0,0,0.6);
            animation:card-in 0.6s cubic-bezier(0.2,1,0.4,1) forwards;
            overflow:hidden;
            position:relative;
        }
        @keyframes card-in{from{opacity:0;transform:translateY(30px) scale(0.96);}to{opacity:1;transform:translateY(0) scale(1);}}

        /* Card top glow line */
        .card::before{content:'';position:absolute;top:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,var(--green-bright),var(--gold),transparent);box-shadow:0 0 20px rgba(16,185,129,0.5);}

        /* Header */
        .card-head{padding:32px 32px 24px;text-align:center;border-bottom:1px solid rgba(16,185,129,0.1);}
        .emblem{
            width:70px;height:70px;border-radius:50%;
            background:linear-gradient(135deg,#020c05,#061f0e);
            border:2px solid var(--green);
            box-shadow:0 0 30px rgba(16,185,129,0.5),0 0 60px rgba(16,185,129,0.15),inset 0 0 20px rgba(16,185,129,0.08);
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 16px;
            animation:emblem-pulse 3s ease-in-out infinite;
        }
        @keyframes emblem-pulse{0%,100%{box-shadow:0 0 25px rgba(16,185,129,0.4),0 0 50px rgba(16,185,129,0.1);}50%{box-shadow:0 0 45px rgba(16,185,129,0.7),0 0 80px rgba(16,185,129,0.2);}}
        .emblem span{font-weight:900;font-size:18px;letter-spacing:-1px;background:linear-gradient(135deg,#34d399,#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
        .uni-name{font-size:13px;font-weight:700;letter-spacing:0.08em;color:var(--green-bright);margin-bottom:4px;}
        .sys-name{font-size:10px;color:rgba(245,158,11,0.7);letter-spacing:0.12em;text-transform:uppercase;}

        /* Body */
        .card-body{padding:28px 32px 32px;}
        .card-body h2{font-size:20px;font-weight:800;color:#e2e8f0;margin-bottom:4px;}
        .card-body p{font-size:12px;color:rgba(160,200,175,0.5);margin-bottom:24px;}

        /* Form */
        label{display:block;font-size:11px;font-weight:600;color:var(--green-bright);letter-spacing:0.06em;text-transform:uppercase;margin-bottom:6px;}
        input[type=email],input[type=password]{
            width:100%;background:rgba(2,9,4,0.9);
            border:1px solid rgba(16,185,129,0.2);
            border-radius:9px;color:#e2e8f0;
            padding:11px 14px;font-size:13px;
            transition:all 0.2s;outline:none;margin-bottom:16px;
        }
        input::placeholder{color:rgba(160,200,175,0.3);}
        input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(16,185,129,0.12),0 0 20px rgba(16,185,129,0.12);}

        .remember{display:flex;align-items:center;gap:8px;margin-bottom:22px;}
        .remember input{width:14px;height:14px;margin:0;accent-color:var(--green);}
        .remember span{font-size:12px;color:rgba(160,200,175,0.6);}

        .btn-login{
            width:100%;padding:13px;border:none;border-radius:10px;cursor:pointer;
            background:linear-gradient(135deg,#059669 0%,#10b981 50%,#34d399 100%);
            color:#020904;font-weight:800;font-size:14px;letter-spacing:0.04em;
            box-shadow:0 0 25px rgba(16,185,129,0.4),0 4px 20px rgba(16,185,129,0.2);
            transition:all 0.25s;position:relative;overflow:hidden;
        }
        .btn-login::after{content:'';position:absolute;top:0;left:-100%;width:60%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.2),transparent);animation:shimmer 2.5s infinite;}
        .btn-login:hover{box-shadow:0 0 40px rgba(16,185,129,0.6),0 6px 30px rgba(16,185,129,0.3);transform:translateY(-1px);filter:brightness(1.08);}
        @keyframes shimmer{0%{left:-100%;}100%{left:200%;}}

        /* Error */
        .alert-err{background:rgba(239,68,68,0.1);border-left:3px solid #ef4444;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#f87171;}

        /* Demo creds */
        .demo{margin-top:22px;padding:14px;background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.12);border-radius:10px;}
        .demo-title{font-size:9px;color:rgba(16,185,129,0.5);letter-spacing:0.12em;text-transform:uppercase;font-weight:700;margin-bottom:10px;}
        .demo-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;}
        .demo-badge{font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;}
        .demo-badge.admin{background:rgba(245,158,11,0.15);color:var(--gold-bright);border:1px solid rgba(245,158,11,0.3);}
        .demo-badge.officer{background:rgba(59,130,246,0.15);color:#93c5fd;border:1px solid rgba(59,130,246,0.3);}
        .demo-badge.student{background:rgba(16,185,129,0.15);color:var(--green-bright);border:1px solid rgba(16,185,129,0.3);}
        .demo-cred{font-size:10px;color:rgba(160,200,175,0.45);font-family:monospace;}

        /* Corner deco */
        .corner{position:absolute;width:16px;height:16px;border-color:var(--gold);border-style:solid;opacity:0.5;}
        .corner-tl{top:12px;left:12px;border-width:2px 0 0 2px;border-radius:3px 0 0 0;}
        .corner-tr{top:12px;right:12px;border-width:2px 2px 0 0;border-radius:0 3px 0 0;}
        .corner-bl{bottom:12px;left:12px;border-width:0 0 2px 2px;border-radius:0 0 0 3px;}
        .corner-br{bottom:12px;right:12px;border-width:0 2px 2px 0;border-radius:0 0 3px 0;}
    </style>
</head>
<body>

<canvas id="matrix"></canvas>
<div class="ambient ambient-green"></div>
<div class="ambient ambient-gold"></div>

<div class="wrapper">
    <div class="card">
        <!-- Corner decorations -->
        <div class="corner corner-tl"></div><div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div><div class="corner corner-br"></div>

        <!-- Header -->
        <div class="card-head">
            <div class="emblem"><span>MUST</span></div>
            <p class="uni-name">Mbeya University of Science &amp; Technology</p>
            <p class="sys-name">Automated Clearance Management System</p>
        </div>

        <!-- Form -->
        <div class="card-body">
            <h2>Sign In</h2>
            <p>Enter your credentials to access the portal</p>

            @if($errors->any())
            <div class="alert-err">
                @foreach($errors->all() as $e)<div>&#9670; {{ $e }}</div>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@must.ac.tz" required autocomplete="email">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">

                <div class="remember">
                    <input type="checkbox" name="remember" id="remember">
                    <span>Keep me signed in</span>
                </div>

                <button type="submit" class="btn-login">ACCESS PORTAL</button>
            </form>

            <!-- Demo -->
            <div class="demo">
                <p class="demo-title">&#9670; Demo Access Credentials</p>
                <div class="demo-row"><span class="demo-badge admin">Admin</span><span class="demo-cred">admin@must.ac.tz / password</span></div>
                <div class="demo-row"><span class="demo-badge officer">Officer</span><span class="demo-cred">lib@must.ac.tz / password</span></div>
                <div class="demo-row" style="margin:0;"><span class="demo-badge student">Student</span><span class="demo-cred">student1@must.ac.tz / password</span></div>
            </div>
        </div>
    </div>
</div>

<script>
// Matrix rain animation
const canvas = document.getElementById('matrix');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
window.addEventListener('resize', () => { canvas.width = window.innerWidth; canvas.height = window.innerHeight; init(); });

const chars = 'MUST01アイウエオカキクケコABCDEF023456789₿∑∏∆CLEARANCE'.split('');
const fontSize = 13;
let cols, drops;

function init() {
    cols = Math.floor(canvas.width / fontSize);
    drops = Array(cols).fill(1);
}
init();

function draw() {
    ctx.fillStyle = 'rgba(2,9,4,0.05)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    for (let i = 0; i < drops.length; i++) {
        const char = chars[Math.floor(Math.random() * chars.length)];
        const opacity = Math.random() > 0.9 ? 1 : 0.35;
        ctx.fillStyle = Math.random() > 0.97 ? `rgba(245,158,11,${opacity})` : `rgba(16,185,129,${opacity})`;
        ctx.font = fontSize + 'px monospace';
        ctx.fillText(char, i * fontSize, drops[i] * fontSize);
        if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
        drops[i]++;
    }
}
setInterval(draw, 50);
</script>
</body>
</html>
