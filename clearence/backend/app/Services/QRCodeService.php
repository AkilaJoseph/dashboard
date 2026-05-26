<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QRCodeService
{
    public function generate(string $data): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => true,
        ]);

        return (new QRCode($options))->render($data);
    }
}