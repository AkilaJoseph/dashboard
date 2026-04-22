@extends('layouts.app')

@section('title', 'New Push Campaign')
@section('page-title', 'New Push Campaign')
@section('page-subtitle', 'Send an ad-hoc push notification to selected users')

@section('content')
<div style="max-width:720px;margin:0 auto;">

    {{-- ── Audience ─────────────────────────────────────────────────────────── --}}
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:16px;">Audience</p>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px;" id="role-tiles">
            @foreach([
                ['student', 'Students',  $subscriberCounts['student']],
                ['officer', 'Officers',  $subscriberCounts['officer']],
                ['admin',   'Admins',    $subscriberCounts['admin']  ],
            ] as [$role, $label, $count])
            <label id="tile-{{ $role }}"
                   style="display:flex;flex-direction:column;align-items:center;padding:14px;border:2px solid #e2e8f0;border-radius:10px;cursor:pointer;transition:all 0.15s;background:#fff;">
                <input type="checkbox" name="audience_roles[]" value="{{ $role }}" form="campaign-form"
                       checked onchange="updateTile('{{ $role }}', this.checked); updateAudienceCount()"
                       style="display:none;" id="role-{{ $role }}">
                <span style="font-size:22px;font-weight:700;color:#059669;">{{ number_format($count) }}</span>
                <span style="font-size:12px;font-weight:600;color:#1e293b;margin-top:3px;">{{ $label }}</span>
                <span style="font-size:11px;color:#94a3b8;">with push enabled</span>
            </label>
            @endforeach
        </div>

        <div style="margin-bottom:4px;">
            <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">Filter by Department (optional)</label>
            <select name="department_id" form="campaign-form" onchange="updateAudienceCount()"
                    style="width:100%;margin-top:6px;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#1e293b;">
                <option value="">All departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <p id="audience-summary" style="font-size:12px;color:#94a3b8;margin-top:10px;"></p>
    </div>

    {{-- ── Message ──────────────────────────────────────────────────────────── --}}
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:16px;">Message</p>

        <form id="campaign-form" method="POST" action="{{ route('admin.push-campaigns.store') }}">
            @csrf

            <div style="margin-bottom:13px;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:6px;">Title <span style="color:#ef4444;">*</span></label>
                <input type="text" name="title" id="preview-title" required maxlength="100"
                       class="glow-input" placeholder="e.g., Clearance Deadline Reminder"
                       oninput="updatePreview()" value="{{ old('title') }}">
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Max 100 characters.</p>
            </div>

            <div style="margin-bottom:13px;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:6px;">Body <span style="color:#ef4444;">*</span></label>
                <textarea name="body" id="preview-body" required maxlength="500" rows="3"
                          class="glow-input" style="resize:none;"
                          placeholder="e.g., The deadline for submitting clearance requests is Friday, 30 April."
                          oninput="updatePreview()">{{ old('body') }}</textarea>
                <p style="font-size:11px;color:#94a3b8;margin-top:4px;"><span id="body-count">0</span>/500 characters.</p>
            </div>

            <div style="margin-bottom:13px;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:6px;">Target URL (optional)</label>
                <input type="text" name="target_url" class="glow-input" placeholder="/student/clearances"
                       value="{{ old('target_url', '/') }}">
            </div>

            <div style="margin-bottom:13px;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:6px;">Image URL (optional)</label>
                <input type="url" name="image_url" class="glow-input" placeholder="https://…"
                       value="{{ old('image_url') }}">
            </div>

            <div style="margin-bottom:13px;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:6px;">Schedule (optional — leave blank to send now)</label>
                <input type="datetime-local" name="scheduled_at" class="glow-input"
                       min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                       value="{{ old('scheduled_at') }}"
                       id="scheduled-at-input"
                       oninput="updateActionLabel()">
            </div>
        </form>
    </div>

    {{-- ── Notification preview ─────────────────────────────────────────────── --}}
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Notification Preview</p>

        <div style="background:#1e293b;border-radius:12px;padding:14px 16px;display:flex;align-items:flex-start;gap:12px;">
            <img src="/images/pwa-icons/icon-96.png" style="width:40px;height:40px;border-radius:8px;flex-shrink:0;">
            <div>
                <p id="prev-title" style="font-size:13px;font-weight:700;color:#f8fafc;margin:0 0 4px;">Campaign Title</p>
                <p id="prev-body"  style="font-size:12px;color:#94a3b8;margin:0;line-height:1.5;">Your notification body will appear here.</p>
                <p style="font-size:10px;color:#64748b;margin:6px 0 0;">ACIMS — MUST</p>
            </div>
        </div>

        <div style="margin-top:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <button type="button" id="btn-preview"
                    style="padding:7px 18px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:13px;font-weight:600;cursor:pointer;color:#1e293b;">
                Preview on My Device
            </button>
            <span id="preview-msg" style="font-size:12px;color:#94a3b8;"></span>
        </div>
    </div>

    {{-- ── Actions ──────────────────────────────────────────────────────────── --}}
    <div style="display:flex;gap:12px;align-items:center;margin-bottom:32px;flex-wrap:wrap;">
        <button type="submit" name="action" value="send" form="campaign-form"
                class="btn-glow" style="font-size:13px;" id="btn-send">
            Send Now
        </button>
        <button type="submit" name="action" value="schedule" form="campaign-form"
                style="padding:10px 22px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:13px;font-weight:600;cursor:pointer;color:#1e293b;"
                id="btn-schedule">
            Schedule
        </button>
        <a href="{{ route('admin.push-campaigns.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;">Cancel</a>
    </div>

    @if($errors->any())
    <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:16px;">
        @foreach($errors->all() as $e)
        <p style="font-size:13px;color:#ef4444;margin:2px 0;">{{ $e }}</p>
        @endforeach
    </div>
    @endif

