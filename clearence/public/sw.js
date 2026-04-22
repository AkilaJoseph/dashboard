// ── ACIMS Service Worker ───────────────────────────────────────────────────────
// Generated from resources/js/pwa/sw-source.js
// BUILD: dev-20260421
// To regenerate for production: npm run build
// ─────────────────────────────────────────────────────────────────────────────

const BUILD        = 'dev-20260421';
const SHELL_CACHE  = `acims-shell-${BUILD}`;
const ASSET_CACHE  = `acims-assets-${BUILD}`;
const DATA_CACHE   = `acims-data-${BUILD}`;

const CURRENT_CACHES = [SHELL_CACHE, ASSET_CACHE, DATA_CACHE];

// Precache manifest — empty in dev; injected by scripts/build-sw.js on npm run build
const PRECACHE = (self.__WB_MANIFEST || []).map(e => (typeof e === 'string' ? e : e.url));

// ─────────────────────────────────────────────────────────────────────────────
// INSTALL
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(SHELL_CACHE).then(cache =>
            cache.addAll(['/offline', ...PRECACHE].filter(Boolean)).catch(() => {})
        )
    );
    // No skipWaiting() — updates are user-confirmed via the toast in register-sw.js
});

// ─────────────────────────────────────────────────────────────────────────────
// ACTIVATE — prune stale acims-* caches, then claim clients
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
// MESSAGE
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
// PUSH
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('push', e => {
    const data = e.data ? e.data.json() : {};
    e.waitUntil(
        self.registration.showNotification(data.title || 'ACIMS — MUST', {
            body:    data.message || 'You have a new notification.',
            icon:    '/images/pwa-icons/icon-192.png',
            badge:   '/images/pwa-icons/icon-96.png',
            data:    { url: data.url || '/' },
            vibrate: [200, 100, 200],
        })
    );
});

// ─────────────────────────────────────────────────────────────────────────────
// NOTIFICATION CLICK
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('notificationclick', e => {
    e.notification.close();
    const target = e.notification.data?.url || '/';
    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(cs => {
            const match = cs.find(c => c.url.includes(location.origin));
            return match ? match.focus() : clients.openWindow(target);
        })
    );
});

