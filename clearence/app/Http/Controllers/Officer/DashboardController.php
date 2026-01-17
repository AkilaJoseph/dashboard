<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $officer = Auth::user();
        $approvals = ClearanceApproval::where('department_id', $officer->department_id)
            ->with('clearance.user')
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'pending' => ClearanceApproval::where('department_id', $officer->department_id)->where('status', 'pending')->count(),
            'approved' => ClearanceApproval::where('department_id', $officer->department_id)->where('status', 'approved')->count(),
            'rejected' => ClearanceApproval::where('department_id', $officer->department_id)->where('status', 'rejected')->count(),
        ];

        return view('officer.dashboard', compact('approvals', 'stats'));
    }
}
