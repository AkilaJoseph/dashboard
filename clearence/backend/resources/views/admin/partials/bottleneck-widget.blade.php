@inject('predictionService', 'App\Services\PredictionService')
@php $bottlenecks = $predictionService->bottleneckDepartments(); @endphp

@if(!empty($bottlenecks))
<div class="glow-card" style="margin-bottom:24px;border-left:3px solid #ef4444;padding:0;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;background:#fef2f2;border-bottom:1px solid #fecaca;">
        <div style="display:flex;align-items:center;gap:9px;">
            <div style="width:30px;height:30px;border-radius:8px;background:#fee2e2;border:1px solid #fecaca;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="14" height="14" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div>
                <p style="font-size:12px;font-weight:700;color:#991b1b;margin:0;">Bottleneck Departments</p>
                <p style="font-size:10px;color:#b91c1c;margin:1px 0 0;">Top 90th percentile of avg decision time this week</p>
            </div>
        </div>
        <span style="font-size:10px;background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:2px 9px;border-radius:999px;font-weight:700;">{{ count($bottlenecks) }} dept{{ count($bottlenecks) > 1 ? 's' : '' }}</span>
    </div>

    <div style="padding:14px 20px;">
        <div style="display:grid;grid-template-columns:7px 1px;margin-bottom:0;">
        </div>
        {{-- Column headers --}}
        <div style="display:grid;grid-template-columns:1fr 90px 80px 100px;gap:8px;align-items:center;padding:0 0 8px;border-bottom:1px solid #f1f5f9;margin-bottom:8px;">
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;">Department</span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;text-align:center;">Avg Time</span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;text-align:center;">Pending</span>
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;text-align:right;">Action</span>
        </div>

        @foreach($bottlenecks as $dept)
        <div style="display:grid;grid-template-columns:1fr 90px 80px 100px;gap:8px;align-items:center;padding:8px 0;border-bottom:1px solid #f8fafc;">
            <div>
                <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0;">{{ $dept['department_name'] }}</p>
                <p style="font-size:10px;color:#94a3b8;margin:1px 0 0;">{{ $dept['sample_count'] }} decisions this week</p>
            </div>
            <div style="text-align:center;">
                <span style="font-size:13px;font-weight:800;color:#dc2626;font-family:monospace;">
                    {{ $dept['avg_hours'] < 24
                        ? $dept['avg_hours'].'h'
                        : round($dept['avg_hours'] / 24, 1).'d' }}
                </span>
                <p style="font-size:9px;color:#94a3b8;margin:1px 0 0;">avg/decision</p>
            </div>
            <div style="text-align:center;">
                <span style="font-size:15px;font-weight:800;color:{{ $dept['pending_count'] > 0 ? '#d97706' : '#94a3b8' }};">{{ $dept['pending_count'] }}</span>
                <p style="font-size:9px;color:#94a3b8;margin:1px 0 0;">queued</p>
            </div>
            <div style="text-align:right;">
                <button
                    onclick="sendBottleneckReminder({{ $dept['department_id'] }}, this)"
                    data-dept="{{ $dept['department_id'] }}"
                    style="font-size:11px;font-weight:700;padding:5px 11px;border-radius:6px;border:1px solid #fecaca;background:#fff;color:#dc2626;cursor:pointer;transition:all 0.15s;white-space:nowrap;"
                    onmouseover="this.style.background='#fee2e2'"
                    onmouseout="this.style.background='#fff'">
                    Send reminder
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
function sendBottleneckReminder(deptId, btn) {
    btn.disabled = true;
    btn.textContent = 'Sending…';

    fetch('/admin/bottleneck/' + deptId + '/remind', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        btn.textContent = 'Sent ✓';
        btn.style.borderColor = '#a7f3d0';
        btn.style.color       = '#059669';
        btn.style.background  = '#f0fdf4';
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Send reminder';
    });
}
</script>
@endif
