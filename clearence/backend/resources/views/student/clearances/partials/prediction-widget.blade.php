@inject('predictionService', 'App\Services\PredictionService')
@php
    $pred       = $predictionService->estimateCompletion($clearance);
    $eta        = $pred['estimated_completion_at'];
    $confidence = $pred['confidence_level'];
    $breakdown  = $pred['per_department_breakdown'];

    $confStyle = match($confidence) {
        'high'             => 'background:#d1fae5;color:#065f46;',
        'medium'           => 'background:#fef9c3;color:#854d0e;',
        'low'              => 'background:#ffedd5;color:#9a3412;',
        default            => 'background:#f1f5f9;color:#64748b;',
    };
    $confLabel = match($confidence) {
        'high'   => 'High confidence',
        'medium' => 'Medium confidence',
        'low'    => 'Low confidence',
        default  => 'Insufficient data',
    };

    $totalHours = collect($breakdown)->sum('estimated_hours');
    $etaLabel   = $eta
        ? ($totalHours < 24
            ? 'today, around '.$eta->format('H:i')
            : 'by '.$eta->format('D d M') . ' (~' . round($totalHours / 24, 1) . ' days)')
        : null;
@endphp

@if($eta)
<div class="glow-card card-anim" style="margin-bottom:18px;animation-delay:0.08s;border-left:3px solid #d97706;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
        <div style="display:flex;align-items:center;gap:9px;">
            <div style="width:32px;height:32px;border-radius:8px;background:#fffbeb;border:1px solid #fde68a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="15" height="15" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <div>
                <p style="font-size:11px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:0.07em;margin:0;">Estimated Completion</p>
                <p style="font-size:15px;font-weight:800;color:#1e293b;margin:2px 0 0;">{{ $etaLabel }}</p>
            </div>
        </div>
        <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;{{ $confStyle }}">{{ $confLabel }}</span>
    </div>

    {{-- Per-department breakdown --}}
    <div style="border-top:1px solid #f1f5f9;padding-top:12px;">
        <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin:0 0 8px;">Remaining departments</p>
        <div style="display:flex;flex-direction:column;gap:5px;">
            @foreach($breakdown as $row)
            <div style="display:grid;grid-template-columns:1fr 70px 80px;align-items:center;gap:8px;font-size:12px;">
                <span style="font-weight:600;color:#374151;">{{ $row['department_name'] }}</span>
                <span style="color:#94a3b8;font-size:11px;">
                    @if($row['queue_depth'] > 0)
                        <span title="{{ $row['queue_depth'] }} request(s) ahead of yours">{{ $row['queue_depth'] }} ahead</span>
                    @else
                        <span style="color:#059669;">Next in queue</span>
                    @endif
                </span>
                <span style="text-align:right;font-weight:700;color:#d97706;font-family:monospace;font-size:11px;">
                    ~{{ $row['estimated_hours'] < 24
                        ? $row['estimated_hours'].'h'
                        : round($row['estimated_hours']/24, 1).'d' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    <p style="font-size:10px;color:#94a3b8;margin:10px 0 0;font-style:italic;">
        Based on {{ collect($breakdown)->sum('sample_count') }} historical approvals over the past 30 days.
        Estimates exclude weekends and may change as queue positions shift.
    </p>
</div>
@endif
