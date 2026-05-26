{{--
    Shared attachments partial.
    Variables:
        $attachments  — Collection<Attachment>
        $downloadRoute — named route string (default: 'attachments.download')
--}}
@php $downloadRoute = $downloadRoute ?? 'attachments.download'; @endphp

@if($attachments->isNotEmpty())
<div class="glow-card" style="margin-bottom:18px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg style="width:15px;height:15px;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
            </svg>
        </div>
        <div>
            <h4 style="font-size:13px;font-weight:700;color:#1e293b;margin:0;">Supporting Documents</h4>
            <p style="font-size:11px;color:#94a3b8;margin:2px 0 0;">{{ $attachments->count() }} file{{ $attachments->count() !== 1 ? 's' : '' }} attached</p>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:8px;">
        @foreach($attachments as $attachment)
        @php
            $isImage = $attachment->is_image;
            $ext     = strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
            $iconBg  = $isImage ? '#eff6ff' : '#fef3c7';
            $iconCol = $isImage ? '#3b82f6' : '#d97706';
        @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;
                    padding:10px 14px;border:1px solid #e2e8f0;border-radius:9px;
                    background:#f8fafc;transition:background 0.15s;"
             onmouseover="this.style.background='#f0fdf4';this.style.borderColor='#a7f3d0';"
             onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0';">

            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                <div style="width:34px;height:34px;border-radius:7px;background:{{ $iconBg }};
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    @if($isImage)
                    <svg style="width:16px;height:16px;color:{{ $iconCol }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5" stroke-width="2"/>
                        <polyline points="21 15 16 10 5 21" stroke-width="2"/>
                    </svg>
                    @else
                    <svg style="width:16px;height:16px;color:{{ $iconCol }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    @endif
                </div>
                <div style="min-width:0;">
                    <p style="font-size:12px;font-weight:600;color:#1e293b;margin:0;
                               white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px;">
                        {{ $attachment->file_name }}
                    </p>
                    <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;">
                        {{ $ext }} &nbsp;&middot;&nbsp; {{ $attachment->human_size }}
                        &nbsp;&middot;&nbsp; {{ $attachment->uploaded_at->format('d M Y') }}
                    </p>
                </div>
            </div>

            <a href="{{ route($downloadRoute, $attachment) }}"
               target="_blank"
               style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;
                      color:#059669;text-decoration:none;padding:5px 12px;border:1px solid #a7f3d0;
                      border-radius:6px;background:#f0fdf4;flex-shrink:0;transition:all 0.15s;white-space:nowrap;"
               onmouseover="this.style.background='#d1fae5'"
               onmouseout="this.style.background='#f0fdf4'">
                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                View
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif
