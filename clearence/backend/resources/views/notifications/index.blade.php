@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Your clearance activity updates')

@section('content')
<div style="max-width:680px;margin:0 auto;">
    <div class="glow-card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:13px;font-weight:600;color:var(--text);margin:0;">All Notifications</p>
            @if($notifications->where('read_at',null)->count() > 0)
            <button onclick="markAllReadPage()" style="font-size:12px;color:var(--green);background:none;border:none;cursor:pointer;font-weight:600;">Mark all as read</button>
            @endif
        </div>

        @forelse($notifications as $n)
        @php $data = $n->data; @endphp
        <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 20px;border-bottom:1px solid #f8fafc;{{ $n->read_at ? '' : 'background:#f0fdf4;' }}">
            <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;{{ ($data['status']??'') === 'approved' ? 'background:#d1fae5;color:#059669;' : 'background:#fee2e2;color:#ef4444;' }}">
                @if(($data['status']??'') === 'approved')
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                @else
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                @endif
            </div>
            <div style="flex:1;">
                <p style="font-size:13px;color:var(--text);margin:0 0 3px;line-height:1.5;">{{ $data['message'] ?? '—' }}</p>
                <p style="font-size:11px;color:var(--text-muted);margin:0;">
                    {{ $n->created_at->format('d M Y, h:i A') }}
                    @if(!$n->read_at) &nbsp;<span style="color:var(--green);font-weight:600;">● New</span>@endif
                </p>
            </div>
            @if(isset($data['clearance_id']))
            <a href="{{ route('student.clearances.show', $data['clearance_id']) }}"
               style="font-size:11px;color:var(--green);text-decoration:none;font-weight:600;flex-shrink:0;margin-top:2px;">View</a>
            @endif
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="opacity:0.3;margin-bottom:10px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <p style="font-size:13px;margin:0;">No notifications yet</p>
        </div>
        @endforelse
    </div>
</div>
@push('scripts')
<script>
function markAllReadPage() {
    fetch('{{ route("notifications.markRead") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({}),
    }).then(() => location.reload());
}
</script>
@endpush
@endsection
