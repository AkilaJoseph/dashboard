<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Push notification schedules ───────────────────────────────────────────────
// These use the database queue (default). Ensure `php artisan queue:work` is running.
// Server cron must call `php artisan schedule:run` every minute.

// Daily 06:30 — remind students whose clearance has been stuck >48 h
Schedule::command('push:stuck-requests')->dailyAt('06:30');

// Every Monday 08:00 — notify officers with a pending queue > 5
Schedule::command('push:staff-backlog')->weeklyOn(1, '08:00');

// Every hour — escalate approvals that have exceeded their SLA window
Schedule::command('push:sla-escalation')->hourly();
