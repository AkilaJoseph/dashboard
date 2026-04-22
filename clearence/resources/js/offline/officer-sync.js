/**
 * officer-sync.js
 *
 * Progressive-enhancement layer for the officer approve/reject forms.
 *
 * Online path  → forms submit normally; this module does nothing.
 * Offline path → submit is intercepted, action saved to IDB (officer_actions store),
 *               Background Sync registered (iOS: falls back to window 'online' listener).
 *
 * Expects the officer approval show view to have:
 *   id="approve-form"  on the approve <form>
 *   id="reject-form"   on the reject <form>
 *   id="officer-offline-status" — a <div> for the offline indicator
 */

import { saveOfficerAction, listOfficerActions, updateOfficerActionStatus } from './draft-store.js';
import { showToast } from './offline-form.js';
import { registerOfficerSync } from './sync-manager.js';

// ── Boot ──────────────────────────────────────────────────────────────────────

export function initOfficerSync() {
    const approveForm = document.getElementById('approve-form');
    const rejectForm  = document.getElementById('reject-form');

    if (!approveForm && !rejectForm) return; // not on the approval show page

    updateOfficerOfflineIndicator();
    window.addEventListener('online',  updateOfficerOfflineIndicator);
    window.addEventListener('offline', updateOfficerOfflineIndicator);

    if (approveForm) approveForm.addEventListener('submit', e => handleOfficerSubmit(e, 'approve'));
    if (rejectForm)  rejectForm.addEventListener('submit',  e => handleOfficerSubmit(e, 'reject'));

    // iOS fallback: sync pending officer actions when connectivity is restored
    window.addEventListener('online', syncPendingOfficerActions);
}

// ── Form interception ─────────────────────────────────────────────────────────

async function handleOfficerSubmit(e, action) {
    if (navigator.onLine) {
        const reachable = await probeNetwork();
        if (reachable) return; // let the browser submit normally
    }

    e.preventDefault();

    const form       = e.currentTarget;
    const approvalId = extractApprovalId(form.action);

    if (!approvalId) {
        showToast('Could not determine approval ID. Please try again online.', 'error');
        return;
    }

    const fields = {
        approval_id: approvalId,
        action,
        comments:   form.elements['comments']?.value ?? null,
        csrf_token: document.querySelector('meta[name="csrf-token"]')?.content ?? '',
    };

    // Reject requires a reason
    if (action === 'reject' && !fields.comments?.trim()) {
        showToast('Please provide a rejection reason.', 'error');
        return;
    }

    try {
        const actionId = await saveOfficerAction(fields);
        await registerOfficerSync(actionId);
        showToast(
            `You're offline. Your ${action} decision has been saved and will sync when you reconnect.`,
            'info', 6000
        );
        setOfficerOfflineStatus('saved', action);
        form.reset();
    } catch (err) {
        showToast('Could not save action: ' + err.message, 'error');
    }
}

/** Extract the numeric approval ID from a URL like /officer/approvals/42/approve */
function extractApprovalId(url) {
    const match = url.match(/\/officer\/approvals\/(\d+)\//);
    return match ? Number(match[1]) : null;
}

// ── iOS fallback sync ─────────────────────────────────────────────────────────

async function syncPendingOfficerActions() {
    const actions = await listOfficerActions();
    const pending = actions.filter(a => a.status === 'pending' || a.status === 'failed');
    if (!pending.length) return;

    for (const action of pending) {
        await syncOneOfficerAction(action);
    }
}

async function syncOneOfficerAction(action) {
    await updateOfficerActionStatus(action.id, 'syncing');

    const url  = `/officer/approvals/${action.approval_id}/${action.action}`;
    const body = new FormData();
    body.append('_token', action.csrf_token ?? '');
    if (action.comments) body.append('comments', action.comments);

    try {
        const res = await fetch(url, {
            method:      'POST',
            credentials: 'same-origin',
            headers: {
                'X-Idempotency-Key': action.idempotency_key,
                'X-Requested-With':  'XMLHttpRequest',
            },
            body,
            redirect: 'follow',
        });

        if (res.ok || res.redirected) {
            await updateOfficerActionStatus(action.id, 'synced');
            showToast('Offline approval action synced successfully.', 'success');
        } else if (res.status === 419) {
            await updateOfficerActionStatus(action.id, 'failed', 'Session expired — reload the page and try again');
            showToast('Sync failed: session expired. Please reload the page.', 'error');
        } else if (res.status === 422) {
            const json = await res.json().catch(() => ({}));
            const msg  = Object.values(json.errors ?? {}).flat()[0] ?? 'Validation error';
            await updateOfficerActionStatus(action.id, 'failed', msg);
            showToast(`Sync failed: ${msg}`, 'error');
        } else {
            await updateOfficerActionStatus(action.id, 'failed', `HTTP ${res.status}`);
        }
    } catch (err) {
        await updateOfficerActionStatus(action.id, 'failed', err.message);
    }
}

// ── Offline indicator ─────────────────────────────────────────────────────────

function updateOfficerOfflineIndicator() {
    setOfficerOfflineStatus(navigator.onLine ? 'online' : 'offline');
}

function setOfficerOfflineStatus(state, action = null) {
    const el = document.getElementById('officer-offline-status');
    if (!el) return;

    if (state === 'online') {
        el.style.display = 'none';
        return;
    }

    const text = state === 'saved'
        ? `Your ${action ?? 'decision'} has been saved offline and will submit automatically when you reconnect.`
        : 'You\'re offline. Submitting will queue your decision and sync when you reconnect.';

    const bg     = state === 'saved' ? '#f0fdf4' : '#fef9c3';
    const color  = state === 'saved' ? '#065f46'  : '#92400e';
    const border = state === 'saved' ? '#a7f3d0' : '#fde68a';

    Object.assign(el.style, {
        display:      'block',
        background:   bg,
        color,
        border:       `1px solid ${border}`,
        borderRadius: '8px',
        padding:      '10px 14px',
        fontSize:     '12px',
        fontWeight:   '500',
        marginBottom: '16px',
        lineHeight:   '1.5',
    });
    el.textContent = text;
}

// ── Network probe ─────────────────────────────────────────────────────────────

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

// ── Auto-init ─────────────────────────────────────────────────────────────────

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOfficerSync);
} else {
    initOfficerSync();
}