// ─────────────────────────────────────────────────────────────────────────────
// FETCH
// ─────────────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', e => {
    const req = e.request;
    const url = new URL(req.url);

    // External CDN → StaleWhileRevalidate
    if (url.origin !== location.origin) {
        if (url.host.includes('tailwindcss.com') || url.host.includes('cdn.')) {
            e.respondWith(staleWhileRevalidate(req, SHELL_CACHE));
        }
        return;
    }

    // Mutations → NetworkOnly (Background Sync can be wired here later)
    if (req.method !== 'GET') return;

    // Notification polling → always network, never cached
    if (url.pathname === '/notifications/unread') return;

    // /build/* Vite hashed assets → CacheFirst, 30-day TTL
    if (url.pathname.startsWith('/build/')) {
        e.respondWith(cacheFirst(req, ASSET_CACHE, 30 * 24 * 3600));
        return;
    }

    // Static assets (images, fonts) → CacheFirst, 7-day TTL
    if (/\.(png|jpe?g|gif|svg|ico|webp|woff2?)(\?.*)?$/.test(url.pathname)) {
        e.respondWith(cacheFirst(req, ASSET_CACHE, 7 * 24 * 3600));
        return;
    }

    // App data routes → NetworkFirst, 5 s timeout, 24 h stale fallback
    // NOTE: /api/v1/student/* does not exist in this app; routes are /student/*
    if (
        url.pathname.startsWith('/student/')     ||
        url.pathname.startsWith('/officer/')     ||
        url.pathname.startsWith('/admin/')       ||
        url.pathname.startsWith('/profile')      ||
        url.pathname.startsWith('/notifications')
    ) {
        e.respondWith(networkFirst(req, DATA_CACHE, 5000, 24 * 3600));
        return;
    }

    // Navigation (full page loads) → NetworkFirst, offline fallback
    if (req.mode === 'navigate') {
        e.respondWith(navigateWithFallback(req));
        return;
    }

    // Everything else (login page, layout assets) → StaleWhileRevalidate
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
        // Stale: serve cached, refresh in background
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
        const ctrl  = new AbortController();
        const timer = setTimeout(() => ctrl.abort(), timeoutMs);
        const res   = await fetch(req, { signal: ctrl.signal });
        clearTimeout(timer);
        if (res.ok) cache.put(req, res.clone());
        return res;
    } catch {
        const cached = await cache.match(req);
        if (cached) return cached;
        throw new Error('networkFirst: timed out and no cached response');
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
// ─────────────────────────────────────────────────────────────────────────────

const SW_SYNC_TAG_DRAFTS  = 'submit-clearance-requests';
const SW_SYNC_TAG_OFFICER = 'submit-officer-actions';

self.addEventListener('sync', e => {
    if (e.tag === SW_SYNC_TAG_DRAFTS)  e.waitUntil(swSyncDrafts());
    if (e.tag === SW_SYNC_TAG_OFFICER) e.waitUntil(swSyncOfficerActions());
});

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
                method: 'POST', credentials: 'include',
                headers: { 'X-Idempotency-Key': draft.idempotency_key, 'X-Requested-With': 'XMLHttpRequest' },
                body, redirect: 'follow',
            });
            if (res.ok || res.redirected) {
                await swIdbPut(db, 'drafts', { ...draft, status: 'synced', error: null });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'drafts', id: draft.id, success: true });
            } else if (res.status === 422) {
                await swIdbPut(db, 'drafts', { ...draft, status: 'failed', error: `Validation error (${res.status})` });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'drafts', id: draft.id, success: false, error: `HTTP ${res.status}` });
            } else {
                const errMsg = `HTTP ${res.status}`;
                await swIdbPut(db, 'drafts', { ...draft, status: 'pending_sync', error: errMsg });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'drafts', id: draft.id, success: false, error: errMsg });
                throw new Error(errMsg);
            }
        } catch (err) {
            const current = await swIdbGetOne(db, 'drafts', draft.id);
            if (current?.status === 'syncing') await swIdbPut(db, 'drafts', { ...draft, status: 'pending_sync', error: err.message });
            db.close(); throw err;
        }
    }
    db.close();
}

async function swSyncOfficerActions() {
    const db      = await swIdbOpen();
    const actions = await swIdbGetPending(db, 'officer_actions');
    for (const action of actions) {
        await swIdbPut(db, 'officer_actions', { ...action, status: 'syncing' });
        const url  = `/officer/approvals/${action.approval_id}/${action.action}`;
        try {
            const body = new FormData();
            body.append('_token', action.csrf_token ?? '');
            if (action.comments) body.append('comments', action.comments);
            const res = await fetch(url, {
                method: 'POST', credentials: 'include',
                headers: { 'X-Idempotency-Key': action.idempotency_key, 'X-Requested-With': 'XMLHttpRequest' },
                body, redirect: 'follow',
            });
            if (res.ok || res.redirected) {
                await swIdbPut(db, 'officer_actions', { ...action, status: 'synced', error: null });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'officer_actions', id: action.id, success: true });
            } else {
                const errMsg = res.status === 419 ? 'Session expired — reload the page and try again' : `HTTP ${res.status}`;
                const nextStatus = (res.status === 419 || res.status === 422) ? 'failed' : 'pending_sync';
                await swIdbPut(db, 'officer_actions', { ...action, status: nextStatus, error: errMsg });
                await swPostToClients({ type: 'SYNC_COMPLETE', store: 'officer_actions', id: action.id, success: false, error: errMsg });
                if (nextStatus === 'pending_sync') throw new Error(errMsg);
            }
        } catch (err) {
            const current = await swIdbGetOne(db, 'officer_actions', action.id);
            if (current?.status === 'syncing') await swIdbPut(db, 'officer_actions', { ...action, status: 'pending_sync', error: err.message });
            db.close(); throw err;
        }
    }
    db.close();
}
