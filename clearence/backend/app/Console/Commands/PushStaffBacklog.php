<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PushService;
use Illuminate\Console\Command;

/**
 * Monday 08:00 — push a backlog summary to any officer with >5 pending approvals.
 */
class PushStaffBacklog extends Command
{
    protected $signature   = 'push:staff-backlog';
    protected $description = 'Notify officers with a pending approval queue > 5';

    public function handle(PushService $push): int
    {
        $officers = User::where('role', 'officer')
            ->with(['pushSubscriptions', 'department'])
            ->get();

        $notified = 0;

        foreach ($officers as $officer) {
            if ($officer->pushSubscriptions->isEmpty()) continue;

            $prefs = $officer->notification_preferences ?? [];
            if (($prefs['push'] ?? true) === false) continue;

            $pending = \App\Models\ClearanceApproval::query()
                ->whereNull('reviewed_at')
                ->where('department_id', $officer->department_id)
                ->count();

            if ($pending <= 5) continue;

            $push->sendToUser(
                $officer,
                'Clearance Queue Reminder',
                "You have {$pending} pending clearance approval(s) waiting in your queue.",
                '/officer/approvals?status=pending'
            );

            $notified++;
        }

        $this->info("Sent backlog summaries to {$notified} officer(s).");
        return self::SUCCESS;
    }
}
