/**
 * my-card.js — QR token refresh + IndexedDB caching for the student ID card.
 *
 * Refreshes the token 30 s before it expires (every ~4.5 min for 5-min tokens).
 * On network failure falls back to the IDB cache (up to 1 hour from issuance).
 *
 * Usage (from a Blade <script type="module">):
 *   import QrCard from '/build/qr/my-card.js';
 *   const card = new QrCard({ apiUrl, csrfToken, expiresAt, clearanceId,
 *                              onNewSvg, onTimer, onStatus, onOffline });
 *   card.start();
 */

import { saveQrToken, getCachedQrToken } from '../offline/draft-store.js';

const REFRESH_BEFORE_EXPIRY = 30;  // seconds — refresh window before JWT expiry

export default class QrCard {
    #opts;
    #expiresAt;
    #timerInterval = null;
    #refreshTimer  = null;
    #offline       = false;

    constructor(opts) {
        this.#opts      = opts;
        this.#expiresAt = new Date(opts.expiresAt);
    }

    start() {
        this.#startTimer();
        this.#scheduleRefresh();
        this.#watchNetwork();

        // Persist the initial server-rendered token to IDB
        // (svg is already in the DOM — we just need the token + metadata)
        // We call the API once immediately to populate the cache with the full payload
        this.#populateCache();
    }

    async refresh() {
        this.#opts.onStatus?.('Refreshing…');
        try {
            const data = await this.#fetchToken();
            this.#applyToken(data);
            this.#opts.onStatus?.('');
        } catch (err) {
            this.#opts.onStatus?.('Could not refresh: ' + err.message);
        }
    }

    // ── Private ─────────────────────────────────────────────────────────────────

    async #populateCache() {
        try {
            const data = await this.#fetchToken();
            this.#applyToken(data);
        } catch {
            // Server-rendered SVG stays; try loading from IDB for offline hint
            const cached = await getCachedQrToken();
            if (cached) {
                this.#expiresAt = new Date(cached.expires_at);
            }
        }
    }

    async #fetchToken() {
        const res = await fetch(this.#opts.apiUrl, {
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': this.#opts.csrfToken,
            },
            credentials: 'same-origin',
        });

        if (! res.ok) {
            throw new Error(`HTTP ${res.status}`);
        }

        const data = await res.json();

        // Persist to IDB for offline fallback
        await saveQrToken(data).catch(() => {});

        return data;
    }

    #applyToken(data) {
        this.#expiresAt = new Date(data.expires_at);

        if (data.qr_svg) {
            this.#opts.onNewSvg?.(data.qr_svg);
        }

        this.#setOffline(false);
        this.#cancelRefreshTimer();
        this.#scheduleRefresh();
    }

    #scheduleRefresh() {
        this.#cancelRefreshTimer();

        const msUntilExpiry  = this.#expiresAt.getTime() - Date.now();
        const msUntilRefresh = Math.max(1000, msUntilExpiry - REFRESH_BEFORE_EXPIRY * 1000);

        this.#refreshTimer = setTimeout(async () => {
            if (this.#offline) {
                await this.#loadFromCache();
            } else {
                try {
                    const data = await this.#fetchToken();
                    this.#applyToken(data);
                } catch {
                    await this.#loadFromCache();
                }
            }
        }, msUntilRefresh);
    }

    async #loadFromCache() {
        const cached = await getCachedQrToken();
        if (cached) {
            this.#expiresAt = new Date(cached.expires_at);
            this.#opts.onNewSvg?.(cached.qr_svg);
            this.#opts.onStatus?.('Showing cached QR (offline)');
            this.#setOffline(true);
            // Retry refresh after the cache display_until window
            const displayUntil = new Date(cached.display_until).getTime();
            const delay = Math.max(60_000, displayUntil - Date.now());
            this.#refreshTimer = setTimeout(() => this.refresh(), delay);
        } else {
            this.#opts.onStatus?.('No cached QR available — connect to refresh.');
            this.#setOffline(true);
        }
    }

    #startTimer() {
        this.#timerInterval = setInterval(() => this.#tick(), 1000);
        this.#tick();
    }

    #tick() {
        const remaining = Math.max(0, Math.floor((this.#expiresAt.getTime() - Date.now()) / 1000));
        const m = String(Math.floor(remaining / 60)).padStart(1, '0');
        const s = String(remaining % 60).padStart(2, '0');

        if (remaining > 0) {
            this.#opts.onTimer?.('QR expires in', `${m}:${s}`);
        } else {
            this.#opts.onTimer?.('QR expired', '—');
        }
    }

    #watchNetwork() {
        window.addEventListener('online',  () => { if (this.#offline) this.refresh(); });
        window.addEventListener('offline', () => this.#setOffline(true));
        if (! navigator.onLine) this.#setOffline(true);
    }

    #setOffline(state) {
        this.#offline = state;
        this.#opts.onOffline?.(state);
    }

    #cancelRefreshTimer() {
        if (this.#refreshTimer !== null) {
            clearTimeout(this.#refreshTimer);
            this.#refreshTimer = null;
        }
    }
}
