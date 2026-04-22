// ── ACIMS Service Worker — source template ────────────────────────────────────
// DO NOT edit public/sw.js directly in production; edit this file and run:
//   npm run build   (vite build && node scripts/build-sw.js)
//
// __SW_BUILD__ is replaced by scripts/build-sw.js with the Vite manifest hash.
// self.__WB_MANIFEST is injected by workbox-build.injectManifest().

// ── Cache names (all prefixed "acims-" so activate can prune old versions) ──
const BUILD        = '__SW_BUILD__';
const SHELL_CACHE  = `acims-shell-${BUILD}`;   // app shell HTML/layout JS+CSS
const ASSET_CACHE  = `acims-assets-${BUILD}`;  // Vite hashed assets + images
const DATA_CACHE   = `acims-data-${BUILD}`;    // student/officer/admin routes

const CURRENT_CACHES = [SHELL_CACHE, ASSET_CACHE, DATA_CACHE];

// ── Precache manifest (injected by workbox-build, empty [] in dev) ────────────
const PRECACHE = (self.__WB_MANIFEST || []).map(e => (typeof e === 'string' ? e : e.url));

// ─────────────────────────────────────────────────────────────────────────────
// INSTALL — cache offline page + any precache entries from the Vite manifest.
// Do NOT call skipWaiting() here; updates are user-confirmed via the update
// toast in register-sw.js which sends a SKIP_WAITING message.
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(SHELL_CACHE).then(cache =>
            cache.addAll(['/offline', ...PRECACHE].filter(Boolean)).catch(() => {})
        )
    );
});

// ─────────────────────────────────────────────────────────────────────────────
// ACTIVATE — delete any stale "acims-*" caches that aren't in CURRENT_CACHES,
// then claim clients so the new SW serves requests on the current page.
// clients.claim() here is safe because activate only fires either on fresh
// install (no prior SW) or after the user confirmed the update via SKIP_WAITING.
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys()
            .then(keys => {
                const stale = keys.filter(
                    k => k.startsWith('acims-') && !CURRENT_CACHES.includes(k)
                );
                return Promise.all(stale.map(k => caches.delete(k)));
            })
            .then(() => self.clients.claim())
    );
});

