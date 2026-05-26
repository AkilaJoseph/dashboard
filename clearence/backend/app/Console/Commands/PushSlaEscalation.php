<?php

namespace App\Console\Commands;

use App\Models\ClearanceApproval;
use App\Models\User;
use App\Services\PushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Hourly — find approvals that have exceeded their SLA (per clearance_type_configs.sla_hours)
 * and push to all officers in the relevant department plus all admins.
 *
 * NOTE: "department head" is not modelled in the schema (departments has no head_user_id).
 * This command pushes to ALL active officers in the department as the closest proxy.
 * Add a head_user_id FK to departments and update this command when that is modelled.
 */
class PushSlaEscalation extends Command
{
    protected $signature   = 'push:sla-escalation';
    protected $description = 'Escalate clearance approvals that have exceeded their SLA';

    public function handle(PushService $push): int
    {
        // Join clearances → clearance_type_configs to get per-type SLA threshold.
        // reviewed_at IS NULL = not yet decided.
        $overdue = ClearanceApproval::query()
            ->select('clearance_approvals.*')
            ->join('clearances', 'clearance_approvals.clearance_id', '=', 'clearances.id')
            ->join(
                'clearance_type_configs',
                'clearances.clearance_type',
                '=',
                'clearance_type_configs.type'
            )
            ->whereNull('clearance_approvals.reviewed_at')
            ->whereRaw(
                'TIMESTAMPDIFF(HOUR, clearance_approvals.created_at, NOW()) > clearance_type_configs.sla_hours'
            )
            ->with(['clearance.user', 'department.officers'])
            ->get();

        if ($overdue->isEmpty()) {
            $this->info('No SLA breaches found.');
            return self::SUCCESS;
        }

        $admins = User::where('role', 'admin')
            ->whereHas('pushSubscriptions')
            ->get();

        $escalated = 0;

        foreach ($overdue as $approval) {
            $dept     = $approval->department;
            $deptName = $dept->name ?? 'Unknown';
            $type     = ucfirst($approval->clearance->clearance_type ?? 'clearance');
            $hoursOld = (int) now()->diffInHours($approval->created_at);
            $url      = '/admin/clearances/' . $approval->clearance_id;

            $title = 'SLA Breach — Action Required';
            $body  = "{$deptName}: {$type} clearance #{$approval->clearance_id} has been "
                   . "pending for {$hoursOld} h (SLA exceeded).";

            // Push to all officers in the department
            foreach ($dept->officers ?? [] as $officer) {
                if ($officer->pushSubscriptions->isEmpty()) continue;
                $prefs = $officer->notification_preferences ?? [];
                if (($prefs['push'] ?? true) === false) continue;
                $push->sendToUser($officer, $title, $body, '/officer/approvals/' . $approval->id);
            }

            // Push to all admins
            foreach ($admins as $admin) {
                $prefs = $admin->notification_preferences ?? [];
                if (($prefs['push'] ?? true) === false) continue;
                $push->sendToUser($admin, $title, $body, $url);
            }

            $escalated++;
        }

        $this->info("Escalated {$escalated} SLA breach(es).");
        return self::SUCCESS;
    }
}
