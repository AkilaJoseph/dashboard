<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationSettingsController extends Controller
{
    public function index()
    {
        $user          = Auth::user();
        $subscriptions = $user->pushSubscriptions()->orderByDesc('last_used_at')->get();
        $prefs         = $user->notification_preferences ?? ['push' => true, 'database' => true];

        return view('student.notification-settings', compact('subscriptions', 'prefs'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'push'     => ['sometimes', 'boolean'],
            'database' => ['sometimes', 'boolean'],
        ]);

        $current = Auth::user()->notification_preferences ?? ['push' => true, 'database' => true];

        Auth::user()->update([
            'notification_preferences' => array_merge($current, [
                'push'     => (bool) ($data['push']     ?? $current['push']     ?? true),
                'database' => (bool) ($data['database'] ?? $current['database'] ?? true),
            ]),
        ]);

        return back()->with('success', 'Notification preferences saved.');
    }

    public function removeDevice(Request $request)
    {
        $data = $request->validate([
            'subscription_id' => ['required', 'integer'],
        ]);

        PushSubscription::where('id', $data['subscription_id'])
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Device removed.');
    }
}
