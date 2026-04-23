<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    /**
     * Owner student, officer whose department is routed on this clearance, or admin.
     * Spec §4.1.3: attachments are private; served only to authorised parties.
     */
    public function download(User $user, Attachment $attachment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $clearance = $attachment->clearance;

        if ($clearance->user_id === $user->id) {
            return true;
        }

        if ($user->isOfficer()) {
            return $clearance->approvals()
                ->where('department_id', $user->department_id)
                ->exists();
        }

        return false;
    }
}
