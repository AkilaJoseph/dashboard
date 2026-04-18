<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Models\ClearanceApproval;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;
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
        if ($clearance->user_id !== Auth::id()) {
            abort(403);
        }

        $clearance->load('user', 'approvals.department', 'approvals.officer');

        $verificationCode = 'MUST/' . strtoupper(substr($clearance->user->student_id ?? 'STU', 0, 8))
            . '/' . str_pad($clearance->id, 5, '0', STR_PAD_LEFT)
            . '/' . $clearance->submitted_at?->format('Ymd');

        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64'    => false,
            'scale'           => 4,
        ]);
        $qrCode = (new QRCode($options))->render($verificationCode);

        $pdf = Pdf::loadView('student.clearances.certificate_pdf', compact('clearance', 'verificationCode', 'qrCode'))
            ->setPaper('a4', 'portrait');

        $safeId   = str_replace(['/', '\\', ' '], '_', $clearance->user->student_id ?? 'STU');
        $filename = 'MUST_Clearance_Form_' . $safeId . '_' . $clearance->id . '.pdf';

        return $pdf->download($filename);
    }
}
