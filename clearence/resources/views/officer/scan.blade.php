@extends('layouts.app')

@section('title', 'Scan Student QR')
@section('page-title', 'Scan Student QR')
@section('page-subtitle', 'Point the camera at the student\'s QR card to load their clearance')

@section('content')
<div style="max-width:520px;margin:0 auto;">

    {{-- ── Camera viewport ──────────────────────────────────────────────────────── --}}
    <div class="glow-card" style="padding:20px;margin-bottom:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin:0;">Camera</p>
            <button id="btn-scan" onclick="window.deptScanner?.toggle()"
                    style="padding:6px 18px;border-radius:8px;border:none;background:#059669;color:#fff;font-size:12px;font-weight:700;cursor:pointer;">
                Start Scanner
            </button>
        </div>

        <div id="scanner-container" style="border-radius:12px;overflow:hidden;background:#0f172a;min-height:200px;display:flex;align-items:center;justify-content:center;">
            <p id="scanner-placeholder" style="font-size:13px;color:#475569;text-align:center;padding:32px;">
                Press <strong>Start Scanner</strong> to activate your camera.
            </p>
        </div>

        <p id="scan-status" style="font-size:12px;color:#94a3b8;margin:10px 0 0;text-align:center;min-height:18px;"></p>
    </div>

    {{-- ── Manual token entry ────────────────────────────────────────────────────── --}}
    <div class="glow-card" style="margin-bottom:14px;">
        <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:10px;">Manual Entry</p>
        <form id="manual-form" style="display:flex;gap:8px;">
            <input id="manual-token" type="text" class="glow-input"
                   placeholder="Paste JWT token here…"
                   style="flex:1;font-size:11px;font-family:monospace;">
            <button type="submit" class="btn-glow" style="font-size:12px;flex-shrink:0;">Verify</button>
        </form>
    </div>

    {{-- ── Result panel ─────────────────────────────────────────────────────────── --}}
    <div id="result-panel" style="display:none;">

        {{-- Student info card --}}
        <div class="glow-card" style="margin-bottom:14px;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:12px;">Student</p>
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
                <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span id="student-initial" style="font-size:18px;font-weight:800;color:#fff;"></span>
                </div>
                <div>
                    <p id="student-name"       style="font-size:15px;font-weight:700;color:#1e293b;margin:0 0 2px;"></p>
                    <p id="student-student-id" style="font-size:12px;color:#64748b;margin:0;"></p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div style="padding:9px 11px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                    <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin:0 0 2px;">Programme</p>
                    <p id="student-programme" style="font-size:12px;font-weight:600;color:#1e293b;margin:0;"></p>
                </div>
                <div style="padding:9px 11px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                    <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin:0 0 2px;">College</p>
                    <p id="student-college" style="font-size:12px;font-weight:600;color:#1e293b;margin:0;"></p>
                </div>
            </div>
        </div>

        {{-- Clearance info --}}
        <div class="glow-card" style="margin-bottom:14px;">
            <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:12px;">Clearance Request</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
                <div style="padding:9px 11px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                    <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin:0 0 2px;">Type</p>
                    <p id="clearance-type" style="font-size:12px;font-weight:600;color:#1e293b;margin:0;"></p>
                </div>
                <div style="padding:9px 11px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                    <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin:0 0 2px;">Status</p>
                    <p id="clearance-status" style="font-size:12px;font-weight:600;color:#1e293b;margin:0;"></p>
                </div>
                <div style="padding:9px 11px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                    <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin:0 0 2px;">Academic Year</p>
                    <p id="clearance-year" style="font-size:12px;font-weight:600;color:#1e293b;margin:0;"></p>
                </div>
                <div style="padding:9px 11px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                    <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#94a3b8;margin:0 0 2px;">Semester</p>
                    <p id="clearance-semester" style="font-size:12px;font-weight:600;color:#1e293b;margin:0;"></p>
                </div>
            </div>

            {{-- Approval status for this department --}}
            <div id="approval-wrap" style="padding:12px 14px;border-radius:10px;border:1.5px solid #e2e8f0;">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;margin:0 0 6px;">Your Department</p>
                <p id="approval-dept" style="font-size:13px;font-weight:700;color:#1e293b;margin:0 0 4px;"></p>
                <p id="approval-status-badge" style="font-size:12px;margin:0;"></p>
            </div>
        </div>

        {{-- Action buttons --}}
        <div id="action-panel" style="display:none;">
            <div class="glow-card" style="margin-bottom:14px;">
                <p style="font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#059669;margin-bottom:12px;">Decision</p>

                <div style="margin-bottom:10px;">
                    <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:5px;">Comments (required for rejection)</label>
                    <textarea id="action-comments" rows="2" class="glow-input" style="resize:none;"
                              placeholder="Optional comments for approval; required for rejection…"></textarea>
                </div>

                <div style="display:flex;gap:10px;">
                    <button id="btn-approve" onclick="window.deptScanner?.decide('approve')"
                            class="btn-glow" style="flex:1;font-size:13px;">
                        Approve
                    </button>
                    <button id="btn-reject" onclick="window.deptScanner?.decide('reject')"
                            style="flex:1;padding:10px;border:1.5px solid #fca5a5;border-radius:9px;background:#fff;font-size:13px;font-weight:700;color:#ef4444;cursor:pointer;">
                        Reject
                    </button>
                </div>

                <p id="action-msg" style="font-size:12px;margin:10px 0 0;text-align:center;min-height:18px;"></p>
            </div>
        </div>

        <div style="text-align:center;margin-bottom:14px;">
            <button onclick="window.deptScanner?.reset()"
                    style="font-size:12px;color:#64748b;background:none;border:none;cursor:pointer;text-decoration:underline;">
                Scan Another Student
            </button>
        </div>
    </div>

    <a href="{{ route('officer.dashboard') }}" style="font-size:13px;color:#64748b;text-decoration:none;">&larr; Back to Dashboard</a>

</div>

<script type="module">
import DeptScanner from '{{ Vite::asset('resources/js/qr/scanner.js') }}';

window.deptScanner = new DeptScanner({
    scanApiUrl:     '{{ route("api.department.scan") }}',
    approveUrlTpl:  '/officer/approvals/{id}/approve',
    rejectUrlTpl:   '/officer/approvals/{id}/reject',
    csrfToken:      document.querySelector('meta[name="csrf-token"]').content,
    containerId:    'scanner-container',
    placeholderId:  'scanner-placeholder',
    statusId:       'scan-status',
    manualFormId:   'manual-form',
    manualInputId:  'manual-token',
    resultPanelId:  'result-panel',
    actionPanelId:  'action-panel',
    actionMsgId:    'action-msg',
    commentsId:     'action-comments',
    fields: {
        studentInitial:   'student-initial',
        studentName:      'student-name',
        studentId:        'student-student-id',
        studentProgramme: 'student-programme',
        studentCollege:   'student-college',
        clearanceType:    'clearance-type',
        clearanceStatus:  'clearance-status',
        clearanceYear:    'clearance-year',
        clearanceSemester:'clearance-semester',
        approvalDept:     'approval-dept',
        approvalStatus:   'approval-status-badge',
    },
});
</script>
@endsection
