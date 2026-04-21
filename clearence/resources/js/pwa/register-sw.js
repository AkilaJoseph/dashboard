// ── PWA: Service Worker Registration ──────────────────────────────────────────
// Handles SW lifecycle, update-available toast, and install prompt.
// The inline registration in app.blade.php stays untouched; browsers
// deduplicate registrations for the same scope automatically.

if (!('serviceWorker' in navigator)) {
    // Browser does not support SW — exit silently, app still works normally.
    /* nothing */
} else {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js', { scope: '/' })
            .then(reg => {
                // ── Update-available handling ──────────────────────────────
                const onNewSW = worker => {
                    if (!worker) return;
                    if (worker.state === 'installed') {
                        showUpdateToast(worker);
                        return;
                    }
                    worker.addEventListener('statechange', () => {
                        if (worker.state === 'installed') showUpdateToast(worker);
                    });
                };

                // Newly installed SW waiting
                if (reg.waiting) onNewSW(reg.waiting);

                // Future updates
                reg.addEventListener('updatefound', () => {
                    onNewSW(reg.installing);
                });

                // Another tab triggered an update
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    if (!window.__acims_reloading) {
                        window.__acims_reloading = true;
                        window.location.reload();
                    }
                });
            })
            .catch(() => {
                // Registration failed (e.g. http:// in incognito) — silent, app unaffected.
            });
    });
}

// ── Update toast ──────────────────────────────────────────────────────────────
function showUpdateToast(worker) {
    // Avoid duplicate toasts
    if (document.getElementById('pwa-update-toast')) return;

    const toast = document.createElement('div');
    toast.id = 'pwa-update-toast';
    Object.assign(toast.style, {
        position:     'fixed',
        bottom:       '24px',
        left:         '50%',
        transform:    'translateX(-50%)',
        background:   '#064e3b',
        color:        '#fff',
        padding:      '12px 20px',
        borderRadius: '10px',
        boxShadow:    '0 4px 20px rgba(0,0,0,0.25)',
        display:      'flex',
        alignItems:   'center',
        gap:          '12px',
        fontSize:     '13px',
        fontWeight:   '600',
        zIndex:       '99999',
        animation:    'pwa-slide-up 0.3s ease',
    });

    toast.innerHTML = `
        <span>A new version of ACIMS is available.</span>
        <button id="pwa-update-btn" style="
            background:#059669;color:#fff;border:none;cursor:pointer;
            padding:5px 14px;border-radius:6px;font-size:12px;font-weight:700;">
            Update now
        </button>
        <button id="pwa-update-dismiss" style="
            background:none;border:none;color:#a7f3d0;cursor:pointer;font-size:16px;line-height:1;">
            ×
        </button>`;

    // Inject keyframe if not already present
    if (!document.getElementById('pwa-keyframes')) {
        const style = document.createElement('style');
        style.id = 'pwa-keyframes';
        style.textContent = '@keyframes pwa-slide-up{from{opacity:0;transform:translateX(-50%) translateY(20px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}';
        document.head.appendChild(style);
    }

    document.body.appendChild(toast);

    document.getElementById('pwa-update-btn').addEventListener('click', () => {
        worker.postMessage({ type: 'SKIP_WAITING' });
        toast.remove();
    });

    document.getElementById('pwa-update-dismiss').addEventListener('click', () => {
        toast.remove();
    });
}

// ── Install prompt ────────────────────────────────────────────────────────────
let _deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    _deferredInstallPrompt = e;
    showInstallButton();
});

window.addEventListener('appinstalled', () => {
    _deferredInstallPrompt = null;
    const btn = document.getElementById('pwa-install-btn');
    if (btn) btn.remove();
});

function showInstallButton() {
    if (document.getElementById('pwa-install-btn')) return;

    const btn = document.createElement('button');
    btn.id = 'pwa-install-btn';
    btn.title = 'Install ACIMS as an app';
    btn.innerHTML = `
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        <span>Install App</span>`;
    Object.assign(btn.style, {
        position:     'fixed',
        bottom:       '24px',
        right:        '24px',
        background:   'linear-gradient(135deg,#064e3b,#059669)',
        color:        '#fff',
        border:       'none',
        borderRadius: '10px',
        padding:      '10px 16px',
        display:      'flex',
        alignItems:   'center',
        gap:          '7px',
        fontSize:     '13px',
        fontWeight:   '700',
        cursor:       'pointer',
        boxShadow:    '0 4px 18px rgba(5,150,105,0.4)',
        zIndex:       '99998',
        animation:    'pwa-slide-up 0.35s ease',
    });

    btn.addEventListener('click', async () => {
        if (!_deferredInstallPrompt) return;
        _deferredInstallPrompt.prompt();
        const { outcome } = await _deferredInstallPrompt.userChoice;
        if (outcome === 'accepted') {
            _deferredInstallPrompt = null;
            btn.remove();
        }
    });

    document.body.appendChild(btn);
}
