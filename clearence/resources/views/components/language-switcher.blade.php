@php $current = app()->getLocale(); @endphp

<div style="display:inline-flex;align-items:center;border:1px solid rgba(255,255,255,0.15);border-radius:7px;overflow:hidden;flex-shrink:0;">
    <a href="{{ route('locale.switch', 'en') }}"
       style="padding:4px 10px;font-size:11px;font-weight:700;letter-spacing:0.04em;text-decoration:none;transition:background 0.15s;
              {{ $current === 'en'
                   ? 'background:rgba(255,255,255,0.18);color:#fff;'
                   : 'background:transparent;color:rgba(255,255,255,0.5);' }}"
       title="English">EN</a>
    <span style="width:1px;background:rgba(255,255,255,0.15);align-self:stretch;"></span>
    <a href="{{ route('locale.switch', 'sw') }}"
       style="padding:4px 10px;font-size:11px;font-weight:700;letter-spacing:0.04em;text-decoration:none;transition:background 0.15s;
              {{ $current === 'sw'
                   ? 'background:rgba(255,255,255,0.18);color:#fff;'
                   : 'background:transparent;color:rgba(255,255,255,0.5);' }}"
       title="Kiswahili">SW</a>
</div>