// ─────────────────────────────────────────────────────────────────────────────
// MESSAGE — handles SKIP_WAITING (from update toast) and notification polling.
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('message', e => {
    if (e.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
        return;
    }

    if (e.data?.type === 'POLL_NOTIFICATIONS') {
        fetch('/notifications/unread', { credentials: 'include' })
            .then(r => r.json())
            .then(data => {
                if (data.count > 0 && data.items?.length) {
                    const latest = data.items[0];
                    self.registration.showNotification('MUST Clearance', {
                        body:    latest.message,
                        icon:    '/images/pwa-icons/icon-192.png',
                        badge:   '/images/pwa-icons/icon-96.png',
                        tag:     'must-notif-' + latest.id,
                        data:    { url: '/student/clearances/' + latest.clearance_id },
                        vibrate: [200, 100, 200],
                    });
                }
            })
            .catch(() => {});
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// PUSH — rich server-sent push notification
//
// Expected payload (from DepartmentApprovalNotification.toWebPush):
//   { title, body, url, clearance_id, status, require_interaction, icon, badge }
//
// Tag: "clearance-{id}" — subsequent pushes for the same clearance silently
// replace the prior notification so the student always sees the latest status.
//
// require_interaction: true only when the whole clearance reaches a terminal
// state ('approved'/'rejected'); false for intermediate department approvals.
//
// Actions note: "approve" action is intentionally absent. Push notifications
// are delivered to students, not officers — students cannot approve their own
// clearance. A future OfficerPendingNotification could add that action.
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('push', e => {
    const data = e.data ? e.data.json() : {};

    const title   = data.title  || 'ACIMS — MUST';
    const body    = data.body   || data.message || 'You have a new notification.';
    const url     = data.url    || '/';
    const icon    = data.icon   || '/images/pwa-icons/icon-192.png';
    const badge   = data.badge  || '/images/pwa-icons/icon-96.png';
    const tag     = data.clearance_id ? `clearance-${data.clearance_id}` : 'acims-notif';
    const isFinal = data.require_interaction === true;

    e.waitUntil(
        self.registration.showNotification(title, {
            body,
            icon,
            badge,
            tag,
            renotify:             true,
            requireInteraction:   isFinal,
            vibrate:              isFinal ? [300, 100, 300, 100, 300] : [200, 100, 200],
            data:                 { url },
            actions: [
                { action: 'view',    title: 'View' },
                { action: 'dismiss', title: 'Dismiss' },
            ],
        })
    );
});

// ─────────────────────────────────────────────────────────────────────────────
// NOTIFICATION CLICK — navigate to the notification's URL
//
// action='view' or default click: open/focus the app at data.url.
// action='dismiss': close only (no navigation).
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('notificationclick', e => {
    e.notification.close();

    if (e.action === 'dismiss') return;

    const target = e.notification.data?.url || '/';

    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(cs => {
            // Prefer a tab already at the target URL
            const exact = cs.find(c => c.url === location.origin + target);
            if (exact) return exact.focus();

            // Otherwise focus any open tab and navigate it there
            const any = cs.find(c => c.url.startsWith(location.origin));
            if (any) return any.focus().then(() => any.navigate(target));

            // No open tab — open a new one
            return clients.openWindow(target);
        })
    );
});

// ─────────────────────────────────────────────────────────────────────────────
// FETCH — route-based caching strategies
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', e => {
    const req = e.request;
    const url = new URL(req.url);

    // ── External CDN (Tailwind) → StaleWhileRevalidate ──────────────────────
    if (url.origin !== location.origin) {
        if (url.host.includes('tailwindcss.com') || url.host.includes('cdn.')) {
            e.respondWith(staleWhileRevalidate(req, SHELL_CACHE));
        }
        // All other cross-origin: fall through to browser default
        return;
    }

    // ── Mutations (POST/PUT/PATCH/DELETE) → NetworkOnly ──────────────────────
    // Background Sync can be added here in a future step.
    if (req.method !== 'GET') return;

    // ── Notification polling endpoint → always NetworkOnly ───────────────────
    if (url.pathname === '/notifications/unread') return;

    // ── Vite hashed assets (/build/*) → CacheFirst, 30-day TTL ──────────────
    // These filenames contain a hash so they are safe to cache indefinitely.
    if (url.pathname.startsWith('/build/')) {
        e.respondWith(cacheFirst(req, ASSET_CACHE, 30 * 24 * 3600));
        return;
    }

    // ── Static assets (images, fonts, icons) → CacheFirst, 7-day TTL ────────
    if (/\.(png|jpe?g|gif|svg|ico|webp|woff2?)(\?.*)?$/.test(url.pathname)) {
        e.respondWith(cacheFirst(req, ASSET_CACHE, 7 * 24 * 3600));
        return;
    }

    // ── App data routes → NetworkFirst, 5 s timeout, 24 h stale fallback ─────
    // NOTE: your routes are /student/*, /officer/*, /admin/* — not /api/v1/*.
    // Update this list if you add an API prefix in future.
    if (
        url.pathname.startsWith('/student/') ||
        url.pathname.startsWith('/officer/') ||
        url.pathname.startsWith('/admin/')   ||
        url.pathname.startsWith('/profile')  ||
        url.pathname.startsWith('/notifications')
    ) {
        e.respondWith(networkFirst(req, DATA_CACHE, 5000, 24 * 3600));
        return;
    }

    // ── Navigation requests (HTML pages) → NetworkFirst, offline fallback ────
    if (req.mode === 'navigate') {
        e.respondWith(navigateWithFallback(req));
        return;
    }

    // ── Everything else (app shell, login page CSS etc.) → StaleWhileRevalidate
    e.respondWith(staleWhileRevalidate(req, SHELL_CACHE));
});

// ─────────────────────────────────────────────────────────────────────────────
// Strategy helpers
// ─────────────────────────────────────────────────────────────────────────────

async function cacheFirst(req, cacheName, maxAgeSec) {
    const cache  = await caches.open(cacheName);
    const cached = await cache.match(req);

    if (cached) {
        const dateHeader = cached.headers.get('date');
        const age = dateHeader
            ? (Date.now() - new Date(dateHeader).getTime()) / 1000
            : 0;
        if (!maxAgeSec || age < maxAgeSec) return cached;
        // Stale: serve cached copy, refresh in background
        fetch(req).then(r => { if (r.ok) cache.put(req, r.clone()); }).catch(() => {});
        return cached;
    }

    const res = await fetch(req);
    if (res.ok || res.type === 'opaque') cache.put(req, res.clone());
    return res;
}

