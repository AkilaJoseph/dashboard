@extends('layouts.app')

@section('title', 'PWA Debug')
@section('page-title', 'PWA Debug')
@section('page-subtitle', 'Service worker, cache, and offline-sync diagnostics')

@section('content')
<div style="max-width:900px;margin:0 auto;">

    <!-- Service Worker State -->
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Service Worker</p>
        <div id="sw-state" style="font-size:13px;color:#64748b;">Checking…</div>
    </div>

    <!-- Background Sync -->
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Background Sync</p>
        <div id="sync-state" style="font-size:13px;color:#64748b;">Checking…</div>
    </div>

    <!-- Timestamps -->
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Sync Timestamps (localStorage)</p>
        <div id="ts-state" style="font-size:13px;color:#64748b;"></div>
    </div>

    <!-- Cache Storage -->
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Cache Storage</p>
        <div id="cache-state" style="font-size:13px;color:#64748b;">Checking…</div>
    </div>

    <!-- IDB — Drafts -->
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">IDB — Student Drafts</p>
        <div id="idb-drafts" style="font-size:13px;color:#64748b;">Checking…</div>
    </div>

    <!-- IDB — Officer Actions -->
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">IDB — Officer Actions</p>
        <div id="idb-officer" style="font-size:13px;color:#64748b;">Checking…</div>
    </div>

    <!-- Actions -->
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Actions</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button id="btn-reload" class="btn-glow" style="font-size:13px;">Reload Page</button>
            <button id="btn-skip-waiting"
                    style="padding:8px 18px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:13px;font-weight:600;cursor:pointer;color:#64748b;">
                Activate Waiting SW
            </button>
            <button id="btn-clear-cache"
                    style="padding:8px 18px;border:1px solid #fecaca;border-radius:8px;background:#fef2f2;font-size:13px;font-weight:600;cursor:pointer;color:#ef4444;">
                Clear All ACIMS Caches
            </button>
        </div>
        <p id="action-result" style="margin-top:10px;font-size:12px;color:#64748b;"></p>
    </div>

</div>

