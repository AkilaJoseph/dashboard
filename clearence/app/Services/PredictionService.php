<?php

namespace App\Services;

use App\Models\ClearanceApproval;
use App\Models\Clearance;
use Illuminate\Support\Facades\DB;

class PredictionService
{
    /**
     * Estimate when a clearance will be fully approved.
     *
     * Algorithm:
     *  - For each pending department (sorted by priority):
     *      avg_hours  = avg(reviewed_at - created_at) for that dept, last 30 days,
     *                   business-day submissions only (Mon–Fri), fallback 24 h if no data.
     *      queue_depth = other pending approvals for same dept submitted before this one.
     *      dept_hours  = avg_hours × (1 + queue_depth)
     *  - Departments run sequentially, so total = sum of dept_hours.
     *  - estimated_completion_at = now + total_hours
     *  - confidence: HIGH ≥10 samples per dept, MEDIUM ≥5, LOW >0, INSUFFICIENT_DATA otherwise.
     */
    public function estimateCompletion(Clearance $clearance): array
    {
        $clearance->loadMissing(['approvals.department']);

        $pending = $clearance->approvals
            ->where('status', 'pending')
            ->sortBy('department.priority')
            ->values();

        if ($pending->isEmpty()) {
            return [
                'estimated_completion_at'  => null,
                'confidence_level'         => 'insufficient_data',
                'per_department_breakdown' => [],
            ];
        }

        $since      = now()->subDays(30)->toDateTimeString();
        $breakdown  = [];
        $totalHours = 0.0;
        $minSamples = PHP_INT_MAX;

        foreach ($pending as $approval) {
            $deptId = $approval->department_id;

            // Avg decision time for this dept over the last 30 days.
            // Only include approvals whose submitted day was a weekday (Mon–Fri).
            $row = DB::selectOne('
                SELECT
                    COUNT(*)                                               AS sample_count,
                    AVG(TIMESTAMPDIFF(SECOND, created_at, reviewed_at))    AS avg_seconds
                FROM clearance_approvals
                WHERE department_id = ?
                  AND reviewed_at IS NOT NULL
                  AND reviewed_at >= ?
                  AND DAYOFWEEK(created_at) NOT IN (1, 7)
            ', [$deptId, $since]);

            $samples  = (int) ($row->sample_count ?? 0);
            // Fallback: 24 h (one business day) when no data exists yet.
            $avgHours = $samples > 0 ? (float) $row->avg_seconds / 3600.0 : 24.0;

            // Queue depth: other requests for this dept that are still pending
            // and were submitted before the current clearance.
            $queueDepth = ClearanceApproval::where('department_id', $deptId)
                ->where('status', 'pending')
                ->where('clearance_id', '!=', $clearance->id)
                ->whereHas('clearance', fn($q) => $q->where('created_at', '<', $clearance->created_at))
                ->count();

            $deptHours  = $avgHours * (1 + $queueDepth);
            $totalHours += $deptHours;
            $minSamples  = min($minSamples, $samples);

            $breakdown[] = [
                'department_id'      => $deptId,
                'department_name'    => $approval->department->name,
                'status'             => $approval->status,
                'avg_decision_hours' => round($avgHours, 1),
                'queue_depth'        => $queueDepth,
                'sample_count'       => $samples,
                'estimated_hours'    => round($deptHours, 1),
            ];
        }

        $confidence = match (true) {
            $minSamples >= 10 => 'high',
            $minSamples >= 5  => 'medium',
            $minSamples > 0   => 'low',
            default           => 'insufficient_data',
        };

        return [
            'estimated_completion_at'  => now()->addHours($totalHours),
            'confidence_level'         => $confidence,
            'per_department_breakdown' => $breakdown,
        ];
    }

    /**
     * Return departments in the 90th percentile of avg decision time this week.
     * Used for the admin bottleneck widget.
     *
     * Each item: {department_id, department_name, avg_hours, sample_count, pending_count}
     */
    public function bottleneckDepartments(): array
    {
        $since = now()->subDays(7)->toDateTimeString();

        $rows = DB::select('
            SELECT
                ca.department_id,
                d.name                                                      AS department_name,
                COUNT(ca.id)                                                AS sample_count,
                AVG(TIMESTAMPDIFF(SECOND, ca.created_at, ca.reviewed_at))   AS avg_seconds
            FROM clearance_approvals ca
            JOIN departments d ON d.id = ca.department_id
            WHERE ca.reviewed_at IS NOT NULL
              AND ca.reviewed_at >= ?
            GROUP BY ca.department_id, d.name
            HAVING COUNT(ca.id) >= 2
            ORDER BY avg_seconds DESC
        ', [$since]);

        if (empty($rows)) {
            return [];
        }

        // 90th-percentile threshold over avg_seconds values found this week.
        $sorted = collect($rows)->sortBy('avg_seconds')->values();
        $idx    = (int) max(0, ceil(0.90 * $sorted->count()) - 1);
        $p90    = (float) ($sorted[$idx]->avg_seconds ?? 0);

        // Pending count per dept.
        $pendingMap = collect(DB::select('
            SELECT department_id, COUNT(*) AS cnt
            FROM clearance_approvals
            WHERE status = ?
            GROUP BY department_id
        ', ['pending']))->keyBy('department_id');

        return collect($rows)
            ->filter(fn($r) => (float) $r->avg_seconds >= $p90)
            ->map(fn($r) => [
                'department_id'   => (int) $r->department_id,
                'department_name' => $r->department_name,
                'avg_hours'       => round((float) $r->avg_seconds / 3600, 1),
                'sample_count'    => (int) $r->sample_count,
                'pending_count'   => (int) ($pendingMap->get($r->department_id)?->cnt ?? 0),
            ])
            ->values()
            ->all();
    }
}
