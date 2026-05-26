<?php

namespace App\Console\Commands;

use App\Models\ClearanceApproval;
use App\Services\PushService;
use Illuminate\Console\Command;

/**
 * Daily 06:30 — notify students whose clearance has a pending approval
 * that hasn't moved in more than 48 hours.
 */
class PushStuckRequests extends Command
{
    protected $signature   = 'push:stuck-requests';
    protected $description = 'Notify students whose clearance approval has been pending >48 h';

    public function handle(PushService $push): int
    {
        // Find the most-recently-created pending approval per clearance where
        // that approval is more than 48 hours old and still unreviewed.
        $stuckApprovals = ClearanceApproval::query()
            ->whereNull('reviewed_at')
            ->where('created_at', '<=', now()->subHours(48))
            ->with(['clearance.user', 'department'])
            ->get()
            // Keep only one record per clearance (the oldest stuck one)
            ->unique('clearance_id');

        $count = 0;

        foreach ($stuckApprovals as $approval) {
            $student = $approval->clearance->user ?? null;
            if (! $student) continue;

            // Honour the student's push preference
            $prefs = $student->notification_preferences ?? [];
            if (($prefs['push'] ?? true) === false) continue;

            $dept = $approval->department->name ?? 'a department';
            $type = ucfirst($approval->clearance->clearance_type ?? 'clearance');

            $push->sendToUser(
                $student,
                'Clearance Awaiting Action',
                "Your {$type} clearance is still waiting for approval from {$dept}.",
                '/student/clearances/' . $approval->clearance_id
            );

            $count++;
        }

        $this->info("Sent stuck-request reminders to {$count} student(s).");
        return self::SUCCESS;
    }
}
