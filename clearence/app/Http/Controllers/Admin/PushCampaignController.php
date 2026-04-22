<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\PushCampaign;
use App\Models\User;
use App\Services\PushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushCampaignController extends Controller
{
    public function __construct(private PushService $push) {}

    public function index()
    {
        $campaigns = PushCampaign::with('creator')
            ->latest()
            ->paginate(20);

        return view('admin.push-campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $departments        = Department::where('is_active', true)->orderBy('name')->get();
        $subscriberCounts   = [
            'student' => User::where('role', 'student')->whereHas('pushSubscriptions')->count(),
            'officer' => User::where('role', 'officer')->whereHas('pushSubscriptions')->count(),
            'admin'   => User::where('role', 'admin')->whereHas('pushSubscriptions')->count(),
        ];

        return view('admin.push-campaigns.create', compact('departments', 'subscriberCounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:100'],
            'body'          => ['required', 'string', 'max:500'],
            'image_url'     => ['nullable', 'url', 'max:2048'],
            'target_url'    => ['nullable', 'string', 'max:512'],
            'audience_roles'=> ['required', 'array', 'min:1'],
            'audience_roles.*' => ['in:student,officer,admin'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'scheduled_at'  => ['nullable', 'date', 'after:now'],
            'action'        => ['required', 'in:send,schedule'],
        ]);

        $campaign = PushCampaign::create([
            'created_by'  => Auth::id(),
            'title'       => $data['title'],
            'body'        => $data['body'],
            'image_url'   => $data['image_url'] ?? null,
            'target_url'  => $data['target_url'] ?? '/',
            'audience'    => [
                'roles'         => $data['audience_roles'],
                'department_id' => $data['department_id'] ?? null,
            ],
            'status'      => $data['action'] === 'schedule' ? 'scheduled' : 'draft',
            'scheduled_at'=> $data['action'] === 'schedule' ? $data['scheduled_at'] : null,
        ]);

        if ($data['action'] === 'send') {
            $this->push->sendCampaign($campaign);
            return redirect()->route('admin.push-campaigns.index')
                ->with('success', "Campaign \"{$campaign->title}\" dispatched to {$campaign->recipient_count} recipient(s).");
        }

        return redirect()->route('admin.push-campaigns.index')
            ->with('success', "Campaign scheduled for {$campaign->scheduled_at->format('d M Y, H:i')}.");
    }

    /** Send a preview push to the currently logged-in admin's own devices. */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body'  => ['required', 'string', 'max:500'],
        ]);

        $admin = Auth::user();

        if ($admin->pushSubscriptions()->doesntExist()) {
            return response()->json(['ok' => false, 'message' => 'No push subscription found for your account. Enable push on this device first.'], 422);
        }

        // Skip quiet hours for preview — it's the admin testing on purpose
        $admin->notify(new \App\Notifications\WebPushNotification(
            $data['title'],
            $data['body'],
            '/admin/push-campaigns'
        ));

        return response()->json(['ok' => true, 'message' => 'Preview sent to your registered device(s).']);
    }
}
