/**
 * offline-form.js
 *
 * Progressive-enhancement layer for the clearance request form.
 *
 * Online path  → form submits normally via the browser; this module does nothing.
 * Offline path → submit is intercepted, draft saved to IDB, toast shown.
 * Network fail → if fetch throws (after a brief attempt), draft is saved as above.
 *
 * This module also exports:
 *   showToast(message, type)      — used by sync-manager.js
 *   renderPendingDrafts(el)       — used by sync-manager.js to refresh the widget
 */

import { saveDraft, listDrafts, deleteDraft } from './draft-store.js';

// ── Form interception ─────────────────────────────────────────────────────────

export function initOfflineForm() {
    const form = document.getElementById('clearance-form');
    if (!form) return;   // not on the create page

    updateOfflineIndicator();

    window.addEventListener('online',  updateOfflineIndicator);
    window.addEventListener('offline', updateOfflineIndicator);

    form.addEventListener('submit', handleSubmit);
}

async function handleSubmit(e) {
    const form = e.currentTarget;

    // If online, let the browser submit normally — no JS involvement
    if (navigator.onLine) {
        // Attempt a quick network probe to confirm connectivity isn't just
        // navigator.onLine being stale (happens on captive portals etc.)
        const reachable = await probeNetwork();
        if (reachable) return; // let the default POST happen
    }

    // Offline (or network unreachable) — intercept
    e.preventDefault();

    const fields = {
        academic_year:  form.elements['academic_year']?.value  ?? '',
        semester:       form.elements['semester']?.value        ?? '',
        clearance_type: form.elements['clearance_type']?.value  ?? '',
        reason:         form.elements['reason']?.value          ?? '',
        attachments:    [],   // no file upload field yet
        csrf_token:     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
    };

    // Basic client-side guard — mirror the server's required fields
    if (!fields.academic_year || !fields.semester || !fields.clearance_type) {
        showToast('Please fill in all required fields before saving offline.', 'error');
        return;
    }

    try {
        const draftId = await saveDraft(fields);
        // Register Background Sync (no-op on iOS; window 'online' handles it there)
        import('./sync-manager.js').then(m => m.registerDraftSync(draftId)).catch(() => {});
        showToast('You\'re offline. Your draft has been saved and will sync automatically when you reconnect.', 'info', 6000);
        setOfflineStatus('saved');
        form.reset();
    } catch (err) {
        showToast('Could not save draft: ' + err.message, 'error');
    }
}

/** Probe connectivity with a lightweight HEAD request to /up (Laravel's health endpoint). */
async function probeNetwork() {
    try {
        const ctrl  = new AbortController();
        const timer = setTimeout(() => ctrl.abort(), 3000);
        const res   = await fetch('/up', { method: 'HEAD', cache: 'no-store', signal: ctrl.signal });
        clearTimeout(timer);
        return res.ok;
    } catch {
        return false;
    }
}

// ── Offline indicator ─────────────────────────────────────────────────────────

function updateOfflineIndicator() {
    setOfflineStatus(navigator.onLine ? 'online' : 'offline');
}

function setOfflineStatus(state) {
    const el = document.getElementById('offline-status');
    if (!el) return;

    const configs = {
        online:  { text: '',             bg: 'transparent',  color: 'transparent', border: 'transparent' },
        offline: { text: '⚡ You\'re offline. Submitting will save a draft that syncs when you reconnect.',
                   bg: '#fef9c3', color: '#92400e', border: '#fde68a' },
        saved:   { text: '✓ Draft saved. It will be submitted automatically when you\'re back online.',
                   bg: '#f0fdf4', color: '#065f46', border: '#a7f3d0' },
    };

    const cfg = configs[state] ?? configs.online;
    if (!cfg.text) {
        el.style.display = 'none';
        return;
    }
    el.textContent    = cfg.text;
    el.style.display  = 'block';
    el.style.background  = cfg.bg;
    el.style.color       = cfg.color;
    el.style.border      = `1px solid ${cfg.border}`;
    el.style.borderRadius= '8px';
    el.style.padding     = '10px 14px';
    el.style.fontSize    = '12px';
    el.style.fontWeight  = '500';
    el.style.marginBottom= '16px';
    el.style.lineHeight  = '1.5';
}

// ── Toast ─────────────────────────────────────────────────────────────────────

let _toastTimer = null;

/**
 * Show a non-blocking toast message.
 * @param {string} message
 * @param {'info'|'success'|'error'} type
 * @param {number} duration  ms, default 4000
 */
