<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use App\Models\Clearance;
use App\Notifications\DepartmentApprovalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $officer = Auth::user();

        $query = ClearanceApproval::where('department_id', $officer->department_id)
            ->with(['clearance.user', 'department']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $approvals = $query->latest()->paginate(15);

        return view('officer.approvals.index', compact('approvals'));
    }

    public function show(ClearanceApproval $approval)
    {
        // Ensure officer can only view approvals from their department
        if ($approval->department_id !== Auth::user()->department_id) {
            abort(403);
        }

        $approval->load('clearance.user', 'clearance.approvals.department', 'department');

        return view('officer.approvals.show', compact('approval'));
    }

    public function approve(Request $request, ClearanceApproval $approval)
    {
        // Ensure officer can only approve for their department
        if ($approval->department_id !== Auth::user()->department_id) {
            abort(403);
        }

        $request->validate([
            'comments' => 'nullable|string|max:500',
        ]);

        $approval->update([
            'status' => 'approved',
            'officer_id' => Auth::id(),
            'comments' => $request->comments,
            'reviewed_at' => now(),
        ]);

        $approval->clearance->updateOverallStatus();
        $approval->clearance->user->notify(new DepartmentApprovalNotification($approval));

        return redirect()->route('officer.approvals.index')
            ->with('success', 'Clearance approved successfully!');
    }

    public function reject(Request $request, ClearanceApproval $approval)
    {
        // Ensure officer can only reject for their department
        if ($approval->department_id !== Auth::user()->department_id) {
            abort(403);
        }

        $request->validate([
            'comments' => 'required|string|max:500',
        ]);

        $approval->update([
            'status' => 'rejected',
            'officer_id' => Auth::id(),
            'comments' => $request->comments,
            'reviewed_at' => now(),
        ]);

        $approval->clearance->updateOverallStatus();
        $approval->clearance->user->notify(new DepartmentApprovalNotification($approval));

        return redirect()->route('officer.approvals.index')
            ->with('success', 'Clearance rejected.');
    }
}
