@extends('layouts.app')

@section('title', 'Review Clearance Request')
@section('page-title', 'Review Clearance Request')
@section('page-subtitle', 'Approve or reject this student\'s clearance for your department')

@section('content')
<style>
.info-cell{padding:16px 20px;border-right:1px solid rgba(16,185,129,0.1);}
.info-cell:last-child{border-right:none;}
.dept-prog-row{
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 14px;border-radius:9px;margin-bottom:6px;border:1px solid;
    transition:all 0.2s;
}
.action-panel{
    background:rgba(4,18,10,0.7);border-radius:14px;padding:22px;border:1px solid;
    position:relative;overflow:hidden;
}
.action-panel::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.approve-panel{border-color:rgba(16,185,129,0.35);}
.approve-panel::before{background:linear-gradient(90deg,transparent,#10b981,transparent);}
.reject-panel{border-color:rgba(239,68,68,0.3);}
.reject-panel::before{background:linear-gradient(90deg,transparent,#ef4444,transparent);}
</style>

<div style="max-width:860px;margin:0 auto;">

    <!-- Student Card -->
    <div class="glow-card" style="padding:0;overflow:hidden;margin-bottom:20px;">
        <div style="padding:18px 24px;background:rgba(16,185,129,0.05);border-bottom:1px solid rgba(16,185,129,0.12);">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.6);">&#9670; Student Particulars</p>
            <p style="font-size:11px;color:rgba(160,200,175,0.4);margin-top:4px;">{{ auth()->user()->department->name }} &mdash; Mbeya University of Science and Technology</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);">
            @foreach([
                ['Full Name', $approval->clearance->user->name, false],
                ['Student ID / UE No.', $approval->clearance->user->student_id ?? '—', true],
                ['Reg. Number', $approval->clearance->user->registration_number ?? '—', true],
                ['Phone', $approval->clearance->user->phone ?? '—', false],
            ] as [$lbl, $val, $mono])
            <div class="info-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(52,211,153,0.45);margin-bottom:6px;">{{ $lbl }}</p>
                <p style="font-size:13px;font-weight:700;color:#e2e8f0;{{ $mono ? 'font-family:monospace;color:#fbbf24;' : '' }}">{{ $val }}</p>
            </div>
            @endforeach
        </div>
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;border-top:1px solid rgba(16,185,129,0.08);">
            <div class="info-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(52,211,153,0.45);margin-bottom:6px;">Programme</p>
                <p style="font-size:13px;font-weight:700;color:#e2e8f0;">{{ $approval->clearance->user->programme ?? '—' }}</p>
            </div>
            <div class="info-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(52,211,153,0.45);margin-bottom:6px;">College</p>
                <p style="font-size:13px;font-weight:700;color:#e2e8f0;">{{ $approval->clearance->user->college ?? '—' }}</p>
            </div>
            <div class="info-cell">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(52,211,153,0.45);margin-bottom:6px;">Year of Study</p>
                <p style="font-size:13px;font-weight:700;color:#e2e8f0;">{{ $approval->clearance->user->year_of_study ?? '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Clearance Details -->
    <div class="glow-card" style="margin-bottom:20px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.5);margin-bottom:16px;">Clearance Request Details</p>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;">
            @foreach([
                ['Academic Year', $approval->clearance->academic_year],
                ['Semester', $approval->clearance->semester],
                ['Type', ucfirst($approval->clearance->clearance_type)],
                ['Submitted', $approval->clearance->submitted_at->format('d M Y, h:i A')],
            ] as [$lbl, $val])
            <div>
                <p style="font-size:10px;color:rgba(160,200,175,0.4);margin-bottom:6px;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;">{{ $lbl }}</p>
                <p style="font-size:13px;font-weight:700;color:#e2e8f0;">{{ $val }}</p>
            </div>
            @endforeach
        </div>
        @if($approval->clearance->reason)
        <div style="margin-top:16px;padding:12px 16px;background:rgba(16,185,129,0.04);border:1px solid rgba(16,185,129,0.12);border-radius:10px;">
            <p style="font-size:10px;color:rgba(52,211,153,0.5);margin-bottom:6px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">Student's Notes</p>
            <p style="font-size:12px;color:rgba(160,200,175,0.7);font-style:italic;">{{ $approval->clearance->reason }}</p>
        </div>
        @endif
    </div>

    <!-- All Departments Progress -->
    <div class="glow-card" style="margin-bottom:20px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.5);margin-bottom:16px;">Clearance Progress — All Departments</p>
        @foreach($approval->clearance->approvals->sortBy('department.priority') as $a)
        <div class="dept-prog-row"
             style="border-color:{{ $a->id===$approval->id ? 'rgba(245,158,11,0.4)' : ($a->status==='approved' ? 'rgba(16,185,129,0.25)' : ($a->status==='rejected' ? 'rgba(239,68,68,0.2)' : 'rgba(16,185,129,0.1)')) }};
                    background:{{ $a->id===$approval->id ? 'rgba(245,158,11,0.05)' : ($a->status==='approved' ? 'rgba(16,185,129,0.05)' : 'rgba(16,185,129,0.02)') }};">
            <div style="display:flex;align-items:center;gap:10px;">
                @if($a->status === 'approved')
                <div style="width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;">
                    <svg style="width:11px;height:11px;color:#020904;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                @elseif($a->status === 'rejected')
                <div style="width:20px;height:20px;border-radius:50%;background:#ef4444;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:10px;height:10px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                @else
                <div style="width:20px;height:20px;border-radius:50%;border:2px solid rgba(16,185,129,0.3);"></div>
                @endif
                <span style="font-size:12px;font-weight:600;color:{{ $a->id===$approval->id ? '#fbbf24' : '#e2e8f0' }};">{{ $a->department->name }}</span>
                @if($a->id === $approval->id)
                <span style="font-size:9px;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.35);color:#fbbf24;padding:2px 8px;border-radius:999px;font-weight:700;letter-spacing:0.05em;">YOUR DEPT</span>
                @endif
            </div>
            <span style="font-size:11px;font-weight:700;color:{{ $a->status==='approved' ? '#34d399' : ($a->status==='rejected' ? '#f87171' : 'rgba(251,191,36,0.7)') }};">{{ ucfirst($a->status) }}</span>
        </div>
        @endforeach
    </div>

    <!-- Decision Panel -->
    @if($approval->status === 'pending')
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
        <!-- Approve -->
        <div class="action-panel approve-panel">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;box-shadow:0 0 18px rgba(16,185,129,0.5);">
                    <svg style="width:16px;height:16px;color:#020904;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h4 style="font-size:14px;font-weight:800;color:#34d399;">Approve Clearance</h4>
            </div>
            <form method="POST" action="{{ route('officer.approvals.approve', $approval) }}">
                @csrf
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(52,211,153,0.6);margin-bottom:7px;">Comments (Optional)</label>
                    <textarea name="comments" rows="3" class="glow-input" style="width:100%;resize:none;"
                              placeholder="e.g., All books returned. No outstanding dues."></textarea>
                </div>
                <button type="submit" class="btn-glow" style="width:100%;justify-content:center;">Approve</button>
            </form>
        </div>

        <!-- Reject -->
        <div class="action-panel reject-panel">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#dc2626,#ef4444);display:flex;align-items:center;justify-content:center;">
                    <svg style="width:16px;height:16px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h4 style="font-size:14px;font-weight:800;color:#f87171;">Reject Clearance</h4>
            </div>
            <form method="POST" action="{{ route('officer.approvals.reject', $approval) }}">
                @csrf
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(248,113,113,0.6);margin-bottom:7px;">Rejection Reason <span style="color:#ef4444;">*</span></label>
                    <textarea name="comments" rows="3" required class="glow-input" style="width:100%;resize:none;border-color:rgba(239,68,68,0.25);"
                              placeholder="e.g., Outstanding library fines of TZS 5,000. Unreturned book: Applied Mathematics Vol.2"></textarea>
                </div>
                <button type="submit"
                        style="width:100%;padding:12px;border:none;border-radius:10px;cursor:pointer;background:linear-gradient(135deg,#dc2626,#ef4444);color:white;font-weight:800;font-size:13px;letter-spacing:0.04em;box-shadow:0 0 20px rgba(239,68,68,0.35);transition:all 0.25s;"
                        onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">Reject</button>
            </form>
        </div>
    </div>

    @else
    <div class="glow-card" style="margin-bottom:20px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:rgba(52,211,153,0.5);margin-bottom:14px;">Decision Recorded</p>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
            @if($approval->status==='approved')
            <span class="badge-approved" style="font-size:13px;padding:5px 16px;">Approved</span>
            @else
            <span class="badge-rejected" style="font-size:13px;padding:5px 16px;">Rejected</span>
            @endif
            <span style="font-size:12px;color:rgba(160,200,175,0.5);">by {{ $approval->officer->name ?? 'N/A' }} &mdash; {{ $approval->reviewed_at?->format('d M Y, h:i A') }}</span>
        </div>
        @if($approval->comments)
        <div style="padding:12px 16px;background:rgba(16,185,129,0.04);border:1px solid rgba(16,185,129,0.12);border-radius:10px;">
            <p style="font-size:10px;color:rgba(52,211,153,0.4);margin-bottom:6px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">Comments</p>
            <p style="font-size:12px;color:rgba(160,200,175,0.7);font-style:italic;">"{{ $approval->comments }}"</p>
        </div>
        @endif
    </div>
    @endif

    <a href="{{ route('officer.approvals.index') }}" style="font-size:12px;color:rgba(160,200,175,0.5);text-decoration:none;">&larr; Back to Approvals</a>
</div>
@endsection
