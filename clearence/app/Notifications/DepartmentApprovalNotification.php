<?php

namespace App\Notifications;

use App\Models\ClearanceApproval;
use Illuminate\Notifications\Notification;

class DepartmentApprovalNotification extends Notification
{
    public function __construct(public ClearanceApproval $approval) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
}
