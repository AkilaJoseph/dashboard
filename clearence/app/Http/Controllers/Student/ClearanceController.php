<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Models\ClearanceApproval;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClearanceController extends Controller
{
    public function index()
    {
        $clearances = Auth::user()->clearances()
            ->with('approvals.department')
            ->latest()
            ->get();

        return view('student.clearances.index', compact('clearances'));
    }

    public function create()
    {
        return view('student.clearances.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_year'  => 'required|string',
            'semester'       => 'required|string',
            'clearance_type' => 'required|in:graduation,semester,withdrawal,transfer',
            'reason'         => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // Create clearance
            $clearance = Auth::user()->clearances()->create([
                'clearance_type' => $request->clearance_type,
                'academic_year'  => $request->academic_year,
                'semester'       => $request->semester,
                'reason'         => $request->reason,
                'status'         => 'pending',
                'submitted_at'   => now(),
            ]);

            // Create approval records for all active departments
            $departments = Department::where('is_active', true)->get();
            foreach ($departments as $department) {
                ClearanceApproval::create([
                    'clearance_id' => $clearance->id,
                    'department_id' => $department->id,
                    'status' => 'pending',
                ]);
            }
        });

        return redirect()->route('student.clearances.index')
            ->with('success', 'Clearance request submitted successfully!');
    }

    public function show(Clearance $clearance)
    {
        // Ensure student can only view their own clearances
        if ($clearance->user_id !== Auth::id()) {
            abort(403);
        }

        $clearance->load('approvals.department', 'approvals.officer');

        return view('student.clearances.show', compact('clearance'));
    }

    public function downloadCertificate(Clearance $clearance)
    {
        // Ensure student can only download their own certificate
        if ($clearance->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if clearance is fully approved
        if ($clearance->status !== 'approved') {
            return back()->with('error', 'Certificate can only be downloaded for approved clearances.');
        }

        // Generate PDF (we'll implement this later)
        // For now, return a view
        return view('student.clearances.certificate', compact('clearance'));
    }
}
