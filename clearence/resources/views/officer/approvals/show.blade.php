@extends('layouts.app')

@section('title', 'Review Clearance Request')
@section('page-title', 'Review Clearance Request')
@section('page-subtitle', 'Approve or reject this student\'s clearance for your department')

@section('content')
<style>
.info-cell{padding:14px 18px;border-right:1px solid #f1f5f9;}
.info-cell:last-child{border-right:none;}
.dept-prog-row{display:flex;align-items:center;justify-content:space-between;padding:9px 13px;border-radius:8px;margin-bottom:5px;border:1px solid;}
</style>

<div style="max-width:860px;margin:0 auto;">

    <!-- Student Card -->
    <div class="glow-card" style="padding:0;overflow:hidden;margin-bottom:18px;">
        <div style="padding:14px 22px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;">Student Particulars</p>
            <p style="font-size:11px;color:#94a3b8;margin-top:3px;">{{ auth()->user()->department->name }} &mdash; Mbeya University of Science and Technology</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);">
            @foreach([
                ['Full Name', $approval->clearance->user->name, false],
                ['Student ID / UE No.', $approval->clearance->user->student_id ?? '—', true],
                ['Reg. Number', $approval->clearance->user->registration_number ?? '—', true],
                ['Phone', $approval->clearance->user->phone ?? '—', false],
            ] as [$lbl, $val, $mono])
            <div class="info-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin-bottom:5px;">{{ $lbl }}</p>
                <p style="font-size:13px;font-weight:600;{{ $mono ? 'font-family:monospace;color:#d97706;' : 'color:#1e293b;' }}">{{ $val }}</p>
            </div>
            @endforeach
        </div>
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;border-top:1px solid #f1f5f9;">
            <div class="info-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin-bottom:5px;">Programme</p>
                <p style="font-size:13px;font-weight:600;color:#1e293b;">{{ $approval->clearance->user->programme ?? '—' }}</p>
            </div>
            <div class="info-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin-bottom:5px;">College</p>
                <p style="font-size:13px;font-weight:600;color:#1e293b;">{{ $approval->clearance->user->college ?? '—' }}</p>
            </div>
            <div class="info-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin-bottom:5px;">Year</p>
                <p style="font-size:13px;font-weight:600;color:#1e293b;">{{ $approval->clearance->user->year_of_study ?? '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Clearance Details -->
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Clearance Request Details</p>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:18px;">
            @foreach([
                ['Academic Year', $approval->clearance->academic_year],
                ['Semester', $approval->clearance->semester],
                ['Type', ucfirst($approval->clearance->clearance_type)],
                ['Submitted', $approval->clearance->submitted_at->format('d M Y, h:i A')],
            ] as [$lbl, $val])
            <div>
                <p style="font-size:10px;color:#94a3b8;margin-bottom:5px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">{{ $lbl }}</p>
                <p style="font-size:13px;font-weight:600;color:#1e293b;">{{ $val }}</p>
            </div>
            @endforeach
        </div>
        @if($approval->clearance->reason)
        <div style="margin-top:14px;padding:12px 15px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
            <p style="font-size:10px;color:#94a3b8;margin-bottom:5px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">Student's Notes</p>
            <p style="font-size:13px;color:#475569;font-style:italic;">{{ $approval->clearance->reason }}</p>
        </div>
        @endif
    </div>

    <!-- All Departments Progress -->
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:14px;">Clearance Progress — All Departments</p>
        @foreach($approval->clearance->approvals->sortBy('department.priority') as $a)
        <div class="dept-prog-row"
             style="border-color:{{ $a->id===$approval->id ? '#fde68a' : ($a->status==='approved' ? '#a7f3d0' : ($a->status==='rejected' ? '#fecaca' : '#e2e8f0')) }};
                    background:{{ $a->id===$approval->id ? '#fefce8' : ($a->status==='approved' ? '#f0fdf4' : ($a->status==='rejected' ? '#fef2f2' : '#f8fafc')) }};">
            <div style="display:flex;align-items:center;gap:10px;">
                @if($a->status==='approved')
                <div style="width:18px;height:18px;border-radius:50%;background:#059669;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:10px;height:10px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                @elseif($a->status==='rejected')
                <div style="width:18px;height:18px;border-radius:50%;background:#ef4444;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:10px;height:10px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                @else
                <div style="width:18px;height:18px;border-radius:50%;border:2px solid #cbd5e1;"></div>
                @endif
                <span style="font-size:12px;font-weight:{{ $a->id===$approval->id ? '700' : '500' }};color:#1e293b;">{{ $a->department->name }}</span>
                @if($a->id === $approval->id)
                <span style="font-size:10px;background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:1px 8px;border-radius:999px;font-weight:700;">YOUR DEPT</span>
                @endif
            </div>
            <span style="font-size:12px;font-weight:600;color:{{ $a->status==='approved' ? '#059669' : ($a->status==='rejected' ? '#ef4444' : '#d97706') }};">{{ ucfirst($a->status) }}</span>
        </div>
        @endforeach
    </div>

    <!-- Decision Panel -->
    @if($approval->status === 'pending')
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
        <!-- Approve -->
        <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:12px;padding:20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                <div style="width:32px;height:32px;border-radius:8px;background:#059669;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:14px;height:14px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h4 style="font-size:14px;font-weight:700;color:#065f46;">Approve Clearance</h4>
            </div>
            <form method="POST" action="{{ route('officer.approvals.approve', $approval) }}">
                @csrf
                <div style="margin-bottom:13px;">
                    <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#065f46;margin-bottom:6px;">Comments (Optional)</label>
                    <textarea name="comments" rows="3" class="glow-input" style="resize:none;"
                              placeholder="e.g., All books returned. No outstanding dues."></textarea>
                </div>
                <button type="submit" class="btn-glow" style="width:100%;justify-content:center;">Approve</button>
            </form>
        </div>

        <!-- Reject -->
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                <div style="width:32px;height:32px;border-radius:8px;background:#ef4444;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:14px;height:14px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h4 style="font-size:14px;font-weight:700;color:#991b1b;">Reject Clearance</h4>
            </div>
            <form method="POST" action="{{ route('officer.approvals.reject', $approval) }}">
                @csrf
                <div style="margin-bottom:13px;">
                    <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#991b1b;margin-bottom:6px;">Rejection Reason <span style="color:#ef4444;">*</span></label>
                    <textarea name="comments" rows="3" required class="glow-input" style="resize:none;border-color:#fecaca;"
                              placeholder="e.g., Outstanding library fines of TZS 5,000. Unreturned book: Applied Mathematics Vol.2"></textarea>
                </div>
                <button type="submit"
                        style="width:100%;padding:10px;border:none;border-radius:8px;cursor:pointer;background:#ef4444;color:#fff;font-weight:700;font-size:13px;font-family:inherit;transition:background 0.2s;"
                        onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Reject</button>
            </form>
        </div>
    </div>

    @else
    <div class="glow-card" style="margin-bottom:18px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#94a3b8;margin-bottom:12px;">Decision Recorded</p>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            @if($approval->status==='approved')
            <span class="badge badge-approved" style="font-size:13px;padding:4px 14px;">Approved</span>
            @else
            <span class="badge badge-rejected" style="font-size:13px;padding:4px 14px;">Rejected</span>
            @endif
            <span style="font-size:12px;color:#64748b;">by {{ $approval->officer->name ?? 'N/A' }} &mdash; {{ $approval->reviewed_at?->format('d M Y, h:i A') }}</span>
        </div>
        @if($approval->comments)
        <div style="padding:11px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
            <p style="font-size:10px;color:#94a3b8;margin-bottom:5px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">Comments</p>
            <p style="font-size:13px;color:#475569;font-style:italic;">"{{ $approval->comments }}"</p>
        </div>
        @endif
    </div>
    @endif

    <a href="{{ route('officer.approvals.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Back to Approvals</a>
</div>
@endsection
