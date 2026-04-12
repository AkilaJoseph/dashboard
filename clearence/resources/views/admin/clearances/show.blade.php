@extends('layouts.app')

@section('title', 'Clearance #' . $clearance->id)
@section('page-title', 'Clearance Request #' . $clearance->id)
@section('page-subtitle', 'Review and override individual department approvals')

@section('content')

<!-- Student + Clearance Info -->
<div class="grid grid-cols-1 gap-5 mb-5 lg:grid-cols-2">

    <!-- Student Card -->
    <div class="glow-card">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#059669;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #d1fae5;">Student Details</p>
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
            <div style="width:48px;height:48px;border-radius:10px;background:#059669;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:#fff;flex-shrink:0;">
                {{ strtoupper(substr($clearance->user->name,0,1)) }}
            </div>
            <div>
                <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:4px;">{{ $clearance->user->name }}</h3>
                <p style="font-size:12px;color:#64748b;">{{ $clearance->user->programme ?? 'Programme not set' }}</p>
            </div>
        </div>
        @php
        $fields = [
            'Student ID'    => $clearance->user->student_id,
            'Reg. Number'   => $clearance->user->registration_number,
            'College'       => $clearance->user->college,
            'Year of Study' => $clearance->user->year_of_study,
            'Email'         => $clearance->user->email,
            'Phone'         => $clearance->user->phone,
        ];
        @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            @foreach($fields as $label => $value)
            <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;">
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:3px;">{{ $label }}</p>
                <p style="font-size:12px;font-weight:600;color:#1e293b;font-family:monospace;">{{ $value ?? '—' }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Clearance Summary -->
    <div class="glow-card">
        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#059669;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #d1fae5;">Clearance Summary</p>
        @php
        $approved = $clearance->approvals->where('status','approved')->count();
        $total    = $clearance->approvals->count();
        $pct      = $total > 0 ? round(($approved/$total)*100) : 0;
        @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
            @php
            $meta = [
                'Type'          => ucfirst($clearance->clearance_type),
                'Academic Year' => $clearance->academic_year,
                'Semester'      => $clearance->semester,
                'Submitted'     => $clearance->submitted_at?->format('d M Y') ?? '—',
                'Completed'     => $clearance->completed_at?->format('d M Y') ?? '—',
            ];
            @endphp
            @foreach($meta as $label => $value)
            <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;">
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:3px;">{{ $label }}</p>
                <p style="font-size:12px;font-weight:600;color:#1e293b;">{{ $value }}</p>
            </div>
            @endforeach
            <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;">
                <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:3px;">Overall Status</p>
                @if($clearance->status === 'approved')
                    <span class="badge badge-approved" style="font-size:12px;">Approved</span>
                @elseif($clearance->status === 'rejected')
                    <span class="badge badge-rejected" style="font-size:12px;">Rejected</span>
                @elseif($clearance->status === 'in_progress')
                    <span class="badge badge-progress" style="font-size:12px;">In Progress</span>
                @else
                    <span class="badge badge-pending" style="font-size:12px;">Pending</span>
                @endif
            </div>
        </div>

        <!-- Progress bar -->
        <div style="margin-top:4px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="font-size:11px;color:#64748b;">Dept. Approvals</span>
                <span style="font-size:11px;font-weight:700;color:#059669;">{{ $approved }}/{{ $total }}</span>
            </div>
            <div class="glow-progress" style="height:8px;">
                <div class="glow-progress-fill" style="width:{{ $pct }}%;height:8px;"></div>
            </div>
        </div>

        @if($clearance->reason)
        <div style="margin-top:14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;">
            <p style="font-size:9px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#92400e;margin-bottom:4px;">Student Reason / Note</p>
            <p style="font-size:12px;color:#78350f;line-height:1.5;">{{ $clearance->reason }}</p>
        </div>
        @endif
    </div>
</div>

<!-- Department Approvals with Override -->
<div class="glow-card">
    <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#059669;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #d1fae5;">
        Department Approvals
        <span style="font-size:10px;font-weight:500;color:#94a3b8;letter-spacing:0;text-transform:none;margin-left:6px;">— Admin can override any decision</span>
    </p>

    <div style="display:grid;gap:12px;">
        @foreach($clearance->approvals->sortBy('department.priority') as $approval)
        <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <!-- Row header -->
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:#f8fafc;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:#059669;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0;">
                        {{ strtoupper(substr($approval->department->code ?? $approval->department->name,0,2)) }}
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0;">{{ $approval->department->name }}</p>
                        <p style="font-size:11px;color:#94a3b8;margin:0;">
                            @if($approval->officer)
                                Reviewed by {{ $approval->officer->name }}
                                @if($approval->reviewed_at)· {{ $approval->reviewed_at->format('d M Y H:i') }}@endif
                            @else
                                Not yet reviewed
                            @endif
                        </p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    @if($approval->status === 'approved')
                        <span class="badge badge-approved">Approved</span>
                    @elseif($approval->status === 'rejected')
                        <span class="badge badge-rejected">Rejected</span>
                    @else
                        <span class="badge badge-pending">Pending</span>
                    @endif
                    <button onclick="toggleOverride('override-{{ $approval->id }}')"
                        style="font-size:11px;font-weight:600;color:#059669;background:none;border:1px solid #a7f3d0;border-radius:6px;padding:4px 12px;cursor:pointer;display:flex;align-items:center;gap:4px;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Override
                    </button>
                </div>
            </div>

            @if($approval->comments)
            <div style="padding:10px 16px;background:#fff;border-top:1px solid #f1f5f9;">
                <p style="font-size:11px;color:#64748b;font-style:italic;">"{{ $approval->comments }}"</p>
            </div>
            @endif

            <!-- Override Form (hidden) -->
            <div id="override-{{ $approval->id }}" style="display:none;border-top:2px dashed #d1fae5;background:#f0fdf4;padding:16px;">
                <p style="font-size:11px;font-weight:700;color:#059669;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.07em;">
                    Admin Override — {{ $approval->department->name }}
                </p>
                <form method="POST" action="{{ route('admin.clearances.override', [$clearance, $approval]) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 2fr;gap:12px;align-items:start;">
                        <div>
                            <label style="font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#475569;display:block;margin-bottom:5px;">New Status</label>
                            <select name="action" class="glow-input" required style="appearance:none;">
                                <option value="approved"  {{ $approval->status==='approved' ?'selected':'' }}>Approve</option>
                                <option value="rejected"  {{ $approval->status==='rejected' ?'selected':'' }}>Reject</option>
                                <option value="pending"   {{ $approval->status==='pending'  ?'selected':'' }}>Reset to Pending</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#475569;display:block;margin-bottom:5px;">Admin Comment (optional)</label>
                            <input type="text" name="comments" value="{{ $approval->comments }}"
                                class="glow-input" placeholder="Reason for override…">
                        </div>
                    </div>
                    <div style="margin-top:12px;display:flex;gap:8px;">
                        <button type="submit" class="btn-glow" style="font-size:12px;padding:8px 18px;">
                            Confirm Override
                        </button>
                        <button type="button" onclick="toggleOverride('override-{{ $approval->id }}')"
                            style="font-size:12px;padding:8px 16px;background:none;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;color:#64748b;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div style="margin-top:16px;">
    <a href="{{ route('admin.clearances.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
        Back to Clearances
    </a>
</div>

<script>
function toggleOverride(id) {
    var el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endsection
