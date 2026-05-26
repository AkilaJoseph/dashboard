<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint'   => ['required', 'url', 'max:2048'],
            'p256dh_key' => ['required', 'string'],
            'auth_key'   => ['required', 'string'],
            'user_agent' => ['nullable', 'string', 'max:512'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id'      => $request->user()->id,
                'p256dh_key'   => $data['p256dh_key'],
                'auth_key'     => $data['auth_key'],
                'user_agent'   => $data['user_agent'] ?? null,
                'created_at'   => now(),
                'last_used_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:2048'],
        ]);

        PushSubscription::where('endpoint', $data['endpoint'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
