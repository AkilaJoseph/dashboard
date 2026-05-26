<?php

namespace App\Services;

use App\Models\PushCampaign;
use App\Models\PushDelivery;
use App\Models\User;
use App\Notifications\WebPushNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PushService
{
    /**
     * Send a push notification to a single user, respecting quiet hours.
     * If the user is currently in their quiet window the notification is
     * delayed (queued with a Carbon delay) until the window ends.
     */
    public function sendToUser(User $user, string $title, string $body, string $url = '/'): void
    {
        $notification = new WebPushNotification($title, $body, $url);

        $delay = $this->quietHoursDelay($user);
        if ($delay > 0) {
            $user->notify($notification->delay(now()->addSeconds($delay)));
        } else {
            $user->notify($notification);
        }
    }

    /**
     * Send the same push to a collection of users (each respects their own quiet hours).
     */
    public function sendToUsers(Collection $users, string $title, string $body, string $url = '/'): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $url);
        }
    }

    /**
     * Dispatch a push campaign to all matching users.
     * Creates PushDelivery records (status=queued) then dispatches notifications.
     * Marks the campaign as sent when finished.
     */
    public function sendCampaign(PushCampaign $campaign): void
    {
        $users = $this->resolveCampaignAudience($campaign->audience ?? []);

        $campaign->update(['status' => 'sending', 'recipient_count' => $users->count()]);

        foreach ($users as $user) {
            $subscriptions = $user->pushSubscriptions;
            if ($subscriptions->isEmpty()) continue;

            foreach ($subscriptions as $sub) {
                $delivery = PushDelivery::create([
                    'campaign_id'     => $campaign->id,
                    'user_id'         => $user->id,
                    'subscription_id' => $sub->id,
                    'status'          => 'queued',
                ]);

                try {
                    $delay        = $this->quietHoursDelay($user);
                    $notification = new WebPushNotification(
                        $campaign->title,
                        $campaign->body,
                        $campaign->target_url ?? '/'
                    );

                    if ($delay > 0) {
                        $user->notify($notification->delay(now()->addSeconds($delay)));
                    } else {
                        $user->notify($notification);
                    }

                    $delivery->update(['status' => 'sent', 'sent_at' => now()]);
                } catch (\Throwable $e) {
                    $delivery->update(['status' => 'failed', 'error' => $e->getMessage()]);
                }
            }
        }

        $campaign->update(['status' => 'sent', 'sent_at' => now()]);
    }

    /**
     * Resolve the audience JSON to a Collection of User models with push subscriptions.
     *
     * Audience shape: {"roles": ["student","officer","admin"], "department_id": null}
     * Any missing key = no filter on that dimension.
     */
    public function resolveCampaignAudience(array $audience): Collection
    {
        $query = User::query()->with('pushSubscriptions')->whereHas('pushSubscriptions');

        if (!empty($audience['roles'])) {
            $query->whereIn('role', (array) $audience['roles']);
        }

        if (!empty($audience['department_id'])) {
            $query->where('department_id', $audience['department_id']);
        }

        return $query->get();
    }

    // ── Quiet hours ───────────────────────────────────────────────────────────

    /**
     * Return the number of seconds to delay a notification so it lands
     * after the user's quiet window ends.  Returns 0 if not in quiet hours.
     */
    public function quietHoursDelay(User $user): int
    {
        $tz  = $user->timezone ?? 'Africa/Dar_es_Salaam';
        $now = now()->setTimezone($tz);

        $startStr = $user->quiet_hours_start ?? '22:00:00';
        $endStr   = $user->quiet_hours_end   ?? '06:00:00';

        $start = $now->copy()->setTimeFromTimeString($startStr);
        $end   = $now->copy()->setTimeFromTimeString($endStr);

        if ($start->gt($end)) {
            // Spans midnight (e.g. 22:00–06:00)
            if ($now->gte($start) || $now->lt($end)) {
                $target = $now->lt($end) ? $end->copy() : $end->copy()->addDay();
                return max(0, (int) $now->diffInSeconds($target, false));
            }
        } else {
            // Same-day window (e.g. 02:00–05:00)
            if ($now->gte($start) && $now->lt($end)) {
                return max(0, (int) $now->diffInSeconds($end, false));
            }
        }

        return 0;
    }
}
