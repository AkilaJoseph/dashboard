/**
 * sync-manager.js
 *
 * Listens for the browser 'online' event and attempts to POST any pending
 * drafts to the existing clearance store endpoint.
 *
 * Online flow is completely unaffected — this module only activates when
 * drafts exist in IDB with status 'pending' or 'failed'.
 *
 * Idempotency:  each draft carries a stable idempotency_key UUID that is sent
 * as the X-Idempotency-Key header. The server's IdempotencyMiddleware returns
 * a cached 200 JSON on replay so the student never gets a duplicate clearance
 * even if the same draft is submitted twice (e.g. after a partial network failure).
 */

import { listDrafts, markSynced, updateDraftStatus, pendingCount } from './draft-store.js';
import { showToast } from './offline-form.js';

const STORE_URL  = '/student/clearances';
const CSRF_META  = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Public API ────────────────────────────────────────────────────────────────

/**
 * Attempt to sync all pending/failed drafts now.
 * Safe to call at any time — silently exits if offline or no drafts.
 */
export async function syncDrafts() {
    if (!navigator.onLine) return;

    const pending = await listDrafts().then(all =>
        all.filter(d => d.status === 'pending' || d.status === 'failed')
    );
    if (!pending.length) return;

    for (const draft of pending) {
        await syncOne(draft);
    }

    // Refresh the pending-drafts UI if it's on the page
    refreshPendingUI();
}

// ── Initialise ────────────────────────────────────────────────────────────────

/**
 * Call once on page load. Registers the 'online' listener and attempts an
 * immediate sync if the page loaded while already online.
 */
export function initSyncManager() {
    window.addEventListener('online',  () => {
        showToast('Connection restored — syncing drafts…', 'info');
        syncDrafts();
    });

    // Try immediately in case drafts exist from a previous offline session
    if (navigator.onLine) {
        syncDrafts().catch(() => {});
    }
}

// ── Internal ──────────────────────────────────────────────────────────────────

async function syncOne(draft) {
    await updateDraftStatus(draft.id, 'syncing');

    const body = new FormData();
    body.append('_token',        CSRF_META());
    body.append('academic_year', draft.academic_year);
    body.append('semester',      draft.semester);
    body.append('clearance_type',draft.clearance_type);
    if (draft.reason) body.append('reason', draft.reason);

    // Attachments (empty for now; extend when file upload is added to the form)
    for (const file of (draft.attachments ?? [])) {
        if (file.blob instanceof Blob) {
            body.append('attachments[]', file.blob, file.name);
        }
    }

    try {
        const res = await fetch(STORE_URL, {
            method:      'POST',
            credentials: 'same-origin',
            headers: {
                'X-Idempotency-Key': draft.idempotency_key,
                'X-Requested-With':  'XMLHttpRequest',
            },
            body,
            // follow redirects so a 302 → 200 is treated as success
            redirect: 'follow',
        });

        if (res.ok) {
            // Extract server clearance ID from JSON replay response if present
            let serverId = null;
            const ct = res.headers.get('content-type') ?? '';
            if (ct.includes('application/json')) {
                const json = await res.json().catch(() => ({}));
                serverId = json.clearance_id ?? null;
            }
            await markSynced(draft.id, serverId);
            showToast('Draft synced successfully.', 'success');
        } else if (res.status === 422) {
            // Validation error from server — draft data is invalid, mark failed
            const json = await res.json().catch(() => ({}));
            const msg  = Object.values(json.errors ?? {}).flat()[0] ?? 'Validation error';
            await updateDraftStatus(draft.id, 'failed', msg);
            showToast(`Sync failed: ${msg}`, 'error');
        } else {
            await updateDraftStatus(draft.id, 'failed', `HTTP ${res.status}`);
            showToast(`Sync failed (${res.status}). Will retry when online.`, 'error');
        }
    } catch (err) {
        await updateDraftStatus(draft.id, 'failed', err.message);
        // Don't show a toast on network errors — the 'online' listener will retry
    }
}

/** Refresh the pending-drafts widget on the dashboard without a full reload. */
async function refreshPendingUI() {
    const container = document.getElementById('pending-drafts-widget');
    if (!container) return;

    const { renderPendingDrafts } = await import('./offline-form.js');
    renderPendingDrafts(container);
}

// ─────────────────────────────────────────────────────────────────────────────
// BACKGROUND SYNC REGISTRATION
// Call after saving a draft or officer action to IDB.
//
// Supported browsers (Chrome/Edge/Android):
//   Updates status to "pending_sync" and registers the sync tag.
//   The SW fires the sync event when connectivity is confirmed.
//
// iOS Safari (no SyncManager):
//   Leaves status as "pending" so the window 'online' listener above handles it.
// ─────────────────────────────────────────────────────────────────────────────

const hasBGSync = () =>
    'serviceWorker' in navigator && 'SyncManager' in window;

/**
 * Register Background Sync for a student draft.
 * @param {string} draftId   local UUID from saveDraft()
 */
export async function registerDraftSync(draftId) {
    if (!hasBGSync()) return; // iOS: window 'online' path already handles this

    try {
        const { updateDraftStatus } = await import('./draft-store.js');
        await updateDraftStatus(draftId, 'pending_sync');

        const reg = await navigator.serviceWorker.ready;
        await reg.sync.register('submit-clearance-requests');

        // Store the registration timestamp for the debug page
        localStorage.setItem('acims_last_sync_registered', new Date().toISOString());
    } catch (err) {
        // Sync registration failed (permissions, SW not active, etc.)
        // Silently fall back: status stays "pending", window 'online' picks it up.
        console.warn('[sync-manager] BG Sync registration failed, falling back to online listener:', err.message);
    }
}

/**
 * Register Background Sync for an officer approval action.
 * @param {string} actionId  local UUID from saveOfficerAction()
 */
export async function registerOfficerSync(actionId) {
    if (!hasBGSync()) return;

    try {
        const { updateOfficerActionStatus } = await import('./draft-store.js');
        await updateOfficerActionStatus(actionId, 'pending_sync');

        const reg = await navigator.serviceWorker.ready;
        await reg.sync.register('submit-officer-actions');

        localStorage.setItem('acims_last_officer_sync_registered', new Date().toISOString());
    } catch (err) {
        console.warn('[sync-manager] Officer BG Sync registration failed:', err.message);
    }
}

// ── Listen for SYNC_COMPLETE messages posted back by the SW ──────────────────
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', async e => {
        const msg = e.data;
        if (!msg || msg.type !== 'SYNC_COMPLETE') return;

        localStorage.setItem('acims_last_sync_completed', new Date().toISOString());

        if (msg.success) {
            showToast(
                msg.store === 'officer_actions'
                    ? 'Approval action synced successfully.'
                    : 'Draft clearance submitted successfully.',
                'success'
            );
        } else if (msg.error) {
            showToast(`Sync failed: ${msg.error}`, 'error');
        }

        refreshPendingUI();
    });
}