<script>
(async function () {
    // ── SW state ──────────────────────────────────────────────────────────────
    const swEl = document.getElementById('sw-state');
    if (!('serviceWorker' in navigator)) {
        swEl.textContent = 'Service Worker not supported in this browser.';
    } else {
        const reg = await navigator.serviceWorker.getRegistration('/');
        if (!reg) {
            swEl.innerHTML = row('Status', '<span style="color:#ef4444;">Not registered</span>');
        } else {
            const active  = reg.active  ? `<span style="color:#059669;">${reg.active.state}</span>`  : '—';
            const waiting = reg.waiting ? `<span style="color:#d97706;">waiting (update available)</span>` : '—';
            const installing = reg.installing ? `<span style="color:#3b82f6;">installing…</span>` : '—';
            swEl.innerHTML = [
                row('Scope',       reg.scope),
                row('Active',      active),
                row('Waiting',     waiting),
                row('Installing',  installing),
                row('Update via',  reg.updateViaCache),
            ].join('');
        }
    }

    // ── BG Sync ───────────────────────────────────────────────────────────────
    const syncEl = document.getElementById('sync-state');
    const hasBGSync = 'serviceWorker' in navigator && 'SyncManager' in window;
    let syncRows = [row('SyncManager', hasBGSync
        ? '<span style="color:#059669;">Supported</span>'
        : '<span style="color:#d97706;">Not supported (iOS/Firefox)</span>')];

    if (hasBGSync) {
        try {
            const reg  = await navigator.serviceWorker.ready;
            const tags = await reg.sync.getTags();
            syncRows.push(row('Registered tags', tags.length ? tags.join(', ') : '(none)'));
        } catch (e) {
            syncRows.push(row('Tags', `Error: ${e.message}`));
        }
    }
    syncEl.innerHTML = syncRows.join('');

    // ── Timestamps ────────────────────────────────────────────────────────────
    const tsEl = document.getElementById('ts-state');
    const tsKeys = [
        ['acims_last_sync_registered',         'Draft sync registered'],
        ['acims_last_sync_completed',           'Draft sync completed'],
        ['acims_last_officer_sync_registered',  'Officer sync registered'],
    ];
    tsEl.innerHTML = tsKeys.map(([k, label]) => {
        const val = localStorage.getItem(k);
        return row(label, val ? new Date(val).toLocaleString() : '<span style="color:#94a3b8;">(not set)</span>');
    }).join('');

    // ── Cache Storage ─────────────────────────────────────────────────────────
    const cacheEl = document.getElementById('cache-state');
    if (!('caches' in window)) {
        cacheEl.textContent = 'Cache Storage not available.';
    } else {
        const keys = await caches.keys();
        const acims = keys.filter(k => k.startsWith('acims-'));
        if (!acims.length) {
            cacheEl.textContent = 'No ACIMS caches found.';
        } else {
            const rows = await Promise.all(acims.map(async k => {
                const cache = await caches.open(k);
                const reqs  = await cache.keys();
                return row(k, `${reqs.length} entries`);
            }));
            cacheEl.innerHTML = rows.join('');
        }
    }

    // ── IDB ───────────────────────────────────────────────────────────────────
    async function readIdbStore(storeName) {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open('acims-offline', 2);
            req.onerror   = () => reject(req.error);
            req.onsuccess = () => {
                const db = req.result;
                if (!db.objectStoreNames.contains(storeName)) { db.close(); resolve([]); return; }
                const tx  = db.transaction(storeName, 'readonly');
                const all = tx.objectStore(storeName).getAll();
                all.onsuccess = () => { db.close(); resolve(all.result ?? []); };
                all.onerror   = () => { db.close(); reject(all.error); };
            };
            req.onupgradeneeded = e => e.target.result.close(); // already up to date
        });
    }

    async function renderIdb(elId, storeName, labelFn) {
        const el = document.getElementById(elId);
        try {
            const records = await readIdbStore(storeName);
            if (!records.length) { el.textContent = 'No records.'; return; }
            el.innerHTML = records.map(r => row(labelFn(r), statusBadge(r.status) + (r.error ? ` — <span style="color:#ef4444;font-size:11px;">${r.error}</span>` : ''))).join('');
        } catch (e) {
            el.textContent = `Error: ${e.message}`;
        }
    }

    await renderIdb('idb-drafts', 'drafts',
        r => `${r.clearance_type} · ${r.academic_year} · ${r.semester}`);

    await renderIdb('idb-officer', 'officer_actions',
        r => `${r.action} · approval #${r.approval_id}`);

    // ── Actions ───────────────────────────────────────────────────────────────
    document.getElementById('btn-reload').addEventListener('click', () => location.reload());

    document.getElementById('btn-skip-waiting').addEventListener('click', async () => {
        const reg = await navigator.serviceWorker.getRegistration('/');
        if (reg?.waiting) {
            reg.waiting.postMessage({ type: 'SKIP_WAITING' });
            document.getElementById('action-result').textContent = 'SKIP_WAITING sent. Reloading…';
            setTimeout(() => location.reload(), 800);
        } else {
            document.getElementById('action-result').textContent = 'No waiting service worker.';
        }
    });

    document.getElementById('btn-clear-cache').addEventListener('click', async () => {
        const keys   = await caches.keys();
        const acims  = keys.filter(k => k.startsWith('acims-'));
        await Promise.all(acims.map(k => caches.delete(k)));
        document.getElementById('action-result').textContent = `Deleted ${acims.length} cache(s). Reload to re-populate.`;
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    function row(label, value) {
        return `<div style="display:flex;gap:10px;padding:5px 0;border-bottom:1px solid #f1f5f9;">
            <span style="min-width:220px;font-size:12px;font-weight:600;color:#64748b;">${label}</span>
            <span style="font-size:12px;color:#1e293b;">${value}</span>
        </div>`;
    }

    function statusBadge(status) {
        const map = {
            pending:      ['#fef3c7','#fde68a','#92400e'],
            pending_sync: ['#eff6ff','#bfdbfe','#1d4ed8'],
            syncing:      ['#eff6ff','#bfdbfe','#1d4ed8'],
            synced:       ['#f0fdf4','#a7f3d0','#065f46'],
            failed:       ['#fef2f2','#fecaca','#991b1b'],
        };
        const [bg, border, color] = map[status] ?? ['#f8fafc','#e2e8f0','#64748b'];
        return `<span style="font-size:10px;background:${bg};border:1px solid ${border};color:${color};padding:1px 7px;border-radius:999px;font-weight:700;">${status}</span>`;
    }
})();
</script>
@endsection
