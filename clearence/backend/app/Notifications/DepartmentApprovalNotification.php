<?php

namespace App\Notifications;

use App\Models\ClearanceApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DepartmentApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ClearanceApproval $approval) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        $prefs       = $notifiable->notification_preferences ?? [];
        $pushEnabled = $prefs['push'] ?? true;

        if ($pushEnabled && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = 'webpush';
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        $dept   = $this->approval->department->name;
        $status = $this->approval->status;
        $type   = ucfirst($this->approval->clearance->clearance_type);

        return [
            'clearance_id'   => $this->approval->clearance_id,
            'department'     => $dept,
            'status'         => $status,
            'clearance_type' => $type,
            'message'        => $status === 'approved'
                ? "{$dept} has approved your {$type} clearance request."
                : "{$dept} has rejected your {$type} clearance request.",
            'icon'           => $status === 'approved' ? 'check' : 'x',
        ];
    }

    public function toWebPush(object $notifiable): array
    {
        $dept    = $this->approval->department->name;
        $status  = $this->approval->status;
        $type    = ucfirst($this->approval->clearance->clearance_type);

        // require_interaction only when the entire clearance reaches a terminal state,
        // so the student is sure to see the final outcome without the notification
        // auto-dismissing.
        $isFinal = in_array(
            $this->approval->clearance->status ?? 'pending',
            ['approved', 'rejected']
        );

        return [
            'title'               => $status === 'approved' ? 'Clearance Approved' : 'Clearance Update',
            'body'                => $status === 'approved'
                ? "{$dept} has approved your {$type} clearance request."
                : "{$dept} has rejected your {$type} clearance request.",
            'url'                 => '/student/clearances/' . $this->approval->clearance_id,
            'clearance_id'        => $this->approval->clearance_id,
            'status'              => $status,
            'require_interaction' => $isFinal,
            'icon'                => '/images/pwa-icons/icon-192.png',
            'badge'               => '/images/pwa-icons/icon-96.png',
        ];
    }
}
