{{--
    Pending Drafts widget — student/partials/pending-drafts.blade.php

    This is a client-side-only widget. The server renders an empty container;
    offline-form.js populates it from IndexedDB on page load and after each
    sync attempt.

    Hidden by default (display:none). JS sets display:block when drafts exist.
--}}
<div id="pending-drafts-widget"
     class="glow-card"
     style="display:none;padding:0;overflow:hidden;margin-bottom:22px;
            border-color:#fde68a;background:linear-gradient(135deg,#fffbeb,#fff);">
    {{-- Content injected by offline-form.js → renderPendingDrafts() --}}
</div>

@push('styles')
<style>
@keyframes pulse-dot {
    0%   { transform: scale(1);   opacity: 0.9; }
    50%  { transform: scale(1.5); opacity: 0.4; }
    100% { transform: scale(1);   opacity: 0.9; }
}
</style>
@endpush
