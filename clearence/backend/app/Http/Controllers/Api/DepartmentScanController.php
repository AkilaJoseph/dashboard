<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use App\Services\QrTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class DepartmentScanController extends Controller
{
    public function __construct(private QrTokenService $qr) {}

    public function scan(Request $request): JsonResponse
    {
        $officer = Auth::user();

        if ($officer->role !== 'officer') {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $request->validate(['token' => 'required|string']);

        try {
            $payload = $this->qr->verify($request->token);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $approval = ClearanceApproval::where('clearance_id', $payload->clearance_id)
            ->where('department_id', $officer->department_id)
            ->with(['clearance.user', 'department'])
            ->first();

        if (! $approval) {
            return response()->json([
                'error' => 'This student has no pending clearance for your department.',
            ], 404);
        }

        $clearance = $approval->clearance;
        $student   = $clearance->user;

        return response()->json([
            'approval' => [
                'id'          => $approval->id,
                'status'      => $approval->status,
                'comments'    => $approval->comments,
                'reviewed_at' => $approval->reviewed_at?->toIso8601String(),
            ],
            'clearance' => [
                'id'             => $clearance->id,
                'clearance_type' => $clearance->clearance_type,
                'academic_year'  => $clearance->academic_year,
                'semester'       => $clearance->semester,
                'status'         => $clearance->status,
                'submitted_at'   => $clearance->submitted_at?->toIso8601String(),
            ],
            'student' => [
                'id'         => $student->id,
                'name'       => $student->name,
                'student_id' => $student->student_id ?? '',
                'programme'  => $student->programme  ?? '',
                'college'    => $student->college    ?? '',
            ],
            'department' => $approval->department?->name,
        ]);
    }
}
