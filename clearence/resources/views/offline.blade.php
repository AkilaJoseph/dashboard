<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline — ACIMS</title>
    <link rel="icon" href="/images/pwa-icons/icon-96.png">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar:   #064e3b;
            --green:     #059669;
            --green-mid: #10b981;
            --green-light: #d1fae5;
            --green-pale:  #f0fdf4;
            --gold:      #d97706;
            --text:      #1e293b;
            --text-muted:#64748b;
            --border:    #e2e8f0;
            --bg:        #f1f5f9;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* Thin branded top bar — mirrors the app's sidebar colour */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--sidebar), var(--green-mid));
        }

        .card {
            background: #fff;
            border: 1.5px solid var(--border);
            border-radius: 16px;
            padding: 40px 36px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            animation: popIn 0.35s ease both;
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to   { opacity: 1; transform: scale(1)    translateY(0); }
        }

        .icon-wrap {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green-pale), var(--green-light));
            border: 2px solid var(--green-light);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }

        h1 {
            font-size: 20px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
        }

        p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.7;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 20px 0;
        }

        .retry-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: linear-gradient(135deg, var(--sidebar), var(--green));
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 10px 22px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.15s, transform 0.15s;
            margin-top: 20px;
        }
        .retry-btn:hover { opacity: 0.9; transform: translateY(-1px); }

        .brand {
            margin-top: 28px;
            font-size: 11px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .brand-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--green);
            display: inline-block;
        }

        .tip {
            background: var(--green-pale);
            border: 1px solid var(--green-light);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12px;
            color: #065f46;
            margin-top: 16px;
            text-align: left;
        }
        .tip strong { font-weight: 700; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="30" height="30" fill="none" stroke="#059669" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M1 6s4-2 11-2 11 2 11 2"/>
                <path d="M5 10s3-1.5 7-1.5S19 10 19 10"/>
                <path d="M8.5 14s1.75-.75 3.5-.75 3.5.75 3.5.75"/>
                <circle cx="12" cy="18" r="1.2" fill="#059669" stroke="none"/>
                <line x1="2" y1="2" x2="22" y2="22" stroke="#ef4444" stroke-width="1.8"/>
            </svg>
        </div>

        <h1>You're Offline</h1>
        <p>No internet connection detected.<br>Please check your network and try again.</p>

        <div class="tip">
            <strong>Already loaded pages</strong> may still be available — tap Back to return to your last visited page.
        </div>

        <div class="divider"></div>

        <p style="font-size:12px;">Any pages you visited while online are cached and accessible below.</p>

        <button class="retry-btn" onclick="window.location.reload()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M23 4v6h-6"/><path d="M1 20v-6h6"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
            </svg>
            Try Again
        </button>
    </div>

    <div class="brand">
        <span class="brand-dot"></span>
        ACIMS — Mbeya University of Science and Technology
    </div>

    <script>
        // Auto-retry when the browser detects connectivity is restored
        window.addEventListener('online', () => window.location.reload());
    </script>
</body>
</html>
