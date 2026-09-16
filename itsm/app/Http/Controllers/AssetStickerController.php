<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AssetStickerController extends Controller
{
    public function preview(Asset $asset)
    {
        $asset->load(['assetCategory', 'assignedUser', 'company']);
        return view('assets.sticker-preview', compact('asset'));
    }

    public function print(Asset $asset)
    {
        $asset->load(['assetCategory', 'assignedUser', 'company']);

        $pdf = Pdf::loadView('assets.sticker-pdf', compact('asset'));
        $pdf->setPaper('A6', 'landscape');
        $pdf->setOption('dpi', 150);

        return $pdf->stream("sticker-{$asset->asset_tag}.pdf");
    }

    public function printBatch(Request $request)
    {
        $request->validate(['assets' => 'required|array']);
        $assets = Asset::with(['assetCategory', 'assignedUser', 'company'])
            ->whereIn('id', $request->assets)
            ->get();

        $pdf = Pdf::loadView('assets.sticker-batch-pdf', compact('assets'));
        // A4 portrait — 4 stickers per page
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('asset-stickers-batch.pdf');
    }
}
