import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { execSync } from 'child_process';
import crypto from 'crypto';

// Runs after every production build to write public/sw.js from sw-source.js.
// execSync cwd is the project root (npm always runs from where package.json lives).
function pwaPlugin() {
    return {
        name: 'acims-pwa',
        closeBundle() {
            if (process.env.NODE_ENV === 'production') {
                console.log('[acims-pwa] Generating service worker…');
                try {
                    execSync('node frontend/scripts/build-sw.js', { stdio: 'inherit' });
                } catch (e) {
                    console.error('[acims-pwa] SW build failed:', e.message);
                }
            }
        },
    };
}

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
            // All paths relative to the project root (process.cwd() when npm runs).
            input: [
                'frontend/css/app.css',
                'frontend/js/app.js',
                'frontend/js/push/subscribe.js',
                'frontend/js/qr/my-card.js',
                'frontend/js/qr/scanner.js',
            ],
            refresh: [
                'resources/views/**/*.blade.php',
                'routes/**/*.php',
            ],
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
