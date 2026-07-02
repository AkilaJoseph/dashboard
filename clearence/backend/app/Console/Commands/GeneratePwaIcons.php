<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePwaIcons extends Command
{
    protected $signature   = 'pwa:icons';
    protected $description = 'Generate PNG icons for the PWA manifest from the embedded SVG template';

    // Sizes required by the manifest.json
    private array $sizes = [72, 96, 128, 144, 152, 192, 384, 512];

    public function handle(): int
    {
        if (!extension_loaded('gd')) {
            $this->error('GD extension is required. Enable it in php.ini: extension=gd');
            return 1;
        }

        $dir = public_path('images/pwa-icons');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($this->sizes as $size) {
            $this->generateIcon($dir, $size);
            $this->line("  ✓  icon-{$size}.png");
        }

        $this->info('PWA icons generated successfully in public/images/pwa-icons/');
        return 0;
    }

    private function generateIcon(string $dir, int $size): void
    {
        $img = imagecreatetruecolor($size, $size);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);

        // Background circle — MUST green
        $bg = imagecolorallocate($img, 6, 78, 59);   // #064e3b
        imagefilledellipse($img, (int)($size/2), (int)($size/2), $size, $size, $bg);

        // Inner circle highlight
        $hi = imagecolorallocate($img, 5, 150, 105);  // #059669
        $r2 = (int)($size * 0.4);
        imagefilledellipse($img, (int)($size/2), (int)($size/2), $r2, $r2, $hi);

        // Text "M" centred — fallback since GD built-in fonts are limited
        $white = imagecolorallocate($img, 255, 255, 255);
        $font  = 5; // largest built-in font (9×15 px)
        $fw    = imagefontwidth($font);
        $fh    = imagefontheight($font);
        $scale = max(1, (int)($size / 48));
        $x     = (int)(($size - $fw * $scale) / 2);
        $y     = (int)(($size - $fh * $scale) / 2);
        imagestring($img, $font, $x, $y, 'M', $white);

        imagepng($img, $dir . "/icon-{$size}.png");
        imagedestroy($img);
    }
}
