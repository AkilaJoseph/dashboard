<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WebPushNotification;
use Illuminate\Support\Collection;

class PushService
{
    /**
     * Send a push notification to a single user.
     */
    public function sendToUser(User $user, string $title, string $body, string $url = '/'): void
    {
        $user->notify(new WebPushNotification($title, $body, $url));
    }

    /**
     * Send a push notification to a collection of users.
     */
    public function sendToUsers(Collection $users, string $title, string $body, string $url = '/'): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $url);
        }
    }
}
