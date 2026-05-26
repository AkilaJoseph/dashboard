<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Services\QrTokenService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StudentQrController extends Controller
{
    public function __construct(private QrTokenService $qr) {}

    public function token(): JsonResponse
    {
        $student = Auth::user();

        if ($student->role !== 'student') {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $clearance = Clearance::where('user_id', $student->id)
            ->whereIn('status', ['pending', 'in_progress', 'approved'])
            ->latest()
            ->first();

        if (! $clearance) {
            return response()->json(['error' => 'No active clearance request found.'], 404);
        }

        $issued = $this->qr->issue($student, $clearance);

        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64'    => false,
            'scale'           => 6,
            'quietzoneSize'   => 2,
        ]);
        $qrSvg = (new QRCode($options))->render($issued['token']);

        return response()->json([
            'token'        => $issued['token'],
            'qr_svg'       => $qrSvg,
            'expires_at'   => $issued['expires_at'],
            'clearance_id' => $clearance->id,
        ]);
    }
}