async function staleWhileRevalidate(req, cacheName) {
    const cache  = await caches.open(cacheName);
    const cached = await cache.match(req);

    const revalidate = fetch(req)
        .then(res => {
            if (res.ok && res.type !== 'opaque') cache.put(req, res.clone());
            return res;
        })
        .catch(() => null);

    return cached ?? revalidate;
}

async function networkFirst(req, cacheName, timeoutMs, maxAgeSec) {
    const cache = await caches.open(cacheName);

    try {
        const ctrl    = new AbortController();
        const timer   = setTimeout(() => ctrl.abort(), timeoutMs);
        const res     = await fetch(req, { signal: ctrl.signal });
        clearTimeout(timer);
        if (res.ok) cache.put(req, res.clone());
        return res;
    } catch {
        const cached = await cache.match(req);
        if (cached) {
            // Optionally check staleness here (maxAgeSec)
            return cached;
        }
        throw new Error('networkFirst: network timed out and no cache entry');
    }
}

async function navigateWithFallback(req) {
    try {
        const ctrl  = new AbortController();
        const timer = setTimeout(() => ctrl.abort(), 5000);
        const res   = await fetch(req, { signal: ctrl.signal });
        clearTimeout(timer);
        return res;
    } catch {
        const cached = await caches.match(req);
        if (cached) return cached;
        const offline = await caches.match('/offline');
        return offline ?? new Response('You are offline.', {
            status:  503,
            headers: { 'Content-Type': 'text/plain' },
        });
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// BACKGROUND SYNC
// Fires when the browser regains connectivity and a sync tag is pending.
// Uses raw IndexedDB API — the idb npm library is not available in SW scope.
//
// Tags:
//   "submit-clearance-requests" — student draft clearances (status: pending_sync)
//   "submit-officer-actions"    — officer approve/reject queued while offline
//
// Drafts with status "pending" are handled by the window 'online' path
// (initSyncManager in sync-manager.js) for iOS Safari which lacks SyncManager.
// ─────────────────────────────────────────────────────────────────────────────

const SW_SYNC_TAG_DRAFTS  = 'submit-clearance-requests';
const SW_SYNC_TAG_OFFICER = 'submit-officer-actions';

self.addEventListener('sync', e => {
    if (e.tag === SW_SYNC_TAG_DRAFTS)  e.waitUntil(swSyncDrafts());
    if (e.tag === SW_SYNC_TAG_OFFICER) e.waitUntil(swSyncOfficerActions());
});

// ── Raw IDB helpers ───────────────────────────────────────────────────────────

function swIdbOpen() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('acims-offline', 2);
        req.onerror   = () => reject(req.error);
        req.onsuccess = () => resolve(req.result);
        req.onupgradeneeded = ev => {
            const db = ev.target.result;
            if (!db.objectStoreNames.contains('drafts')) {
                const s = db.createObjectStore('drafts', { keyPath: 'id' });
                s.createIndex('status',    'status',          { unique: false });
                s.createIndex('created_at','created_at',      { unique: false });
                s.createIndex('idem_key',  'idempotency_key', { unique: true  });
            }
            if (!db.objectStoreNames.contains('officer_actions')) {
                const oa = db.createObjectStore('officer_actions', { keyPath: 'id' });
                oa.createIndex('status',    'status',          { unique: false });
                oa.createIndex('created_at','created_at',      { unique: false });
                oa.createIndex('idem_key',  'idempotency_key', { unique: true  });
            }
        };
    });
}

function swIdbGetPending(db, storeName) {
    return new Promise((resolve, reject) => {
        const req = db.transaction(storeName, 'readonly')
                      .objectStore(storeName)
                      .index('status')
                      .getAll('pending_sync');
        req.onsuccess = () => resolve(req.result ?? []);
        req.onerror   = () => reject(req.error);
    });
}

function swIdbGetOne(db, storeName, id) {
    return new Promise((resolve, reject) => {
        const req = db.transaction(storeName, 'readonly')
                      .objectStore(storeName)
                      .get(id);
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    });
}

