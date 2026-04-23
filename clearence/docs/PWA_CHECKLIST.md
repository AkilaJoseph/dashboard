# PWA & Lighthouse Checklist — ACIMS

**Project:** MUST Automated Clearance Information Management System  
**Audited:** 2026-04-23  
**Method:** Static analysis of source (no live browser run)

---

## Lighthouse Categories

| Category | Estimated Score | Key Issues |
|---|---|---|
| Performance | ~55–65 | Render-blocking CDN Tailwind (C1), no image lazy-loading |
| Accessibility | ~78–84 (before fixes) → ~88–92 (after A-fixes) | Missing h1, unlabelled inputs, icon-only buttons |
| Best Practices | ~85 | Double manifest link, CDN script |
| SEO | ~72 (before A1) → ~82 (after A1) | Missing meta description |
| PWA | ~80 | SW present, manifest present, offline page present |

---

## ✅ Fixes Applied (Category A)

| ID | File | Change | Lighthouse Impact |
|---|---|---|---|
| A1 | `layouts/app.blade.php` | Added `<meta name="description">` | SEO +5–8 pts |
| A2 | `layouts/app.blade.php` | Topbar `<h2>` → `<h1>` (all auth pages had no h1) | Accessibility +3 pts |
| A3 | `layouts/app.blade.php` | `aria-label="Notifications"` + `aria-haspopup/expanded` on bell button | Accessibility +2 pts |
| A4 | `layouts/app.blade.php` | `aria-label="Account menu"` + `aria-haspopup/expanded` on avatar button | Accessibility +2 pts |
| A5 | `admin/push-campaigns/create.blade.php` | `alt=""` + `role="presentation"` on decorative notification preview icon | Accessibility +1 pt |
| A6 | `auth/login.blade.php` | `<span>` → `<label for="remember">` for "Keep me signed in" | Accessibility +1 pt |
| A7 | `auth/register.blade.php` | Added `for`/`id` pairs to all 10 label/input pairs | Accessibility +4–6 pts |

---

## 🔶 Pending Approval (Category B)

### B1 — Skip-to-content link
- **Issue:** No keyboard navigation bypass mechanism. Users tabbing into the page must tab through the entire sidebar before reaching main content.
- **Fix:** Add visually-hidden skip link as the first child of `<body>`, targeting `<main id="main-content">`.
- **Diff:**
  ```diff
  + <a href="#main-content"
  +    style="position:absolute;left:-9999px;top:4px;z-index:99999;
  +           background:#059669;color:#fff;padding:8px 16px;border-radius:0 0 8px 8px;
  +           font-size:13px;font-weight:700;text-decoration:none;"
  +    onfocus="this.style.left='4px'" onblur="this.style.left='-9999px'">
  +   Skip to main content
  + </a>
  ```
  ```diff
  - <main class="page-content"
  + <main id="main-content" class="page-content"
  ```
- **Expected impact:** Accessibility +3 pts, removes Lighthouse "bypass blocks" audit failure.

### B2 — Lazy-load non-critical images
- **Issue:** MUST logo `<img>` in sidebar (layout:382) and in clearance show preview (show:336) are not lazy-loaded. These are off-screen on most pages.
- **Fix:** Add `loading="lazy"` to both images.
- **Expected impact:** Performance LCP -0.2s on pages where logo is below fold.

### B3 — Dynamic `lang` attribute
- **Issue:** `<html lang="en">` is hardcoded in `layouts/app.blade.php` even after the locale switcher was implemented. Swahili content is announced as English to screen readers.
- **Fix:** Change to `<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">`.
- **Expected impact:** Accessibility +2 pts; screen reader pronunciation correct for Swahili content.

### B4 — `autocomplete` on admin user forms
- **Issue:** `admin/users/create.blade.php` and `admin/users/edit.blade.php` are missing `autocomplete` attributes on email/password fields. Browsers cannot pre-fill and Lighthouse flags this.
- **Fix:** Add `autocomplete="email"` / `autocomplete="new-password"` to the respective inputs.
- **Expected impact:** Best Practices +1 pt.

### B5 — Admin form label wiring (remaining views)
- **Issue:** Labels in the following admin/officer views are not programmatically associated with their inputs (no `for`/`id` pairs):
  - `admin/clearances/show.blade.php` (action, comments)
  - `admin/clearances/index.blade.php` (search, status, type filters)
  - `admin/sims/sync.blade.php` (email, password)
  - `officer/scan.blade.php` (manual token, comments)
  - `officer/approvals/show.blade.php` (comments, rejection reason)
  - `profile/show.blade.php` (email, password, confirm)
- **Fix:** Add `for`/`id` pairs to each affected label/input — same mechanical pattern as A7.
- **Expected impact:** Accessibility +2–3 pts combined.

---

## 🔴 Proposed Only — Awaiting Decision (Category C)

