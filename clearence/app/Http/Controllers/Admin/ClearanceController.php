<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Models\ClearanceApproval;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class ClearanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Clearance::with('user')->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('clearance_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $clearances = $query->paginate(20)->withQueryString();

        $stats = [
            'total'       => Clearance::count(),
            'pending'     => Clearance::where('status', 'pending')->count(),
            'in_progress' => Clearance::where('status', 'in_progress')->count(),
            'approved'    => Clearance::where('status', 'approved')->count(),
            'rejected'    => Clearance::where('status', 'rejected')->count(),
        ];

        return view('admin.clearances.index', compact('clearances', 'stats'));
    }

    public function show(Clearance $clearance)
    {
        $this->authorize('adminView', $clearance);
        $clearance->load('user', 'approvals.department', 'approvals.officer');
        $departments = Department::where('is_active', true)->orderBy('priority')->get();

        return view('admin.clearances.show', compact('clearance', 'departments'));
    }

    public function override(Request $request, Clearance $clearance, ClearanceApproval $approval)
    {
        $this->authorize('override', $clearance);
        // Ensure the approval belongs to this clearance
        if ($approval->clearance_id !== $clearance->id) {
            abort(403);
        }

        $request->validate([
            'action'   => 'required|in:approved,rejected,pending',
            'comments' => 'nullable|string|max:500',
        ]);

        $approval->update([
            'status'      => $request->action,
            'officer_id'  => auth()->id(),
            'comments'    => $request->comments ?? '[Admin override]',
            'reviewed_at' => now(),
        ]);

        // Recalculate overall clearance status
        $clearance->updateOverallStatus();

        return redirect()->route('admin.clearances.show', $clearance)
            ->with('success', 'Approval status overridden to "' . $request->action . '" successfully.');
    }
}
