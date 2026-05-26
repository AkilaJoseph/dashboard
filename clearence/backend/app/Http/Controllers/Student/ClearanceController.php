<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClearanceRequest;
use App\Models\Attachment;
use App\Models\Clearance;
use App\Models\ClearanceApproval;
use App\Models\CertificateLedger;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function store(StoreClearanceRequest $request)
    {
        DB::transaction(function () use ($request) {
            $clearance = Auth::user()->clearances()->create([
                'clearance_type' => $request->clearance_type,
                'academic_year'  => $request->academic_year,
                'semester'       => $request->semester,
                'reason'         => $request->reason,
                'status'         => 'pending',
                'submitted_at'   => now(),
            ]);

            // Store reference number now that the id is known.
            $clearance->update([
                'reference_no' => 'CLR/' . now()->year . '/' . str_pad($clearance->id, 6, '0', STR_PAD_LEFT),
            ]);

            // Seed one approval row per active department.
            $departments = Department::where('is_active', true)->get();
            foreach ($departments as $department) {
                ClearanceApproval::create([
                    'clearance_id'  => $clearance->id,
                    'department_id' => $department->id,
                    'status'        => 'pending',
                ]);
            }

            // Handle uploaded supporting documents.
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    // Second MIME check via finfo — guards against MIME spoofing
                    // even though the Form Request already validated with mimes:.
                    $finfo      = new \finfo(FILEINFO_MIME_TYPE);
                    $actualMime = $finfo->file($file->getRealPath());
                    $allowed    = ['application/pdf', 'image/jpeg', 'image/png'];

                    if (! in_array($actualMime, $allowed, true)) {
                        continue;
                    }

                    $ext        = strtolower($file->getClientOriginalExtension());
                    $storedPath = Storage::disk('attachments')->putFileAs(
                        'clearance_' . $clearance->id,
                        $file,
                        uniqid('', true) . '.' . $ext
                    );

                    Attachment::create([
                        'clearance_id' => $clearance->id,
                        'file_name'    => $file->getClientOriginalName(),
                        'stored_path'  => $storedPath,
                        'mime_type'    => $actualMime,
                        'size_bytes'   => $file->getSize(),
                        'uploaded_at'  => now(),
                    ]);
                }
            }
        });

        return redirect()->route('student.clearances.index')
            ->with('success', 'Clearance request submitted successfully!');
    }

    public function show(Clearance $clearance)
    {
        $this->authorize('view', $clearance);
        // Authorised by ClearancePolicy@view + explicit ownership check (belt + braces).
        if ($clearance->user_id !== Auth::id()) {
            abort(403);
        }

        $clearance->load('approvals.department', 'approvals.officer', 'attachments');

        return view('student.clearances.show', compact('clearance'));
    }

    public function downloadCertificate(Clearance $clearance)
    {
        $this->authorize('downloadCertificate', $clearance);
        // Authorised by ClearancePolicy@downloadCertificate + explicit ownership check (belt + braces).
        if ($clearance->user_id !== Auth::id()) {
            abort(403);
        }

        if ($clearance->status !== 'approved') {
            abort(403, 'Certificate is only available for fully approved clearances.');
        }

        $clearance->load('user', 'approvals.department', 'approvals.officer', 'finalApprover');

        $verificationCode = 'MUST/' . strtoupper(substr($clearance->user->student_id ?? 'STU', 0, 8))
            . '/' . str_pad($clearance->id, 5, '0', STR_PAD_LEFT)
            . '/' . $clearance->submitted_at?->format('Ymd');

        $ledger = CertificateLedger::where('clearance_id', $clearance->id)->first();

        $qrPayload = $ledger
            ? url('/verify/' . $clearance->id)
              . '?seq=' . $ledger->sequence
              . '&h='   . substr($ledger->certificate_hash, 0, 16)
            : $verificationCode;

        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64'    => false,
            'scale'           => 4,
        ]);
        $qrCode = (new QRCode($options))->render($qrPayload);

        $pdf = Pdf::loadView('student.clearances.certificate_pdf', compact('clearance', 'verificationCode', 'qrCode', 'ledger'))
            ->setPaper('a4', 'portrait');

        $safeId   = str_replace(['/', '\\', ' '], '_', $clearance->user->student_id ?? 'STU');
        $filename = 'MUST_Clearance_Form_' . $safeId . '_' . $clearance->id . '.pdf';

        return $pdf->download($filename);
    }
}
