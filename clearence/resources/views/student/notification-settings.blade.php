@extends('layouts.app')

@section('title', 'Notification Settings')
@section('page-title', 'Notification Settings')
@section('page-subtitle', 'Control how and where you receive clearance updates')

@section('content')
<div style="max-width:720px;margin:0 auto;">

    @if(session('success'))
    <div style="margin-bottom:16px;padding:10px 16px;background:#f0fdf4;border:1px solid #a7f3d0;border-radius:10px;font-size:13px;font-weight:600;color:#065f46;">
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Preferences ──────────────────────────────────────────────────────── --}}
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:16px;">Notification Channels</p>

        <form method="POST" action="{{ route('student.notification-settings.update') }}">
            @csrf
            @method('PATCH')

            <div style="display:flex;flex-direction:column;gap:14px;">
                {{-- Push toggle --}}
                <label style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;background:#fff;">
                    <div>
                        <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0 0 3px;">Push Notifications</p>
                        <p style="font-size:12px;color:#94a3b8;margin:0;">Instant browser/device alerts when a department approves or rejects your request.</p>
                    </div>
                    <div style="position:relative;flex-shrink:0;margin-left:16px;">
                        <input type="checkbox" name="push" value="1" id="toggle-push"
                               {{ ($prefs['push'] ?? true) ? 'checked' : '' }}
                               onchange="this.form.submit()"
                               style="opacity:0;width:0;height:0;position:absolute;">
                        <div id="track-push"
                             onclick="document.getElementById('toggle-push').click()"
                             style="width:44px;height:24px;border-radius:12px;cursor:pointer;transition:background 0.2s;
                                    background:{{ ($prefs['push'] ?? true) ? '#059669' : '#cbd5e1' }};">
                            <div style="width:18px;height:18px;border-radius:50%;background:#fff;margin-top:3px;transition:transform 0.2s;box-shadow:0 1px 3px rgba(0,0,0,0.2);
                                        transform:{{ ($prefs['push'] ?? true) ? 'translateX(23px)' : 'translateX(3px)' }};"></div>
                        </div>
                    </div>
                </label>

                {{-- In-app / database toggle --}}
                <label style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;background:#fff;">
                    <div>
                        <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0 0 3px;">In-App Notifications</p>
                        <p style="font-size:12px;color:#94a3b8;margin:0;">Notifications shown inside the dashboard when you're logged in.</p>
                    </div>
                    <div style="position:relative;flex-shrink:0;margin-left:16px;">
                        <input type="checkbox" name="database" value="1" id="toggle-db"
                               {{ ($prefs['database'] ?? true) ? 'checked' : '' }}
                               onchange="this.form.submit()"
                               style="opacity:0;width:0;height:0;position:absolute;">
                        <div id="track-db"
                             onclick="document.getElementById('toggle-db').click()"
                             style="width:44px;height:24px;border-radius:12px;cursor:pointer;transition:background 0.2s;
                                    background:{{ ($prefs['database'] ?? true) ? '#059669' : '#cbd5e1' }};">
                            <div style="width:18px;height:18px;border-radius:50%;background:#fff;margin-top:3px;transition:transform 0.2s;box-shadow:0 1px 3px rgba(0,0,0,0.2);
                                        transform:{{ ($prefs['database'] ?? true) ? 'translateX(23px)' : 'translateX(3px)' }};"></div>
                        </div>
                    </div>
                </label>
            </div>
        </form>
    </div>

    {{-- ── Push subscription setup ───────────────────────────────────────────── --}}
    <div class="glow-card" style="margin-bottom:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin:0;">This Device</p>
            <button id="btn-subscribe"
                    style="padding:6px 16px;border-radius:8px;border:none;font-size:12px;font-weight:700;cursor:pointer;
                           background:#059669;color:#fff;transition:background 0.2s;"
                    onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                Enable Push on This Device
            </button>
        </div>
        <div id="push-status" style="font-size:12px;color:#94a3b8;margin-bottom:0;"></div>
    </div>

    {{-- ── Registered devices ────────────────────────────────────────────────── --}}
    <div class="glow-card" style="padding:0;overflow:hidden;margin-bottom:18px;">
        <div style="padding:14px 22px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin:0;">
                Registered Devices
                <span style="font-size:11px;background:#e0f2fe;border:1px solid #bae6fd;color:#0369a1;padding:1px 8px;border-radius:999px;font-weight:700;margin-left:6px;">{{ $subscriptions->count() }}</span>
            </p>
        </div>

        @if($subscriptions->isEmpty())
        <div style="padding:20px 22px;text-align:center;">
            <p style="font-size:13px;color:#94a3b8;">No devices registered for push notifications.</p>
        </div>
        @else
        <div style="padding:12px 16px;display:flex;flex-direction:column;gap:8px;">
            @foreach($subscriptions as $sub)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;gap:12px;">
                <div style="flex:1;min-width:0;">
                    <p style="font-size:12px;font-weight:600;color:#1e293b;margin:0 0 3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $sub->user_agent ? Str::limit($sub->user_agent, 60) : 'Unknown browser' }}
                    </p>
                    <p style="font-size:11px;color:#94a3b8;margin:0;">
                        Added {{ $sub->created_at->format('d M Y') }}
                        @if($sub->last_used_at)
                         · Last used {{ $sub->last_used_at->diffForHumans() }}
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('student.notification-settings.remove-device') }}"
                      onsubmit="return confirm('Remove this device?')">
                    @csrf
                    <input type="hidden" name="subscription_id" value="{{ $sub->id }}">
                    <button type="submit"
                            style="flex-shrink:0;padding:5px 12px;border:1px solid #e2e8f0;border-radius:7px;background:none;
                                   font-size:12px;font-weight:600;color:#94a3b8;cursor:pointer;"
                            onmouseover="this.style.borderColor='#fca5a5';this.style.color='#ef4444';"
                            onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#94a3b8';">Remove</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <a href="{{ route('student.dashboard') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Back to Dashboard</a>

