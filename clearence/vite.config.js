import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { execSync } from 'child_process';
import crypto from 'crypto';

// ── PWA post-build plugin ─────────────────────────────────────────────────────
// Runs `node scripts/build-sw.js` after every production build so that
// public/sw.js is always in sync with the Vite manifest.
function pwaPlugin() {
    return {
        name: 'acims-pwa',
        closeBundle() {
            if (process.env.NODE_ENV === 'production') {
                console.log('[acims-pwa] Generating service worker…');
                try {
                    execSync('node scripts/build-sw.js', { stdio: 'inherit' });
                } catch (e) {
                    console.error('[acims-pwa] SW build failed:', e.message);
                }
            }
        },
    };
}

// Derive a short build ID from the current timestamp so __SW_BUILD__ is
// available to Vite-bundled files (app.js → register-sw.js if needed).
const SW_BUILD = crypto
    .createHash('sha1')
    .update(Date.now().toString())
    .digest('hex')
    .slice(0, 8);

export default defineConfig({
    define: {
        __SW_BUILD__: JSON.stringify(SW_BUILD),
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/push/subscribe.js',
                'resources/js/qr/my-card.js',
                'resources/js/qr/scanner.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
        pwaPlugin(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
