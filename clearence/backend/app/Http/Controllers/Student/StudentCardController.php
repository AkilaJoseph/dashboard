<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Services\QrTokenService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;
use Illuminate\Support\Facades\Auth;

class StudentCardController extends Controller
{
    public function __construct(private QrTokenService $qr) {}

    public function show()
    {
        $student = Auth::user();

        $clearance = Clearance::where('user_id', $student->id)
            ->whereIn('status', ['pending', 'in_progress', 'approved'])
            ->latest()
            ->first();

        $token     = null;
        $expiresAt = null;
        $qrSvg     = null;

        if ($clearance) {
            $issued    = $this->qr->issue($student, $clearance);
            $token     = $issued['token'];
            $expiresAt = $issued['expires_at'];
            $qrSvg     = $this->buildSvg($token);
        }

        return view('student.my-card', compact('student', 'clearance', 'token', 'expiresAt', 'qrSvg'));
    }

    private function buildSvg(string $data): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64'    => false,
            'scale'           => 6,
            'quietzoneSize'   => 2,
        ]);
        return (new QRCode($options))->render($data);
    }
}
