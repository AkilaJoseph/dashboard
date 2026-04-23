<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Stream an attachment to the authenticated viewer.
     *
     * Authorised viewers:
     *   - The student who owns the clearance.
     *   - Any officer whose department is routed on the clearance.
     *   - Any admin.
     */
    public function download(Request $request, Attachment $attachment): StreamedResponse
    {
        $clearance = $attachment->clearance;
        $user      = $request->user();

        $authorised =
            $user->isAdmin()
            || $clearance->user_id === $user->id
            || ($user->isOfficer() && $clearance->approvals()
                ->where('department_id', $user->department_id)
                ->exists());

        if (! $authorised) {
            abort(403);
        }

        if (! Storage::disk('attachments')->exists($attachment->stored_path)) {
            abort(404, 'File not found on server.');
        }

        return Storage::disk('attachments')->response(
            $attachment->stored_path,
            $attachment->file_name,
            ['Content-Type' => $attachment->mime_type]
        );
    }
}
