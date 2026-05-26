<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClearanceApproval;
use App\Models\Department;
use App\Models\User;
use App\Services\PushService;
use Illuminate\Http\JsonResponse;

class BottleneckReminderController extends Controller
{
    public function __construct(private readonly PushService $push) {}

    public function send(Department $department): JsonResponse
    {
        $pending = ClearanceApproval::where('department_id', $department->id)
            ->where('status', 'pending')
            ->count();

        $officers = User::where('role', 'officer')
            ->where('department_id', $department->id)
            ->get();

        if ($officers->isEmpty()) {
            return response()->json(['message' => 'No officers assigned to this department.'], 422);
        }

        $title = 'Clearance Approvals Pending';
        $body  = "You have {$pending} clearance request(s) awaiting your review in {$department->name}.";
        $url   = route('officer.approvals.index');

        $this->push->sendToUsers($officers, $title, $body, $url);

        return response()->json([
            'message'          => "Reminder sent to {$officers->count()} officer(s).",
            'officers_notified' => $officers->count(),
        ]);
    }
}
