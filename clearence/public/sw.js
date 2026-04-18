const CACHE = 'must-cms-v1';
const OFFLINE_URL = '/offline';

const PRECACHE = [
    '/',
    '/offline',
    'https://cdn.tailwindcss.com',
];

// Install: cache shell
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll(PRECACHE).catch(() => {}))
    );
    self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch: network-first for HTML/API, cache-first for assets
self.addEventListener('fetch', e => {
    const url = new URL(e.request.url);

    // Skip non-GET and cross-origin except cdn
    if (e.request.method !== 'GET') return;
    if (url.origin !== location.origin && !url.host.includes('tailwindcss.com')) return;

    // API / notifications — network only, no cache
    if (url.pathname.startsWith('/notifications/unread')) return;

    e.respondWith(
        fetch(e.request)
            .then(res => {
                if (res.ok && res.type !== 'opaque') {
                    caches.open(CACHE).then(c => c.put(e.request, res.clone()));
                }
                return res;
            })
            .catch(() => caches.match(e.request).then(r => r || caches.match('/offline')))
    );
});

// Push: show notification from server
self.addEventListener('push', e => {
    const data = e.data ? e.data.json() : {};
    e.waitUntil(
        self.registration.showNotification(data.title || 'MUST CMS', {
            body: data.message || 'You have a new notification.',
            icon: '/images/icon-192.png',
            badge: '/images/icon-192.png',
            data: { url: data.url || '/' },
            vibrate: [200, 100, 200],
        })
    );
});

// Notification click: open the app
self.addEventListener('notificationclick', e => {
    e.notification.close();
    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(cs => {
            const url = e.notification.data?.url || '/';
            const match = cs.find(c => c.url.includes(location.origin));
            if (match) return match.focus();
            return clients.openWindow(url);
        })
    );
});

// Periodic polling: check for new notifications every 30s via message
self.addEventListener('message', e => {
    if (e.data?.type === 'POLL_NOTIFICATIONS') {
        fetch('/notifications/unread', { credentials: 'include' })
            .then(r => r.json())
            .then(data => {
                if (data.count > 0 && data.items?.length) {
                    const latest = data.items[0];
                    self.registration.showNotification('MUST Clearance', {
                        body: latest.message,
                        icon: '/images/icon-192.png',
                        tag: 'must-notif-' + latest.id,
                        data: { url: '/student/clearances/' + latest.clearance_id },
                        vibrate: [200, 100, 200],
                    });
                }
            })
            .catch(() => {});
    }
});