export function showToast(message, type = 'info', duration = 4000) {
    let toast = document.getElementById('acims-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'acims-toast';
        Object.assign(toast.style, {
            position:     'fixed',
            bottom:       '80px',     // above the PWA install button if visible
            left:         '50%',
            transform:    'translateX(-50%)',
            maxWidth:     '480px',
            width:        'calc(100% - 32px)',
            borderRadius: '10px',
            padding:      '12px 18px',
            fontSize:     '13px',
            fontWeight:   '600',
            lineHeight:   '1.5',
            zIndex:       '99997',
            boxShadow:    '0 4px 20px rgba(0,0,0,0.15)',
            transition:   'opacity 0.25s',
            pointerEvents:'none',
        });
        document.body.appendChild(toast);

        if (!document.getElementById('acims-toast-kf')) {
            const s = document.createElement('style');
            s.id = 'acims-toast-kf';
            s.textContent = '@keyframes acims-toast-in{from{opacity:0;transform:translateX(-50%) translateY(12px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}';
            document.head.appendChild(s);
        }
    }

    const palettes = {
        info:    { bg: '#064e3b', color: '#d1fae5' },
        success: { bg: '#059669', color: '#fff'    },
        error:   { bg: '#dc2626', color: '#fff'    },
    };
    const p = palettes[type] ?? palettes.info;
    toast.style.background = p.bg;
    toast.style.color      = p.color;
    toast.textContent      = message;
    toast.style.opacity    = '1';
    toast.style.animation  = 'acims-toast-in 0.3s ease';

    if (_toastTimer) clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => { toast.style.opacity = '0'; }, duration);
}

// ── Pending-drafts widget renderer ────────────────────────────────────────────

/**
 * Render the list of pending/failed drafts into the given container element.
 * Called on page load and by sync-manager after a sync attempt.
 * @param {HTMLElement} container
 */
export async function renderPendingDrafts(container) {
    if (!container) return;

    const drafts = await listDrafts().then(all =>
        all.filter(d => d.status === 'pending' || d.status === 'failed' || d.status === 'syncing')
    );

    if (!drafts.length) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';

    const typeLabels = {
        graduation: 'Graduation Clearance',
        semester:   'End of Semester',
        withdrawal: 'Withdrawal',
        transfer:   'Transfer',
    };

    container.innerHTML = `
        <div style="padding:0;overflow:hidden;">
            <div style="padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:#d97706;animation:pulse-dot 1.4s ease-out infinite;"></div>
                    <p style="font-size:13px;font-weight:700;color:#1e293b;margin:0;">Pending Sync</p>
                    <span style="font-size:11px;background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:1px 8px;border-radius:999px;font-weight:700;">${drafts.length}</span>
                </div>
                <span style="font-size:11px;color:#94a3b8;">Saved offline — will submit when online</span>
            </div>
            <div style="padding:12px 16px;display:flex;flex-direction:column;gap:8px;" id="draft-list">
                ${drafts.map(d => draftRow(d, typeLabels)).join('')}
            </div>
        </div>`;

    // Bind delete buttons
    container.querySelectorAll('[data-delete-draft]').forEach(btn => {
        btn.addEventListener('click', async () => {
            await deleteDraft(btn.dataset.deleteDraft);
            renderPendingDrafts(container);
        });
    });
}

function draftRow(draft, typeLabels) {
    const label   = typeLabels[draft.clearance_type] ?? draft.clearance_type;
    const date    = new Date(draft.created_at).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' });
    const isFailed= draft.status === 'failed';
    const isSyncing = draft.status === 'syncing';

    const statusChip = isFailed
        ? `<span style="font-size:10px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:1px 7px;border-radius:999px;font-weight:700;">Failed</span>`
        : isSyncing
        ? `<span style="font-size:10px;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:1px 7px;border-radius:999px;font-weight:700;">Syncing…</span>`
        : `<span style="font-size:10px;background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:1px 7px;border-radius:999px;font-weight:700;">Pending</span>`;

    return `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border:1px solid #e2e8f0;border-radius:9px;background:#fff;gap:10px;">
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-bottom:3px;">
                    <span style="font-size:13px;font-weight:600;color:#1e293b;">${label}</span>
                    ${statusChip}
                </div>
                <p style="font-size:11px;color:#94a3b8;margin:0;">${draft.academic_year} · ${draft.semester} Semester · Saved ${date}</p>
                ${isFailed && draft.error ? `<p style="font-size:11px;color:#dc2626;margin:2px 0 0;">${draft.error}</p>` : ''}
            </div>
            <button data-delete-draft="${draft.id}"
                    title="Discard this draft"
                    style="flex-shrink:0;background:none;border:1px solid #e2e8f0;border-radius:6px;padding:4px 8px;cursor:pointer;color:#94a3b8;font-size:11px;font-weight:600;transition:all 0.15s;"
                    onmouseover="this.style.borderColor='#fca5a5';this.style.color='#dc2626';"
                    onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#94a3b8';">
                Discard
            </button>
        </div>`;
}

// ── Boot ──────────────────────────────────────────────────────────────────────
// Auto-initialise when the module is first imported.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOfflineForm);
} else {
    initOfflineForm();
}

// Initialise the pending-drafts widget if on the dashboard
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('pending-drafts-widget');
    if (widget) renderPendingDrafts(widget);
});
