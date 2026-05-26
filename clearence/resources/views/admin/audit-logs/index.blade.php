@extends('layouts.app')

@section('title', 'Audit Trail')
@section('page-title', 'Audit Trail')
@section('page-subtitle', 'System-wide record of all significant actions')

@section('content')
<div style="display:flex;flex-direction:column;gap:18px;">

    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:220px;">
            <label style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:5px;">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Actor name, event, IP…" class="glow-input">
        </div>
        <div style="min-width:180px;">
            <label style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;display:block;margin-bottom:5px;">Event</label>
            <select name="event" class="glow-input">
                <option value="">All events</option>
                @foreach($events as $e)
                    <option value="{{ $e }}" @selected(request('event') === $e)>{{ $e }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-glow">Filter</button>
            @if(request()->hasAny(['search','event']))
                <a href="{{ route('admin.audit-logs.index') }}" class="btn-glow" style="background:var(--text-muted);">Clear</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="glow-card" style="padding:0;overflow:hidden;">
        <table class="glow-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="padding:12px 18px;text-align:left;">When</th>
                    <th style="padding:12px 18px;text-align:left;">Actor</th>
                    <th style="padding:12px 18px;text-align:left;">Event</th>
                    <th style="padding:12px 18px;text-align:left;">Subject</th>
                    <th style="padding:12px 18px;text-align:left;">Changes</th>
                    <th style="padding:12px 18px;text-align:left;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="padding:11px 18px;white-space:nowrap;font-size:12px;color:var(--text-muted);">
                        {{ $log->created_at->format('d M Y H:i') }}
                    </td>
                    <td style="padding:11px 18px;">
                        @if($log->user)
                            <div style="font-size:13px;font-weight:600;">{{ $log->user->name }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $log->user->role }}</div>
                        @else
                            <span style="color:var(--text-muted);font-size:12px;">System</span>
                        @endif
                    </td>
                    <td style="padding:11px 18px;">
                        <span class="badge {{ $log->eventBadgeClass() }}">{{ $log->event }}</span>
                    </td>
                    <td style="padding:11px 18px;font-size:12px;color:var(--text-muted);">
                        {{ $log->subjectLabel() }}
                    </td>
                    <td style="padding:11px 18px;">
                        @if($log->old_values || $log->new_values)
                        <button onclick="toggleDiff(this)"
                            data-old="{{ json_encode($log->old_values) }}"
                            data-new="{{ json_encode($log->new_values) }}"
                            style="font-size:11px;color:var(--green);background:none;border:none;cursor:pointer;font-weight:600;padding:0;">
                            View diff
                        </button>
                        @else
                        <span style="color:var(--text-muted);font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="padding:11px 18px;font-size:12px;color:var(--text-muted);font-family:monospace;">
                        {{ $log->ip_address ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:40px;text-align:center;color:var(--text-muted);font-size:13px;">
                        No audit entries found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div style="display:flex;justify-content:flex-end;">
        {{ $logs->links() }}
    </div>
    @endif

</div>

{{-- Diff modal --}}
<div id="diff-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:24px;max-width:600px;width:90%;max-height:80vh;overflow-y:auto;box-shadow:0 12px 40px rgba(0,0,0,.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="margin:0;font-size:15px;font-weight:700;">Change details</h3>
            <button onclick="closeDiff()" style="background:none;border:none;cursor:pointer;font-size:20px;color:var(--text-muted);">&times;</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin:0 0 8px;">Before</p>
                <pre id="diff-old" style="font-size:11px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;margin:0;white-space:pre-wrap;word-break:break-word;color:#991b1b;"></pre>
            </div>
            <div>
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin:0 0 8px;">After</p>
                <pre id="diff-new" style="font-size:11px;background:#f0fdf4;border:1px solid #a7f3d0;border-radius:8px;padding:12px;margin:0;white-space:pre-wrap;word-break:break-word;color:#065f46;"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleDiff(btn) {
    const old_ = JSON.parse(btn.dataset.old || 'null');
    const new_ = JSON.parse(btn.dataset.new || 'null');
    document.getElementById('diff-old').textContent = old_ ? JSON.stringify(old_, null, 2) : '(none)';
    document.getElementById('diff-new').textContent = new_ ? JSON.stringify(new_, null, 2) : '(none)';
    document.getElementById('diff-modal').style.display = 'flex';
}
function closeDiff() {
    document.getElementById('diff-modal').style.display = 'none';
}
document.getElementById('diff-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDiff();
});
</script>
@endpush
