<?php

namespace App\Notifications\Channels;

use App\Models\PushSubscription;
use Illuminate\Notifications\Notification;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWebPush')) {
            return;
        }

        $cfg = config('services.webpush');
        if (empty($cfg['public_key']) || empty($cfg['private_key'])) {
            return; // VAPID keys not configured yet — skip silently
        }

        $subscriptions = PushSubscription::where('user_id', $notifiable->getKey())->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = $notification->toWebPush($notifiable);

        $webPush = new WebPush([
            'VAPID' => [
                'subject'    => $cfg['subject'],
                'publicKey'  => $cfg['public_key'],
                'privateKey' => $cfg['private_key'],
            ],
        ]);
        $webPush->setReuseVAPIDHeaders(true);

        foreach ($subscriptions as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'contentEncoding' => 'aesgcm',
                    'keys'            => [
                        'p256dh' => $sub->p256dh_key,
                        'auth'   => $sub->auth_key,
                    ],
                ]),
                json_encode($payload)
            );
        }

        /** @var MessageSentReport $report */
        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                // Endpoint gone — remove stale subscription
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();
            }
        }
    }
}
