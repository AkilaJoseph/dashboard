<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->latest()->take(30)->get();
        return view('notifications.index', compact('notifications'));
    }

    public function unread()
    {
        $user  = Auth::user();
        $items = $user->unreadNotifications()->latest()->take(5)->get();

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'items' => $items->map(fn($n) => [
                'id'         => $n->id,
                'message'    => $n->data['message'],
                'icon'       => $n->data['icon'],
                'status'     => $n->data['status'],
                'clearance_id' => $n->data['clearance_id'],
                'created_at' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function markRead(Request $request)
    {
        if ($request->id) {
            Auth::user()->notifications()->where('id', $request->id)->update(['read_at' => now()]);
        } else {
            Auth::user()->unreadNotifications->markAsRead();
        }
        return response()->json(['ok' => true]);
    }
}