function swIdbPut(db, storeName, record) {
    return new Promise((resolve, reject) => {
        const req = db.transaction(storeName, 'readwrite')
                      .objectStore(storeName)
                      .put(record);
        req.onsuccess = () => resolve();
        req.onerror   = () => reject(req.error);
    });
}

function swPostToClients(msg) {
    return self.clients
        .matchAll({ type: 'window', includeUncontrolled: true })
        .then(cs => Promise.all(cs.map(c => c.postMessage(msg))));
}

// ── Clearance draft sync ──────────────────────────────────────────────────────

async function swSyncDrafts() {
    const db     = await swIdbOpen();
    const drafts = await swIdbGetPending(db, 'drafts');

    for (const draft of drafts) {
        await swIdbPut(db, 'drafts', { ...draft, status: 'syncing' });

        try {
            const body = new FormData();
            body.append('_token',         draft.csrf_token    ?? '');
            body.append('academic_year',  draft.academic_year ?? '');
            body.append('semester',       draft.semester       ?? '');
            body.append('clearance_type', draft.clearance_type ?? '');
            if (draft.reason) body.append('reason', draft.reason);

            const res = await fetch('/student/clearances', {
                method:      'POST',
                credentials: 'include',
                headers: {
                    'X-Idempotency-Key': draft.idempotency_key,
                    'X-Requested-With':  'XMLHttpRequest',
                },
                body,
                redirect: 'follow',
            });

            if (res.ok || res.redirected) {
                await swIdbPut(db, 'drafts', { ...draft, status: 'synced', error: null });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'drafts', id: draft.id, success: true });
            } else if (res.status === 422) {
                // Validation error — don't retry, mark permanently failed
                await swIdbPut(db, 'drafts', { ...draft, status: 'failed', error: `Validation error (${res.status})` });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'drafts', id: draft.id, success: false, error: `HTTP ${res.status}` });
            } else {
                const errMsg = `HTTP ${res.status}`;
                await swIdbPut(db, 'drafts', { ...draft, status: 'pending_sync', error: errMsg });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'drafts', id: draft.id, success: false, error: errMsg });
                throw new Error(errMsg); // signal browser to retry the sync tag
            }
        } catch (err) {
            const current = await swIdbGetOne(db, 'drafts', draft.id);
            if (current?.status === 'syncing') {
                await swIdbPut(db, 'drafts', { ...draft, status: 'pending_sync', error: err.message });
            }
            db.close();
            throw err;
        }
    }
    db.close();
}

// ── Officer action sync ───────────────────────────────────────────────────────

async function swSyncOfficerActions() {
    const db      = await swIdbOpen();
    const actions = await swIdbGetPending(db, 'officer_actions');

    for (const action of actions) {
        await swIdbPut(db, 'officer_actions', { ...action, status: 'syncing' });

        const url = `/officer/approvals/${action.approval_id}/${action.action}`;

        try {
            const body = new FormData();
            body.append('_token', action.csrf_token ?? '');
            if (action.comments) body.append('comments', action.comments);

            const res = await fetch(url, {
                method:      'POST',
                credentials: 'include',
                headers: {
                    'X-Idempotency-Key': action.idempotency_key,
                    'X-Requested-With':  'XMLHttpRequest',
                },
                body,
                redirect: 'follow',
            });

            if (res.ok || res.redirected) {
                await swIdbPut(db, 'officer_actions', { ...action, status: 'synced', error: null });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'officer_actions', id: action.id, success: true });
            } else {
                // 419 = session expired; 422 = validation; neither should be retried
                const errMsg = res.status === 419
                    ? 'Session expired — reload the page and try again'
                    : `HTTP ${res.status}`;
                const nextStatus = (res.status === 419 || res.status === 422) ? 'failed' : 'pending_sync';
                await swIdbPut(db, 'officer_actions', { ...action, status: nextStatus, error: errMsg });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'officer_actions', id: action.id, success: false, error: errMsg });
                if (nextStatus === 'pending_sync') throw new Error(errMsg);
            }
        } catch (err) {
            const current = await swIdbGetOne(db, 'officer_actions', action.id);
            if (current?.status === 'syncing') {
                await swIdbPut(db, 'officer_actions', { ...action, status: 'pending_sync', error: err.message });
            }
            db.close();
            throw err;
        }
    }
    db.close();
}