### C1 — Remove CDN Tailwind (HIGHEST PRIORITY)
- **Issue:** `<script src="https://cdn.tailwindcss.com">` in `layouts/app.blade.php` is a **synchronous render-blocking script**. It generates all CSS at runtime (~380 KB uncompressed). Lighthouse flags this as a critical render-blocking resource.
- **Context:** The app already bundles Tailwind via `@tailwindcss/vite` in `vite.config.js`. The CDN tag appears redundant.
- **Risk:** If any Blade template uses dynamically-constructed Tailwind class strings (e.g., `"text-"+color`) they won't be picked up by the content scanner and would break. Also `offline.blade.php` doesn't use the Vite bundle — confirm it doesn't use any Tailwind utilities.
- **Fix:** Remove the CDN `<script>` tag; audit `vite.config.js` content globs; add explicit `safelist` for any dynamic class patterns; run `npm run build` and test all views.
- **Expected impact:** Performance +15–25 pts (removes render-blocking resource, reduces total blocking time).

### C2 — Vite manual chunk splitting
- **Issue:** No `build.rollupOptions.output.manualChunks` in `vite.config.js`. All vendor code (html5-qrcode, chillerlan) is bundled with app code. Any app change invalidates the vendor cache.
- **Fix:** Add chunk config separating `node_modules` into a `vendor` chunk.
- **Expected impact:** Cache efficiency; Performance +3–5 pts on repeat visits.

### C3 — Service worker precache list update
- **Issue:** The SW precache list (`scripts/build-sw.js` or generated manifest) does not include the new prediction API route or the `/verify/*` public route. Cache-first strategies on these would fail offline; network-first strategies are not configured.
- **Fix:** Add `/api/v1/student/requests/*/prediction` as network-first with 5s timeout fallback; add `/verify/*` as stale-while-revalidate.
- **Expected impact:** PWA offline robustness.

### C4 — `<link rel="preconnect">` for CDN (temporary, while C1 is pending)
- **Fix:** Add `<link rel="preconnect" href="https://cdn.tailwindcss.com">` to `<head>` to reduce connection latency.
- **Expected impact:** Performance +1–2 pts.

---

## Colour Contrast Report (informational — no changes)

All values computed against WCAG 2.1 relative luminance formula.

| Element | Foreground | Background | Contrast Ratio | WCAG AA Normal (4.5:1) | WCAG AA Large (3:1) |
|---|---|---|---|---|---|
| `.btn-glow` text / active nav | `#ffffff` | `#059669` | **3.8:1** | ❌ Fails | ✅ Passes |
| Link colour | `#059669` | `#ffffff` | **3.8:1** | ❌ Fails | ✅ Passes |
| Body text | `#1e293b` | `#ffffff` | ~14.2:1 | ✅ AAA | ✅ AAA |
| Muted text | `#64748b` | `#ffffff` | ~4.6:1 | ✅ AA (just) | ✅ AA |
| Badge approved | `#065f46` | `#d1fae5` | ~6.7:1 | ✅ AAA | ✅ AAA |
| Badge pending | `#92400e` | `#fef3c7` | ~6.2:1 | ✅ AA | ✅ AAA |
| Sidebar nav inactive | `rgba(209,250,229,0.75)` | `#064e3b` | ~5.1:1 | ✅ AA | ✅ AA |

**Note:** The primary green `#059669` used for buttons, active states, and links fails WCAG AA at normal text sizes (13px/14px). No change made per instruction. Nearest compliant replacement: `#047857` (~5.2:1). The failure will be visible in a Lighthouse Accessibility audit as a flagged item.

---

## Heading Hierarchy — Per-Page Summary

| Page | Before fix | After A2 | Status |
|---|---|---|---|
| All authenticated pages (via layout) | First heading: `<h2>` (no h1) | First heading: `<h1>` | ✅ Fixed by A2 |
| `auth/login.blade.php` | `<h1>` brand, `<h2>` form | Unchanged | ✅ Correct |
| `auth/register.blade.php` | `<h1>` panel title | Unchanged | ✅ Correct |
| `offline.blade.php` | `<h1>` | Unchanged | ✅ Correct |
| `certificate/verify.blade.php` | `<h1>` | Unchanged | ✅ Correct |
| `student/clearances/show.blade.php` | `<h3>` as first content heading (after layout h1, skips h2) | Still skips h2 | ⚠️ Minor — flagged |

---

## PWA Specific Checks

| Check | Status | Notes |
|---|---|---|
| `manifest.json` present | ✅ | Linked twice (json + webmanifest) — minor; both point to same file |
| `theme-color` meta | ✅ | `#064e3b` |
| Service worker registered | ✅ | `/sw.js` via `navigator.serviceWorker.register` |
| Offline page | ✅ | `/offline` — clean, self-contained, auto-retries on `online` event |
| `apple-mobile-web-app-capable` | ✅ | |
| Icons (192, 512) | ✅ | Multiple sizes present |
| HTTPS requirement | ⚠️ | Development only; must be HTTPS in production for SW to activate |
| Precache completeness | ⚠️ | New routes (prediction, verify, bottleneck) not in SW — see C3 |
