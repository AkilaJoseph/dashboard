/**
 * subscribe.js
 *
 * Web Push subscription helpers — plain ES modules, no framework.
 *
 * Usage:
 *   import { isSupported, requestPermission, subscribe, unsubscribe } from './push/subscribe.js';
 *
 *   if (isSupported() && await requestPermission() === 'granted') {
 *       await subscribe(import.meta.env.VITE_VAPID_PUBLIC_KEY);
 *   }
 */

const SUBSCRIBE_URL   = '/api/v1/push/subscribe';
const UNSUBSCRIBE_URL = '/api/v1/push/unsubscribe';

/** True if this browser supports Web Push. */
export function isSupported() {
    return (
        'serviceWorker' in navigator &&
        'PushManager'   in window    &&
        'Notification'  in window
    );
}

/** Returns the current notification permission: 'default' | 'granted' | 'denied'. */
export function getPermissionState() {
    return Notification.permission;
}

/**
 * Ask the user for notification permission.
 * @returns {Promise<'granted'|'denied'|'default'>}
 */
export async function requestPermission() {
    return Notification.requestPermission();
}

/**
 * Subscribe this browser to push notifications and register the endpoint with
 * the server. Safe to call multiple times — server upserts on the endpoint.
 *
 * @param {string} serverPublicKey  VAPID public key (base64url, from VAPID_PUBLIC_KEY env)
 * @returns {Promise<PushSubscription>}
 */
export async function subscribe(serverPublicKey) {
    if (!isSupported()) throw new Error('Web Push is not supported in this browser.');

    const reg = await navigator.serviceWorker.ready;

    const subscription = await reg.pushManager.subscribe({
        userVisibleOnly:      true,
        applicationServerKey: urlBase64ToUint8Array(serverPublicKey),
    });

    await postToServer(SUBSCRIBE_URL, {
        endpoint:   subscription.endpoint,
        p256dh_key: arrayBufferToBase64(subscription.getKey('p256dh')),
        auth_key:   arrayBufferToBase64(subscription.getKey('auth')),
        user_agent: navigator.userAgent,
    });

    return subscription;
}

/**
 * Unsubscribe this browser from push notifications and remove the record from
 * the server.
 *
 * @returns {Promise<void>}
 */
export async function unsubscribe() {
    if (!isSupported()) return;

    const reg          = await navigator.serviceWorker.ready;
    const subscription = await reg.pushManager.getSubscription();
    if (!subscription) return;

    const endpoint = subscription.endpoint;
    await subscription.unsubscribe();

    await postToServer(UNSUBSCRIBE_URL, { endpoint });
}

// ── Private helpers ───────────────────────────────────────────────────────────

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function postToServer(url, data) {
    const res = await fetch(url, {
        method:      'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN':  getCsrfToken(),
            'Accept':        'application/json',
        },
        body: JSON.stringify(data),
    });
    if (!res.ok) {
        const json = await res.json().catch(() => ({}));
        throw new Error(json.message ?? `Server error ${res.status}`);
    }
    return res.json();
}

/** Convert a base64url VAPID public key string to a Uint8Array. */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw     = atob(base64);
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}

/** Convert an ArrayBuffer (from PushSubscription.getKey) to base64url string. */
function arrayBufferToBase64(buffer) {
    if (!buffer) return '';
    return btoa(String.fromCharCode(...new Uint8Array(buffer)))
        .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}
