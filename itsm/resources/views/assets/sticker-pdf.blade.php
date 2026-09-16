<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A6 landscape; margin: 15mm 20mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; }
        .sticker {
            width: 90mm;
            height: 52mm;
            border: 1.5px solid #334155;
            border-radius: 2mm;
            overflow: hidden;
        }
        .header { width: 100%; height: 15mm; overflow: hidden; }
        .header-left {
            float: left; width: 35%; height: 15mm;
            padding: 2mm 3mm; text-align: center;
        }
        .header-right {
            float: left; width: 65%; height: 15mm;
            background: #1e3a5f; color: white;
            text-align: center; padding: 2.5mm 3mm;
        }
        .company-logo { height: 7mm; max-width: 20mm; }
        .company-name { font-size: 6px; font-weight: bold; color: #1e3a5f; margin-top: 0.5mm; }
        .tag-label { font-size: 5.5px; letter-spacing: 1px; opacity: 0.8; }
        .tag-number { font-size: 12px; font-weight: bold; margin-top: 0.5mm; }
        .tag-company { font-size: 5px; opacity: 0.7; margin-top: 0.5mm; }

        .body { width: 100%; height: 28mm; overflow: hidden; border-top: 0.5px solid #e2e8f0; }
        .body-left {
            float: left; width: 38%; height: 28mm;
            padding: 2mm 3mm; border-right: 0.5px solid #e2e8f0;
        }
        .body-center {
            float: left; width: 27%; height: 28mm;
            text-align: center; padding: 2.5mm 2mm;
        }
        .body-right {
            float: left; width: 35%; height: 28mm;
            padding: 3mm 3mm; background: #f8fafc;
        }
        .info-row { margin-bottom: 1.2mm; }
        .info-label { font-size: 4.5px; font-weight: bold; color: #1e3a5f; text-transform: uppercase; }
        .info-value { font-size: 6.5px; color: #1f2937; margin-top: 0.2mm; }
        .qr-code { width: 18mm; height: 18mm; }
        .scan-title { font-size: 5.5px; font-weight: bold; color: #1e3a5f; }
        .scan-desc { font-size: 4.5px; color: #6b7280; line-height: 1.4; margin-top: 0.3mm; }

        .footer { width: 100%; height: 7mm; overflow: hidden; }
        .footer-left {
            float: left; width: 65%; height: 7mm;
            background: #f59e0b; color: white;
            font-size: 5px; font-weight: bold; padding: 2mm 3mm;
        }
        .footer-right {
            float: left; width: 35%; height: 7mm;
            background: #1e3a5f; color: white;
            font-size: 5.5px; font-weight: bold;
            text-align: center; padding: 2mm 2mm;
        }
        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>
    <div class="sticker">
        <div class="header clearfix">
            <div class="header-left">
                @if($asset->company && $asset->company->logo)
                <img src="{{ public_path('storage/' . $asset->company->logo) }}" class="company-logo">
                @endif
                <div class="company-name">{{ $asset->company->name ?? 'AMARIN' }}</div>
            </div>
            <div class="header-right">
                <div class="tag-label">ASSET TAG</div>
                <div class="tag-number">{{ $asset->asset_tag }}</div>
                <div class="tag-company">{{ $asset->company->full_name ?? 'PT Amarin Ship Management' }}</div>
            </div>
        </div>
        <div class="body clearfix">
            <div class="body-left">
                <div class="info-row"><div class="info-label">Asset Name</div><div class="info-value">{{ Str::limit($asset->name, 20) }}</div></div>
                <div class="info-row"><div class="info-label">Category</div><div class="info-value">{{ $asset->assetCategory->name ?? '-' }}</div></div>
                <div class="info-row"><div class="info-label">Serial Number</div><div class="info-value">{{ Str::limit($asset->serial_number ?? '-', 16) }}</div></div>
                <div class="info-row"><div class="info-label">Acquisition Date</div><div class="info-value">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</div></div>
                <div class="info-row"><div class="info-label">Location</div><div class="info-value">{{ Str::limit($asset->location ?? $asset->vessel_name ?? '-', 16) }}</div></div>
                <div class="info-row"><div class="info-label">Responsible</div><div class="info-value">{{ Str::limit($asset->assignedUser->name ?? '-', 16) }}</div></div>
            </div>
            <div class="body-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&ecc=H&data={{ urlencode(url('/assets/' . $asset->id)) }}" class="qr-code">
            </div>
            <div class="body-right">
                <div style="margin-bottom: 3mm;"><div class="scan-title">SCAN ME</div><div class="scan-desc">for asset info, history, maintenance & more</div></div>
                <div><div class="scan-title">PROTECT THIS ASSET</div><div class="scan-desc">Report if lost or damaged</div></div>
            </div>
        </div>
        <div class="footer clearfix">
            <div class="footer-left">PROPERTY OF {{ strtoupper(Str::limit($asset->company->full_name ?? 'PT AMARIN SHIP MANAGEMENT', 35)) }}</div>
            <div class="footer-right">DO NOT REMOVE</div>
        </div>
    </div>
</body>
</html>
