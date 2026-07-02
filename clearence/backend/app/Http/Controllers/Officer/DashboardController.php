<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $officer   = Auth::user();
        $deptId    = $officer->department_id;

        // Recent requests: show pending + decided, not waiting (not yet this dept's turn)
        $approvals = ClearanceApproval::where('department_id', $deptId)
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->with('clearance.user')
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total'    => ClearanceApproval::where('department_id', $deptId)->count(),
            'pending'  => ClearanceApproval::where('department_id', $deptId)->where('status', 'pending')->count(),
            'approved' => ClearanceApproval::where('department_id', $deptId)->where('status', 'approved')->count(),
            'rejected' => ClearanceApproval::where('department_id', $deptId)->where('status', 'rejected')->count(),
            'waiting'  => ClearanceApproval::where('department_id', $deptId)->where('status', 'waiting')->count(),
        ];

        return view('officer.dashboard', compact('approvals', 'stats'));
    }
}
