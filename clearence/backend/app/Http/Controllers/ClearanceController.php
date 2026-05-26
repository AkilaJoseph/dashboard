<?php

namespace App\Http\Controllers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class ClearanceController extends Controller
{
    public function generateQR($studentId)
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => true,
        ]);

        $qrDataUri = (new QRCode($options))->render($studentId);

        return view('clearance.qr', ['qr' => $qrDataUri]);
    }
}