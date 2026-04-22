@extends('layouts.app')

@section('title', 'Pending Drafts')
@section('page-title', 'Pending Drafts')
@section('page-subtitle', 'Clearance requests saved offline — awaiting sync')

@section('content')
<div style="max-width:720px;margin:0 auto;">

    <!-- Connection status banner -->
    <div id="conn-banner" style="display:none;margin-bottom:18px;padding:10px 16px;border-radius:10px;font-size:13px;font-weight:600;"></div>

    <!-- Drafts list -->
    <div class="glow-card" style="padding:0;overflow:hidden;margin-bottom:18px;">
        <div style="padding:14px 22px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin:0;">Offline Drafts</p>
            <button id="btn-sync-now" class="btn-glow" style="font-size:12px;padding:6px 14px;">Sync Now</button>
        </div>
        <div id="drafts-container" style="padding:16px;"></div>
    </div>

    <!-- Officer actions list -->
    <div class="glow-card" style="padding:0;overflow:hidden;margin-bottom:18px;">
        <div style="padding:14px 22px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin:0;">Offline Officer Actions</p>
        </div>
        <div id="officer-container" style="padding:16px;"></div>
    </div>

    <a href="{{ route('student.dashboard') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Back to Dashboard</a>

</div>

<script>
(async function () {
    // ── Helpers ───────────────────────────────────────────────────────────────
    function statusBadge(status) {
        const map = {
            pending:      ['#fef3c7','#fde68a','#92400e', 'Pending'],
            pending_sync: ['#eff6ff','#bfdbfe','#1d4ed8', 'Queued (BG Sync)'],
            syncing:      ['#eff6ff','#bfdbfe','#1d4ed8', 'Syncing…'],
            synced:       ['#f0fdf4','#a7f3d0','#065f46', 'Synced'],
            failed:       ['#fef2f2','#fecaca','#991b1b', 'Failed'],
        };
        const [bg, border, color, label] = map[status] ?? ['#f8fafc','#e2e8f0','#64748b', status];
        return `<span style="font-size:10px;background:${bg};border:1px solid ${border};color:${color};padding:1px 8px;border-radius:999px;font-weight:700;">${label}</span>`;
    }

    function openDb() {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open('acims-offline', 2);
            req.onerror   = () => reject(req.error);
            req.onsuccess = () => resolve(req.result);
            req.onupgradeneeded = e => e.target.result.close();
        });
    }

    async function getAllFromStore(db, storeName) {
        if (!db.objectStoreNames.contains(storeName)) return [];
        return new Promise((resolve, reject) => {
            const req = db.transaction(storeName, 'readonly').objectStore(storeName).getAll();
            req.onsuccess = () => resolve(req.result ?? []);
            req.onerror   = () => reject(req.error);
        });
    }

    async function deleteFromStore(db, storeName, id) {
        return new Promise((resolve, reject) => {
            const req = db.transaction(storeName, 'readwrite').objectStore(storeName).delete(id);
            req.onsuccess = () => resolve();
            req.onerror   = () => reject(req.error);
        });
    }

    const typeLabels = {
        graduation: 'Graduation Clearance',
        semester:   'End of Semester',
        withdrawal: 'Withdrawal',
        transfer:   'Transfer',
    };

    // ── Render drafts ─────────────────────────────────────────────────────────
    async function renderDrafts(db) {
        const el     = document.getElementById('drafts-container');
        const drafts = (await getAllFromStore(db, 'drafts'))
            .filter(d => d.status !== 'synced')
            .sort((a, b) => (a.created_at < b.created_at ? 1 : -1));

        if (!drafts.length) {
            el.innerHTML = '<p style="font-size:13px;color:#94a3b8;text-align:center;padding:8px 0;">No pending drafts.</p>';
            return;
        }

        el.innerHTML = drafts.map(d => `
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:8px;background:#fff;gap:12px;">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                        <span style="font-size:13px;font-weight:600;color:#1e293b;">${typeLabels[d.clearance_type] ?? d.clearance_type}</span>
                        ${statusBadge(d.status)}
                    </div>
                    <p style="font-size:11px;color:#94a3b8;margin:0;">${d.academic_year} &middot; Semester ${d.semester} &middot; Saved ${new Date(d.created_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</p>
                    ${d.error ? `<p style="font-size:11px;color:#dc2626;margin:3px 0 0;">${d.error}</p>` : ''}
                </div>
                <button data-delete-draft="${d.id}"
                        style="flex-shrink:0;padding:5px 12px;border:1px solid #e2e8f0;border-radius:7px;background:none;font-size:12px;font-weight:600;color:#94a3b8;cursor:pointer;"
                        onmouseover="this.style.borderColor='#fca5a5';this.style.color='#ef4444';"
                        onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#94a3b8';">Discard</button>
            </div>`).join('');

        el.querySelectorAll('[data-delete-draft]').forEach(btn => {
            btn.addEventListener('click', async () => {
                await deleteFromStore(db, 'drafts', btn.dataset.deleteDraft);
                renderDrafts(db);
            });
        });
    }

    // ── Render officer actions ────────────────────────────────────────────────
    async function renderOfficerActions(db) {
        const el      = document.getElementById('officer-container');
        const actions = (await getAllFromStore(db, 'officer_actions'))
            .filter(a => a.status !== 'synced')
            .sort((a, b) => (a.created_at < b.created_at ? 1 : -1));

        if (!actions.length) {
            el.innerHTML = '<p style="font-size:13px;color:#94a3b8;text-align:center;padding:8px 0;">No pending officer actions.</p>';
            return;
        }

        el.innerHTML = actions.map(a => `
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:8px;background:#fff;gap:12px;">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                        <span style="font-size:13px;font-weight:600;color:#1e293b;text-transform:capitalize;">${a.action}</span>
                        <span style="font-size:12px;color:#64748b;">· Approval #${a.approval_id}</span>
                        ${statusBadge(a.status)}
                    </div>
                    <p style="font-size:11px;color:#94a3b8;margin:0;">Saved ${new Date(a.created_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</p>
                    ${a.error ? `<p style="font-size:11px;color:#dc2626;margin:3px 0 0;">${a.error}</p>` : ''}
                </div>
                <button data-delete-action="${a.id}"
                        style="flex-shrink:0;padding:5px 12px;border:1px solid #e2e8f0;border-radius:7px;background:none;font-size:12px;font-weight:600;color:#94a3b8;cursor:pointer;"
                        onmouseover="this.style.borderColor='#fca5a5';this.style.color='#ef4444';"
                        onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#94a3b8';">Discard</button>
            </div>`).join('');

        el.querySelectorAll('[data-delete-action]').forEach(btn => {
            btn.addEventListener('click', async () => {
                await deleteFromStore(db, 'officer_actions', btn.dataset.deleteAction);
                renderOfficerActions(db);
            });
        });
    }

    // ── Connection banner ─────────────────────────────────────────────────────
    function updateBanner() {
        const el = document.getElementById('conn-banner');
        if (navigator.onLine) {
            el.style.display    = 'none';
        } else {
            el.style.display    = 'block';
            el.style.background = '#fef9c3';
            el.style.color      = '#92400e';
            el.style.border     = '1px solid #fde68a';
            el.textContent      = 'You are currently offline. Drafts will sync automatically when you reconnect.';
        }
    }

    window.addEventListener('online',  updateBanner);
    window.addEventListener('offline', updateBanner);
    updateBanner();

    // ── Init ──────────────────────────────────────────────────────────────────
    let db;
    try {
        db = await openDb();
    } catch (e) {
        document.getElementById('drafts-container').textContent  = 'IndexedDB unavailable: ' + e.message;
        document.getElementById('officer-container').textContent = 'IndexedDB unavailable.';
        return;
    }

    await renderDrafts(db);
    await renderOfficerActions(db);

    // Sync Now button
    document.getElementById('btn-sync-now').addEventListener('click', async () => {
        if (!navigator.onLine) {
            alert('You are offline. Cannot sync right now.');
            return;
        }
        // Trigger sync via sync-manager (imported dynamically to keep this page standalone)
        try {
            const { syncDrafts } = await import('/build/offline/sync-manager.js').catch(() => ({}));
            if (typeof syncDrafts === 'function') {
                await syncDrafts();
                await renderDrafts(db);
                await renderOfficerActions(db);
            } else {
                location.reload();
            }
        } catch {
            location.reload();
        }
    });

    // Refresh after SW posts SYNC_COMPLETE
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', async e => {
            if (e.data?.type === 'SYNC_COMPLETE') {
                await renderDrafts(db);
                await renderOfficerActions(db);
            }
        });
    }
})();
</script>
@endsection
