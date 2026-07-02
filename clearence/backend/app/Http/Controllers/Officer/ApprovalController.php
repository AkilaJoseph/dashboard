<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use App\Models\User;
use App\Notifications\DepartmentApprovalNotification;
use App\Services\CertificateLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $officer = Auth::user();

        $query = ClearanceApproval::where('department_id', $officer->department_id)
            ->with(['clearance.user', 'department']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            // Default: hide 'waiting' (locked) approvals — not actionable yet
            $query->whereIn('status', ['pending', 'approved', 'rejected']);
        }

        $approvals = $query->latest()->paginate(15);

        return view('officer.approvals.index', compact('approvals'));
    }

    public function show(ClearanceApproval $approval)
    {
        $this->authorize('view', $approval);
        if ($approval->department_id !== Auth::user()->department_id) {
            abort(403);
        }

        $approval->load('clearance.user', 'clearance.approvals.department', 'clearance.attachments', 'department');

        return view('officer.approvals.show', compact('approval'));
    }

    public function approve(Request $request, ClearanceApproval $approval)
    {
        $this->authorize('decide', $approval);
        if ($approval->department_id !== Auth::user()->department_id) {
            abort(403);
        }

        // A 'waiting' approval is not yet the student's turn — block action
        if ($approval->isWaiting()) {
            return back()->withErrors(['pin' => 'This clearance step is not yet active for your department.']);
        }

        $request->validate([
            'comments'       => 'nullable|string|max:500',
            'department_pin' => 'required|string',
        ]);

        // Verify the department PIN
        $dept = Auth::user()->department;
        if (!$dept || !Hash::check($request->department_pin, $dept->access_pin)) {
            return back()->withErrors(['department_pin' => 'Incorrect department PIN. Action not recorded.'])->withInput();
        }

        $approval->update([
            'status'      => 'approved',
            'officer_id'  => Auth::id(),
            'comments'    => $request->comments,
            'reviewed_at' => now(),
        ]);

        $approval->clearance->updateOverallStatus();

        // If the clearance is now fully approved, seal the ledger
        if ($approval->clearance->status === 'approved') {
            $approval->clearance->load('user', 'approvals.department', 'approvals.officer');
            app(CertificateLedgerService::class)->append($approval->clearance);
        } else {
            // Unlock the next waiting step for this clearance
            $this->unlockNextStep($approval);
        }

        $approval->clearance->user->notify(new DepartmentApprovalNotification($approval));

        return redirect()->route('officer.approvals.index')
            ->with('success', 'Clearance approved. Next department has been notified.');
    }

    public function reject(Request $request, ClearanceApproval $approval)
    {
        $this->authorize('decide', $approval);
        if ($approval->department_id !== Auth::user()->department_id) {
            abort(403);
        }

        if ($approval->isWaiting()) {
            return back()->withErrors(['pin' => 'This clearance step is not yet active for your department.']);
        }

        $request->validate([
            'comments'       => 'required|string|max:500',
            'department_pin' => 'required|string',
        ]);

        $dept = Auth::user()->department;
        if (!$dept || !Hash::check($request->department_pin, $dept->access_pin)) {
            return back()->withErrors(['department_pin' => 'Incorrect department PIN. Action not recorded.'])->withInput();
        }

        $approval->update([
            'status'      => 'rejected',
            'officer_id'  => Auth::id(),
            'comments'    => $request->comments,
            'reviewed_at' => now(),
        ]);

        $approval->clearance->updateOverallStatus();
        $approval->clearance->user->notify(new DepartmentApprovalNotification($approval));

        return redirect()->route('officer.approvals.index')
            ->with('success', 'Clearance rejected. Student has been notified.');
    }

    // Unlock the next waiting approval (next step in the sequence) after a successful approval.
    private function unlockNextStep(ClearanceApproval $justApproved): void
    {
        $next = ClearanceApproval::where('clearance_id', $justApproved->clearance_id)
            ->where('status', 'waiting')
            ->join('departments', 'clearance_approvals.department_id', '=', 'departments.id')
            ->orderBy('departments.priority')
            ->select('clearance_approvals.*')
            ->first();

        if (!$next) {
            return;
        }

        $next->update(['status' => 'pending']);

        // Notify the officer(s) of the next department
        $nextOfficer = User::where('department_id', $next->department_id)
            ->where('role', 'officer')
            ->where('is_active', true)
            ->first();

        if ($nextOfficer) {
            $nextOfficer->notify(new DepartmentApprovalNotification($next));
        }
    }
}
