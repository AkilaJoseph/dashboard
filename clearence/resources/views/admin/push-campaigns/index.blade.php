@extends('layouts.app')

@section('title', 'Push Campaigns')
@section('page-title', 'Push Campaigns')
@section('page-subtitle', 'Broadcast push notifications to students, officers, or all users')

@section('content')
<div style="max-width:960px;margin:0 auto;">

    @if(session('success'))
    <div style="margin-bottom:16px;padding:10px 16px;background:#f0fdf4;border:1px solid #a7f3d0;border-radius:10px;font-size:13px;font-weight:600;color:#065f46;">
        {{ session('success') }}
    </div>
    @endif

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div></div>
        <a href="{{ route('admin.push-campaigns.create') }}" class="btn-glow" style="text-decoration:none;font-size:13px;">
            + New Campaign
        </a>
    </div>

    <div class="glow-card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 22px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin:0;">All Campaigns</p>
        </div>

        @if($campaigns->isEmpty())
        <div style="padding:40px;text-align:center;">
            <p style="font-size:14px;color:#94a3b8;">No campaigns yet. Create your first broadcast above.</p>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        @foreach(['Title', 'Audience', 'Status', 'Recipients', 'Sent At', 'Created By', ''] as $h)
                        <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $c)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:12px 16px;font-weight:600;color:#1e293b;max-width:200px;">
                            <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $c->title }}</div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($c->body, 60) }}</div>
                        </td>
                        <td style="padding:12px 16px;color:#64748b;">
                            {{ implode(', ', array_map('ucfirst', $c->audience['roles'] ?? [])) }}
                            @if($c->audience['department_id'] ?? null)
                            <span style="font-size:11px;color:#94a3b8;display:block;">Dept #{{ $c->audience['department_id'] }}</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;">
                            @php
                            $badge = match($c->status) {
                                'sent'      => ['#f0fdf4','#a7f3d0','#065f46'],
                                'sending'   => ['#eff6ff','#bfdbfe','#1d4ed8'],
                                'scheduled' => ['#fef9c3','#fde68a','#92400e'],
                                'failed'    => ['#fef2f2','#fecaca','#991b1b'],
                                default     => ['#f8fafc','#e2e8f0','#64748b'],
                            };
                            @endphp
                            <span style="font-size:11px;background:{{ $badge[0] }};border:1px solid {{ $badge[1] }};color:{{ $badge[2] }};padding:2px 9px;border-radius:999px;font-weight:700;">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;color:#64748b;">{{ number_format($c->recipient_count) }}</td>
                        <td style="padding:12px 16px;color:#64748b;white-space:nowrap;">
                            {{ $c->sent_at ? $c->sent_at->format('d M Y, H:i') : ($c->scheduled_at ? 'Sched. '.$c->scheduled_at->format('d M H:i') : '—') }}
                        </td>
                        <td style="padding:12px 16px;color:#64748b;">{{ $c->creator->name ?? '—' }}</td>
                        <td style="padding:12px 16px;text-align:right;">
                            <span style="font-size:11px;color:#94a3b8;">{{ $c->created_at->diffForHumans() }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:14px 22px;">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
