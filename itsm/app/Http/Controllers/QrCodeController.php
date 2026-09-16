<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function asset(Asset $asset)
    {
        $url = url('/assets/' . $asset->id);

        // Generate SVG QR (no Imagick needed, always works)
        $svg = QrCode::format('svg')
            ->size(250)
            ->errorCorrection('H')
            ->margin(1)
            ->generate($url);

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }
}