</div>

<script type="module">
import { isSupported, getPermissionState, requestPermission, subscribe, unsubscribe }
    from '/build/push/subscribe.js';

const btn    = document.getElementById('btn-subscribe');
const status = document.getElementById('push-status');

function setStatus(text, ok = null) {
    status.textContent = text;
    status.style.color = ok === true ? '#059669' : ok === false ? '#ef4444' : '#94a3b8';
}

async function init() {
    if (!isSupported()) {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        setStatus('Push notifications are not supported in this browser.');
        return;
    }

    const perm = getPermissionState();
    if (perm === 'granted') {
        btn.textContent = 'Push Already Enabled';
        btn.style.background = '#059669';
        btn.disabled = true;
        setStatus('This device is registered for push notifications.', true);
    } else if (perm === 'denied') {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        setStatus('Notification permission has been blocked. Reset it in your browser settings.');
    } else {
        setStatus('Click the button above to enable push notifications on this device.');
    }
}

btn.addEventListener('click', async () => {
    btn.disabled = true;
    btn.textContent = 'Requesting permission…';

    try {
        const perm = await requestPermission();
        if (perm !== 'granted') {
            setStatus('Permission denied. You can enable it again in your browser settings.', false);
            btn.textContent = 'Enable Push on This Device';
            btn.disabled = false;
            return;
        }

        const publicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
        if (!publicKey) {
            setStatus('VAPID public key not configured. Run: php artisan push:generate-keys', false);
            return;
        }

        await subscribe(publicKey);
        btn.textContent = 'Push Enabled';
        setStatus('This device is now registered. Reload the page to see it in the list.', true);
        setTimeout(() => location.reload(), 1500);
    } catch (err) {
        setStatus('Error: ' + err.message, false);
        btn.textContent = 'Enable Push on This Device';
        btn.disabled = false;
    }
});

init();
</script>
@endsection
