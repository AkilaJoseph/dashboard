/**
 * scripts/build-sw.js
 *
 * Runs AFTER `vite build` as part of `npm run build`.
 * Uses workbox-build.injectManifest to:
 *   1. Read public/build/manifest.json (Vite output) and build a precache list
 *   2. Inject it into sw-source.js as self.__WB_MANIFEST
 *   3. Replace __SW_BUILD__ with a hash derived from the Vite manifest
 *   4. Write the result to public/sw.js
 */

import { injectManifest } from 'workbox-build';
import { readFileSync, writeFileSync, existsSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';
import crypto from 'crypto';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root      = resolve(__dirname, '..');

// ── Derive BUILD hash from Vite's manifest ────────────────────────────────────
function getBuildHash() {
    const viteMfPath = resolve(root, 'public/build/.vite/manifest.json');
    // Vite 5+ puts the manifest at public/build/.vite/manifest.json
    const altPath    = resolve(root, 'public/build/manifest.json');

    const mfPath = existsSync(viteMfPath) ? viteMfPath : (existsSync(altPath) ? altPath : null);

    if (!mfPath) {
        const ts = new Date().toISOString().slice(0, 10).replace(/-/g, '');
        console.warn(`[build-sw] Vite manifest not found — using date hash: ${ts}`);
        return ts;
    }

    const content = readFileSync(mfPath, 'utf-8');
    const hash    = crypto.createHash('sha1').update(content).digest('hex').slice(0, 8);
    console.log(`[build-sw] Build hash: ${hash}  (from ${mfPath})`);
    return hash;
}

// ── Run workbox-build.injectManifest ─────────────────────────────────────────
async function main() {
    const BUILD = getBuildHash();

    const { count, size, warnings } = await injectManifest({
        swSrc:  resolve(root, 'resources/js/pwa/sw-source.js'),
        swDest: resolve(root, 'public/sw.js'),
        globDirectory:  resolve(root, 'public'),
        globPatterns: [
            'build/**/*.{js,css}',    // Vite hashed JS + CSS
            'images/pwa-icons/*.png', // PWA icons
            'offline',                // served by Laravel, but cached by SW
        ],
        globIgnores: ['build/**/*.map'],
        // Avoid injecting the SW into its own precache
        dontCacheBustURLsMatching: /\/build\/.+\.[0-9a-f]{8}\./,
    });

    if (warnings.length) {
        console.warn('[build-sw] Workbox warnings:', warnings);
    }

    console.log(`[build-sw] Injected ${count} precache entries (${(size / 1024).toFixed(1)} kB)`);

    // Replace __SW_BUILD__ placeholder in the generated public/sw.js
    const swPath    = resolve(root, 'public/sw.js');
    const swContent = readFileSync(swPath, 'utf-8');
    const updated   = swContent.replace(/__SW_BUILD__/g, BUILD);
    writeFileSync(swPath, updated, 'utf-8');

    // Also stamp a readable header comment
    const stamped = updated.replace(
        /^\/\/ ── ACIMS Service Worker.*?\n/m,
        `// ── ACIMS Service Worker — BUILD: ${BUILD} — generated ${new Date().toISOString()}\n`
    );
    writeFileSync(swPath, stamped, 'utf-8');

    console.log(`[build-sw] public/sw.js written (BUILD=${BUILD})`);
}

main().catch(err => {
    console.error('[build-sw] FAILED:', err);
    process.exit(1);
});
