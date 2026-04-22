<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WebPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly string $url = '/',
    ) {}

    public function via(object $notifiable): array
    {
        return ['webpush'];
    }

    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'url'   => $this->url,
            'icon'  => '/images/pwa-icons/icon-192.png',
            'badge' => '/images/pwa-icons/icon-96.png',
        ];
    }
}
