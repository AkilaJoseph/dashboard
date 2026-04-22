<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MUST Clearance') — MUST CMS</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#064e3b">
    <meta name="application-name" content="ACIMS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ACIMS">
    <link rel="apple-touch-icon" href="/images/pwa-icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/pwa-icons/icon-152.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/images/pwa-icons/icon-144.png">
    <link rel="apple-touch-icon" sizes="128x128" href="/images/pwa-icons/icon-128.png">
    <meta name="msapplication-TileImage" content="/images/pwa-icons/icon-144.png">
    <meta name="msapplication-TileColor" content="#064e3b">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vapid-public-key" content="{{ config('services.webpush.public_key', '') }}">
    @php try { @endphp
    @vite(['resources/js/app.js'])
    @php } catch (\Throwable $e) { /* Vite not built yet — run: npm run dev */ } @endphp
    @stack('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --green:      #059669;
            --green-mid:  #10b981;
            --green-light:#d1fae5;
            --green-pale: #f0fdf4;
            --sidebar:    #064e3b;
            --sidebar2:   #065f46;
            --gold:       #d97706;
            --gold-light: #fef3c7;
            --text:       #1e293b;
            --text-muted: #64748b;
            --border:     #e2e8f0;
            --bg:         #f1f5f9;
            --white:      #ffffff;
        }

        html, body { height: 100%; margin: 0; }

        body {
            background: var(--bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text);
            font-size: 14px;
            line-height: 1.5;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #e2e8f0; }
        ::-webkit-scrollbar-thumb { background: var(--green-mid); border-radius: 3px; }

        /* Animations */
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slide-in {
            from { opacity: 0; transform: translateX(-8px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* ── Cards ── */
        .glow-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 22px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            transition: box-shadow 0.2s, border-color 0.2s;
        }
        .glow-card:hover {
            box-shadow: 0 4px 12px rgba(5,150,105,0.1), 0 1px 3px rgba(0,0,0,0.05);
            border-color: #a7f3d0;
        }

        /* ── Stat card number ── */
        .gold-number {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--green);
            line-height: 1;
        }

        /* ── Sidebar ── */
        .sidebar {
            background: var(--sidebar);
            border-right: none;
            box-shadow: 2px 0 8px rgba(0,0,0,0.12);
        }

        /* ── Nav links ── */
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border-radius: 8px;
            margin-bottom: 2px;
            font-size: 13.5px;
            font-weight: 500;
            color: rgba(209,250,229,0.75);
            transition: all 0.15s ease;
            text-decoration: none;
            cursor: pointer;
            background: none;
            border: none;
            width: 100%;
            animation: slide-in 0.3s ease forwards;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: #d1fae5;
        }
        .nav-link.active {
            background: var(--green);
            color: #ffffff;
            font-weight: 600;
        }
        .nav-link.active svg { opacity: 1; }

        /* ── Button ── */
        .btn-glow {
            background: var(--green);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-weight: 600;
            font-size: 13px;
            padding: 9px 20px;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            letter-spacing: 0.01em;
        }
        .btn-glow:hover {
            background: var(--green-mid);
            box-shadow: 0 4px 12px rgba(5,150,105,0.3);
            transform: translateY(-1px);
        }

        /* ── Table ── */
        .glow-table th {
            background: var(--green-pale);
            color: var(--green);
            font-size: 11px;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            font-weight: 700;
            border-bottom: 2px solid var(--green-light);
        }
        .glow-table td {
            border-bottom: 1px solid #f1f5f9;
            padding: 13px 18px;
            font-size: 13px;
            color: var(--text);
            transition: background 0.15s;
        }
        .glow-table tr:hover td { background: var(--green-pale); }

        /* ── Badges ── */
        .badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:600; }
        .badge-approved { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-pending  { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-progress { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

        /* ── Input ── */
        .glow-input {
            background: var(--white);
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: var(--text);
            padding: 9px 13px;
            font-size: 13px;
            width: 100%;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            font-family: inherit;
        }
        .glow-input::placeholder { color: #94a3b8; }
        .glow-input:focus {
            border-color: var(--green-mid);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
        }

        /* ── Progress ── */
        .glow-progress {
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .glow-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--green), var(--green-mid));
            transition: width 0.6s ease;
        }

        /* ── Flash ── */
        .flash-success {
            background: #d1fae5;
            border-left: 4px solid var(--green);
            border-radius: 0 8px 8px 0;
            color: #065f46;
        }
        .flash-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            border-radius: 0 8px 8px 0;
            color: #991b1b;
        }

        /* ── Emblem ── */
        .must-emblem {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(209,250,229,0.4);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* ── Page ── */
        .page-content { animation: fade-in-up 0.35s ease forwards; }

        /* ── Section label ── */
        .glow-sep {
            height: 1px;
            background: var(--border);
            margin: 10px 0;
        }

        /* Stat card */
        .stat-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(5,150,105,0.1);
            transform: translateY(-2px);
        }

        /* ── Topbar dropdowns ── */
        .tb-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            padding: 7px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            transition: background 0.15s, color 0.15s;
        }
        .tb-btn:hover { background: var(--bg); color: var(--text); }

        .tb-badge {
            position: absolute;
            top: 2px; right: 2px;
            min-width: 16px; height: 16px;
            background: #ef4444;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            line-height: 1;
        }

        .tb-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            z-index: 9999;
            min-width: 260px;
            display: none;
            animation: fade-in-up 0.15s ease;
        }
        .tb-dropdown.open { display: block; }

        .tb-dd-header {
            padding: 12px 16px 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }
        .tb-dd-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 14px;
            text-decoration: none;
            color: var(--text);
            font-size: 13px;
            transition: background 0.12s;
            border-bottom: 1px solid #f8fafc;
            cursor: pointer;
        }
        .tb-dd-item:last-child { border-bottom: none; }
        .tb-dd-item:hover { background: var(--green-pale); }
        .tb-dd-item.unread { background: #f0fdf4; }

        .notif-icon {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .notif-approved { background: #d1fae5; color: #059669; }
        .notif-rejected  { background: #fee2e2; color: #ef4444; }
        .notif-pending   { background: #fef3c7; color: #d97706; }

        .tb-dd-footer {
            padding: 8px 14px;
            border-top: 1px solid var(--border);
            text-align: center;
        }
        .tb-dd-footer a {
            font-size: 12px;
            color: var(--green);
            text-decoration: none;
            font-weight: 600;
        }

        .profile-avatar {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: var(--green);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
            font-size: 13px;
            color: #fff;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.15s;
        }
        .profile-avatar:hover { border-color: var(--green-mid); }
    </style>
</head>
<body>

@auth
<div style="display:flex; height:100vh; overflow:hidden;">

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="sidebar" style="width:240px; flex-shrink:0; display:flex; flex-direction:column;">

        <!-- Logo -->
        <div style="padding:18px 16px 14px; border-bottom:1px solid rgba(255,255,255,0.08);">
            <div style="display:flex;align-items:center;gap:11px;">
                @php $logoPath = public_path('images/must_logo.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="/images/must_logo.png" alt="MUST" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid rgba(209,250,229,0.3);">
                @else
                    <div class="must-emblem">
                        <span style="color:#d1fae5;font-weight:900;font-size:13px;letter-spacing:-0.5px;">MUST</span>
                    </div>
                @endif
                <div>
                    <p style="font-size:11px;font-weight:700;color:#d1fae5;margin:0;letter-spacing:0.04em;">MBEYA UNIVERSITY</p>
                    <p style="font-size:9px;color:rgba(209,250,229,0.45);margin:3px 0 0;letter-spacing:0.06em;line-height:1.3;">CLEARANCE MANAGEMENT<br>SYSTEM</p>
                </div>
            </div>
        </div>

        <!-- Nav -->
        <nav style="flex:1; padding:12px 10px; overflow-y:auto;">
            @if(auth()->user()->isStudent())
                <p style="font-size:9px; color:rgba(209,250,229,0.35); letter-spacing:0.12em; text-transform:uppercase; font-weight:700; margin:0 0 6px 8px;">Student Portal</p>
                <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('student.clearances.index') }}" class="nav-link {{ request()->routeIs('student.clearances.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    My Clearances
                </a>

            @elseif(auth()->user()->isOfficer())
                <p style="font-size:9px; color:rgba(209,250,229,0.35); letter-spacing:0.12em; text-transform:uppercase; font-weight:700; margin:0 0 6px 8px;">Officer Portal</p>
                <a href="{{ route('officer.dashboard') }}" class="nav-link {{ request()->routeIs('officer.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('officer.approvals.index') }}" class="nav-link {{ request()->routeIs('officer.approvals.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    Approvals
                </a>

            @elseif(auth()->user()->isAdmin())
                <p style="font-size:9px; color:rgba(209,250,229,0.35); letter-spacing:0.12em; text-transform:uppercase; font-weight:700; margin:0 0 6px 8px;">Admin Portal</p>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Users
                </a>
                <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    Departments
                </a>
                <a href="{{ route('admin.clearances.index') }}" class="nav-link {{ request()->routeIs('admin.clearances.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    Clearances
                </a>
                <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6"  y1="20" x2="6"  y2="14"/></svg>
                    Reports
                </a>
                <div class="glow-sep" style="margin:8px 0;background:rgba(255,255,255,0.08);"></div>
                <p style="font-size:9px; color:rgba(209,250,229,0.35); letter-spacing:0.12em; text-transform:uppercase; font-weight:700; margin:0 0 6px 8px;">Integrations</p>
                <a href="{{ route('admin.sims.sync') }}" class="nav-link {{ request()->routeIs('admin.sims.sync') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    SIMS Sync
                </a>
                <a href="{{ route('admin.sims.settings') }}" class="nav-link {{ request()->routeIs('admin.sims.settings') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    SIMS Settings
                </a>
            @endif
        </nav>

        <!-- Logout -->
        <div style="padding:10px; border-top:1px solid rgba(255,255,255,0.08);">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link" style="color:rgba(252,165,165,0.7);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- ═══ MAIN ═══ -->
    <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

        <!-- Top Bar -->
        <header style="background:var(--white); border-bottom:1px solid var(--border); padding:11px 24px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <h2 style="font-size:16px; font-weight:700; color:var(--text); margin:0;">@yield('page-title','Dashboard')</h2>
                <p style="font-size:11px; color:var(--text-muted); margin:2px 0 0;">@yield('page-subtitle','Mbeya University of Science and Technology')</p>
            </div>

            <div style="display:flex; align-items:center; gap:6px;">
                <!-- System status -->
                <span style="font-size:11px; color:var(--text-muted); display:flex; align-items:center; gap:5px; margin-right:8px;">
                    <span style="width:7px; height:7px; border-radius:50%; background:var(--green); display:inline-block;"></span>
                    {{ now()->format('d M Y') }}
                </span>

                @auth
                <!-- ── Notification Bell ── -->
                <div style="position:relative;" id="notif-wrap">
                    <button class="tb-btn" id="notif-btn" onclick="toggleDropdown('notif-dd')" title="Notifications">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="tb-badge" id="notif-count" style="display:none;">0</span>
                    </button>

                    <div class="tb-dropdown" id="notif-dd" style="min-width:300px;">
                        <div class="tb-dd-header" style="display:flex;align-items:center;justify-content:space-between;">
                            <span>Notifications</span>
                            <button onclick="markAllRead()" style="font-size:10px;color:var(--green);background:none;border:none;cursor:pointer;font-weight:600;">Mark all read</button>
                        </div>
                        <div id="notif-list" style="max-height:320px;overflow-y:auto;">
                            <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:12px;">Loading...</div>
                        </div>
                        <div class="tb-dd-footer">
                            <a href="{{ route('notifications.index') }}">View all notifications</a>
                        </div>
                    </div>
                </div>

                <!-- ── Profile Avatar ── -->
                <div style="position:relative;" id="profile-wrap">
                    <button class="tb-btn" onclick="toggleDropdown('profile-dd')" title="Account" style="padding:4px;">
                        <div class="profile-avatar">
                            {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                        </div>
                    </button>

                    <div class="tb-dropdown" id="profile-dd" style="min-width:220px;">
                        <div class="tb-dd-header">
                            <p style="font-size:13px;font-weight:700;color:var(--text);margin:0 0 2px;text-transform:none;letter-spacing:0;">{{ auth()->user()->name }}</p>
                            <p style="font-size:11px;color:var(--text-muted);font-weight:400;text-transform:none;letter-spacing:0;margin:0;">{{ auth()->user()->email }}</p>
                        </div>

                        <a href="{{ route('profile.show') }}" class="tb-dd-item" onclick="closeAll()">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--green-mid)"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                            <span>My Profile</span>
                        </a>

                        <a href="{{ route('notifications.index') }}" class="tb-dd-item" onclick="closeAll()">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:var(--green-mid)"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            <span>Notifications</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="tb-dd-item" style="width:100%;border:none;background:none;text-align:left;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;color:#ef4444"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                <span style="color:#ef4444;font-weight:600;">Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </header>

        <!-- Flash -->
        @if(session('success'))
        <div class="flash-success" style="margin:14px 28px 0; padding:11px 16px; font-size:13px; display:flex; align-items:center; gap:8px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="flash-error" style="margin:14px 28px 0; padding:11px 16px; font-size:13px; display:flex; align-items:center; gap:8px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            {{ session('error') }}
        </div>
        @endif

        <!-- Content -->
        <main class="page-content" style="flex:1; overflow-y:auto; padding:22px 28px;">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer style="background:var(--white); border-top:1px solid var(--border); padding:8px 28px; text-align:center; font-size:10px; color:#94a3b8; flex-shrink:0; letter-spacing:0.04em;">
            &copy; {{ date('Y') }} &nbsp;&middot;&nbsp; Mbeya University of Science and Technology &nbsp;&middot;&nbsp; Automated Clearance Management System &nbsp;&middot;&nbsp; P.O. Box 131, Mbeya, Tanzania
        </footer>
    </div>
</div>

@else
@yield('content')
@endauth

@stack('scripts')
<script>
// ── Dropdown toggle ──
function toggleDropdown(id) {
    const target = document.getElementById(id);
    const isOpen = target.classList.contains('open');
    closeAll();
    if (!isOpen) {
        target.classList.add('open');
        if (id === 'notif-dd') loadNotifications();
    }
}
function closeAll() {
    document.querySelectorAll('.tb-dropdown').forEach(d => d.classList.remove('open'));
}
document.addEventListener('click', e => {
    if (!e.target.closest('#notif-wrap') && !e.target.closest('#profile-wrap')) closeAll();
});

// ── Notifications ──
function loadNotifications() {
    fetch('{{ route("notifications.unread") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('notif-list');
            const badge = document.getElementById('notif-count');

            if (data.count > 0) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }

            if (!data.items || data.items.length === 0) {
                list.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);font-size:12px;">No new notifications</div>';
                return;
            }

            list.innerHTML = data.items.map(n => `
                <div class="tb-dd-item unread" onclick="markRead('${n.id}', ${n.clearance_id})">
                    <div class="notif-icon notif-${n.status}">
                        ${n.icon === 'check'
                            ? '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>'
                            : '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>'}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:12px;color:var(--text);margin:0 0 2px;line-height:1.4;">${n.message}</p>
                        <p style="font-size:10px;color:var(--text-muted);margin:0;">${n.created_at}</p>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {});
}

function markRead(id, clearanceId) {
    fetch('{{ route("notifications.markRead") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ id }),
    }).then(() => {
        closeAll();
        updateBadge();
        if (clearanceId) window.location.href = '/student/clearances/' + clearanceId;
    });
}

function markAllRead() {
    fetch('{{ route("notifications.markRead") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({}),
    }).then(() => { closeAll(); updateBadge(); });
}

function updateBadge() {
    fetch('{{ route("notifications.unread") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notif-count');
            if (badge) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.style.display = data.count > 0 ? 'flex' : 'none';
            }
        }).catch(() => {});
}

// ── Poll badge every 30s ──
@auth
updateBadge();
setInterval(updateBadge, 30000);
@endauth

// ── PWA: Register Service Worker ──
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => {
                console.log('SW registered');

                // Request notification permission
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission();
                }

                // Poll SW for notifications every 30s
                setInterval(() => {
                    if (reg.active) {
                        reg.active.postMessage({ type: 'POLL_NOTIFICATIONS' });
                    }
                }, 30000);
            })
            .catch(err => console.warn('SW failed:', err));
    });
}
</script>
</body>
</html>