</div>

<script>
// ── Tile toggle ───────────────────────────────────────────────────────────────
function updateTile(role, checked) {
    const tile = document.getElementById('tile-' + role);
    tile.style.borderColor = checked ? '#059669' : '#e2e8f0';
    tile.style.background  = checked ? '#f0fdf4' : '#fff';
}

// Init tiles on load
['student','officer','admin'].forEach(r => {
    updateTile(r, document.getElementById('role-' + r).checked);
});

// ── Preview ───────────────────────────────────────────────────────────────────
function updatePreview() {
    const title = document.getElementById('preview-title').value || 'Campaign Title';
    const body  = document.getElementById('preview-body').value  || 'Your notification body will appear here.';
    document.getElementById('prev-title').textContent = title;
    document.getElementById('prev-body').textContent  = body;
    document.getElementById('body-count').textContent = document.getElementById('preview-body').value.length;
}
updatePreview();

// ── Schedule label ────────────────────────────────────────────────────────────
function updateActionLabel() {
    const hasSchedule = document.getElementById('scheduled-at-input').value !== '';
    document.getElementById('btn-send').textContent     = hasSchedule ? 'Send Now (ignore schedule)' : 'Send Now';
    document.getElementById('btn-schedule').disabled    = !hasSchedule;
    document.getElementById('btn-schedule').style.opacity = hasSchedule ? '1' : '0.4';
}
updateActionLabel();

// ── Audience count ────────────────────────────────────────────────────────────
function updateAudienceCount() {
    const counts = @json($subscriberCounts);
    const checked = [...document.querySelectorAll('input[name="audience_roles[]"]:checked')].map(i => i.value);
    const total = checked.reduce((s, r) => s + (counts[r] ?? 0), 0);
    document.getElementById('audience-summary').textContent =
        checked.length === 0 ? 'No roles selected — no one will receive this campaign.'
        : `Estimated reach: ${total.toLocaleString()} device(s) with push subscriptions.`;
}
updateAudienceCount();

// ── Preview on device ─────────────────────────────────────────────────────────
document.getElementById('btn-preview').addEventListener('click', async () => {
    const title = document.getElementById('preview-title').value;
    const body  = document.getElementById('preview-body').value;
    const msg   = document.getElementById('preview-msg');

    if (!title || !body) {
        msg.textContent = 'Fill in title and body first.';
        msg.style.color = '#ef4444';
        return;
    }

    msg.textContent = 'Sending…';
    msg.style.color = '#94a3b8';

    try {
        const res = await fetch('{{ route("admin.push-campaigns.preview") }}', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':       'application/json',
            },
            body: JSON.stringify({ title, body }),
        });
        const json = await res.json();
        msg.textContent = json.message;
        msg.style.color = json.ok ? '#059669' : '#ef4444';
    } catch (e) {
        msg.textContent = 'Error: ' + e.message;
        msg.style.color = '#ef4444';
    }
});
</script>
@endsection
