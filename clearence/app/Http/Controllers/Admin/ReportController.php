<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Models\ClearanceApproval;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Summary stats
        $stats = [
            'total_students'   => User::where('role', 'student')->count(),
            'total_clearances' => Clearance::count(),
            'approved'         => Clearance::where('status', 'approved')->count(),
            'pending'          => Clearance::where('status', 'pending')->count(),
            'in_progress'      => Clearance::where('status', 'in_progress')->count(),
            'rejected'         => Clearance::where('status', 'rejected')->count(),
        ];

        // Clearances by type
        $byType = Clearance::selectRaw('clearance_type, count(*) as count')
            ->groupBy('clearance_type')
            ->get()
            ->pluck('count', 'clearance_type');

        // Clearances per department (approval stats)
        $deptStats = Department::withCount([
            'approvals',
            'approvals as approved_count' => fn ($q) => $q->where('status', 'approved'),
            'approvals as pending_count'  => fn ($q) => $q->where('status', 'pending'),
            'approvals as rejected_count' => fn ($q) => $q->where('status', 'rejected'),
        ])->orderBy('priority')->get();

        // Recent approved clearances
        $recentApproved = Clearance::where('status', 'approved')
            ->with('user')
            ->latest('completed_at')
            ->take(10)
            ->get();

        // Filter by academic year if provided
        $selectedYear = $request->input('year');
        $filteredClearances = null;
        if ($selectedYear) {
            $filteredClearances = Clearance::where('academic_year', $selectedYear)
                ->with('user')
                ->latest()
                ->get();
        }

        // Available academic years
        $academicYears = Clearance::distinct()->pluck('academic_year')->sort()->values();

        return view('admin.reports.index', compact(
            'stats', 'byType', 'deptStats', 'recentApproved',
            'filteredClearances', 'selectedYear', 'academicYears'
        ));
    }
}
