# Predictive Completion Estimates

**Service:** `App\Services\PredictionService`
**API:** `GET /api/v1/student/requests/{id}/prediction`
**Widget:** `resources/views/student/clearances/partials/prediction-widget.blade.php`

---

## Algorithm — `estimateCompletion(Clearance $clearance)`

### Inputs

| Variable | Source |
|---|---|
| Pending department approvals | `clearance_approvals` where `status = 'pending'`, ordered by `department.priority` |
| Historical decision times | `clearance_approvals` where `reviewed_at IS NOT NULL` and `reviewed_at >= now() - 30 days` |
| Queue depth | Other pending approvals for same department submitted before this clearance |

### Per-department estimate

For each remaining (pending) department **d**:

```
sample_set = approvals for dept d
             with reviewed_at in the last 30 days
             where created_at falls on a weekday (DAYOFWEEK NOT IN 1,7)

avg_hours  = AVG(reviewed_at - created_at) in hours
           = fallback 24h when sample_set is empty

queue_depth = COUNT of other clearances with status='pending' for dept d
              whose clearance.created_at < this clearance.created_at

dept_estimate_hours = avg_hours × (1 + queue_depth)
```

The `queue_depth` multiplier assumes each queued request takes one average cycle before this one is reached. This is conservative; in practice, officers may batch-process items.

### Total estimate

Departments are processed sequentially (governed by `department.priority`), so:

```
total_hours = SUM(dept_estimate_hours for all pending departments)

estimated_completion_at = NOW() + total_hours
```

> **Note on "excluding weekends":** The sample set filters on the *submission* day (`DAYOFWEEK(created_at) NOT IN (1,7)`) rather than the decision day. This avoids including approvals for requests submitted on weekends, which may have inflated wait times due to officers starting on Monday.

### Confidence level

Confidence is set by the **minimum** sample count across all remaining departments (weakest-link principle):

| Samples (minimum across depts) | Confidence |
|---|---|
| ≥ 10 | `high` |
| ≥ 5 | `medium` |
| 1–4 | `low` |
| 0 | `insufficient_data` (widget hidden) |

The widget is hidden entirely when `estimated_completion_at` is null (all approvals already decided, or no data for any department).

---

## Algorithm — `bottleneckDepartments()`

### Purpose

Surfaces departments to admins that are processing approvals significantly slower than the rest this week, so they can send a targeted push reminder to the responsible officers.

### Method

```
sample_set = clearance_approvals
             where reviewed_at >= now() - 7 days
             grouped by department_id
             having COUNT >= 2       (exclude single-decision noise)

avg_seconds per dept = AVG(reviewed_at - created_at)

p90 = 90th-percentile value of avg_seconds across all departments in sample_set

bottlenecks = departments where avg_seconds >= p90
```

The 90th-percentile threshold is computed over the current week's cohort, not a fixed absolute value, so the widget adapts to the typical throughput of each deployment.

### Pending count overlay

A separate query counts `status = 'pending'` approvals per department and overlays it on each bottleneck row, giving admins a sense of backlog severity before sending a reminder.

---

## Performance

The main cost driver is the per-department avg query inside `estimateCompletion`. The migration `2026_04_23_000003_add_prediction_index_to_clearance_approvals.php` adds:

```sql
INDEX ca_dept_reviewed_idx (department_id, reviewed_at)
```

This covers the `WHERE department_id = ? AND reviewed_at IS NOT NULL AND reviewed_at >= ?` predicate directly. With N remaining departments on a clearance, the service executes N+1 queries (N avg lookups + 1 queue depth per dept via Eloquent). For typical deployments (≤10 departments) this is negligible.

**Run the migration before going to production:**

```bash
php artisan migrate
```

---

## Limitations & Known Caveats

- **Sequential assumption:** The algorithm treats departments as a queue, not parallel. If officers in multiple departments can approve simultaneously, actual completion will be faster than predicted.
- **No holiday calendar:** Only weekday *submission* filtering is applied. Public holidays are not excluded.
- **Queue depth is optimistic about batching:** Real officers may process 5 requests in the time avg_hours suggests they'll process 1.
- **Cold start:** Until at least 5 approvals per department have been recorded in the last 30 days, confidence is `low` or `insufficient_data`. Consider pre-populating data or showing a "not enough data yet" message at first deployment.
