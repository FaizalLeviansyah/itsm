<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 8px; }
        .page { page-break-after: always; padding: 5mm; }
        .page:last-child { page-break-after: avoid; }
        .grid { display: block; }
        .sticker {
            width: 95mm;
            height: 56mm;
            border: 1px solid #ccc;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 4mm;
            display: inline-block;
            vertical-align: top;
        }
        .header { display: table; width: 100%; height: 16mm; }
        .header-left { display: table-cell; width: 35%; vertical-align: middle; padding: 2mm; background: #fff; }
        .header-right { display: table-cell; width: 65%; vertical-align: middle; padding: 2mm; background: #1e3a5f; color: white; text-align: center; }
        .company-name { font-size: 6px; font-weight: bold; color: #1e3a5f; }
        .company-logo { height: 8mm; max-width: 20mm; }
        .asset-tag-label { font-size: 6px; opacity: 0.8; }
        .asset-tag-number { font-size: 12px; font-weight: bold; }
        .company-under-tag { font-size: 5px; opacity: 0.7; margin-top: 0.5mm; }
        .body { display: table; width: 100%; height: 28mm; }
        .body-left { display: table-cell; width: 40%; vertical-align: top; padding: 2mm; border-right: 1px solid #e5e7eb; }
        .body-center { display: table-cell; width: 25%; vertical-align: middle; text-align: center; padding: 1mm; }
        .body-right { display: table-cell; width: 35%; vertical-align: middle; padding: 2mm; background: #f8fafc; }
        .info-row { margin-bottom: 1.2mm; }
        .info-label { font-size: 5px; font-weight: bold; color: #1e3a5f; text-transform: uppercase; }
        .info-value { font-size: 6.5px; color: #333; }
        .qr-code { width: 18mm; height: 18mm; }
        .scan-text { font-size: 6px; font-weight: bold; color: #1e3a5f; margin-bottom: 0.5mm; }
        .scan-desc { font-size: 5px; color: #666; line-height: 1.3; }
        .footer { display: table; width: 100%; height: 7mm; }
        .footer-left { display: table-cell; width: 65%; vertical-align: middle; padding: 1mm 2mm; background: #f59e0b; color: white; font-size: 5.5px; font-weight: bold; }
        .footer-right { display: table-cell; width: 35%; vertical-align: middle; padding: 1mm; background: #1e3a5f; color: white; font-size: 5.5px; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
@foreach($assets->chunk(4) as $chunk)
<div class="page">
    @foreach($chunk as $asset)
    <div class="sticker">
        <div class="header">
            <div class="header-left">
                @if($asset->company && $asset->company->logo)
                <img src="{{ public_path('storage/' . $asset->company->logo) }}" class="company-logo">
                @endif
                <div class="company-name">{{ $asset->company->name ?? 'AMARIN' }}</div>
            </div>
            <div class="header-right">
                <div class="asset-tag-label">ASSET TAG</div>
                <div class="asset-tag-number">{{ $asset->asset_tag }}</div>
                <div class="company-under-tag">{{ $asset->company->full_name ?? 'PT Amarin Ship Management' }}</div>
            </div>
        </div>
        <div class="body">
            <div class="body-left">
                <div class="info-row"><div class="info-label">Asset Name</div><div class="info-value">{{ Str::limit($asset->name, 22) }}</div></div>
                <div class="info-row"><div class="info-label">Category</div><div class="info-value">{{ $asset->assetCategory->name ?? '-' }}</div></div>
                <div class="info-row"><div class="info-label">Serial Number</div><div class="info-value">{{ Str::limit($asset->serial_number ?? '-', 18) }}</div></div>
                <div class="info-row"><div class="info-label">Acquisition Date</div><div class="info-value">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</div></div>
                <div class="info-row"><div class="info-label">Location</div><div class="info-value">{{ Str::limit($asset->location ?? $asset->vessel_name ?? '-', 20) }}</div></div>
                <div class="info-row"><div class="info-label">Responsible</div><div class="info-value">{{ Str::limit($asset->assignedUser->name ?? '-', 18) }}</div></div>
            </div>
            <div class="body-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&ecc=H&data={{ urlencode(url('/assets/' . $asset->id)) }}" class="qr-code">
            </div>
            <div class="body-right">
                <div style="margin-bottom:2mm;"><div class="scan-text">SCAN ME</div><div class="scan-desc">for asset info & history</div></div>
                <div><div class="scan-text">PROTECT</div><div class="scan-desc">Report if lost or damaged</div></div>
            </div>
        </div>
        <div class="footer">
            <div class="footer-left">⚠ PROPERTY OF {{ strtoupper(Str::limit($asset->company->full_name ?? 'PT AMARIN SHIP MANAGEMENT', 35)) }}</div>
            <div class="footer-right">DO NOT REMOVE</div>
        </div>
    </div>
    @endforeach
</div>
@endforeach
</body>
</html>
