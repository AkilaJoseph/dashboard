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
// PUSH — server-sent push notification
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
// NOTIFICATION CLICK — focus existing tab or open a new one
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
