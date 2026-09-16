@extends('layouts.app')
@section('title', 'Asset Sticker - ' . $asset->asset_tag)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Asset Sticker Preview</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $asset->asset_tag }} - {{ $asset->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('assets.sticker.print', $asset) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-semibold hover:bg-brand-600 transition">
                <i class="fas fa-print"></i> Print Sticker
            </a>
            <a href="{{ route('assets.show', $asset) }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">← Back</a>
        </div>
    </div>

    <!-- Sticker Preview -->
    <div class="flex justify-center">
        <div class="w-[420px] h-[250px] rounded-xl overflow-hidden shadow-xl border border-gray-200" style="background: white;">
            <!-- Header -->
            <div class="flex h-[75px]">
                <div class="w-[35%] flex flex-col items-center justify-center px-3 bg-white border-r border-gray-100">
                    @if($asset->company && $asset->company->logo)
                    <img src="{{ asset('storage/' . $asset->company->logo) }}" class="h-10 max-w-[80px] object-contain">
                    @else
                    <div class="w-10 h-10 bg-blue-900 rounded flex items-center justify-center">
                        <i class="fas fa-building text-white text-sm"></i>
                    </div>
                    @endif
                    <p class="text-[10px] font-bold text-[#1e3a5f] mt-1 text-center">{{ $asset->company->name ?? 'PT AMARIN' }}</p>
                </div>
                <div class="w-[65%] bg-[#1e3a5f] flex flex-col items-center justify-center text-white px-4">
                    <p class="text-[9px] tracking-wider opacity-80">ASSET TAG</p>
                    <p class="text-xl font-black tracking-wide mt-0.5">{{ $asset->asset_tag }}</p>
                    <p class="text-[8px] opacity-60 mt-0.5">{{ $asset->company->full_name ?? 'PT Amarin Ship Management' }}</p>
                </div>
            </div>

            <!-- Body -->
            <div class="flex h-[130px]">
                <!-- Info Left -->
                <div class="w-[37%] p-3 space-y-1.5 border-r border-gray-100">
                    <div>
                        <p class="text-[7px] font-bold text-[#1e3a5f] uppercase tracking-wider">Asset Name</p>
                        <p class="text-[10px] text-gray-800">{{ Str::limit($asset->name, 22) }}</p>
                    </div>
                    <div>
                        <p class="text-[7px] font-bold text-[#1e3a5f] uppercase tracking-wider">Category</p>
                        <p class="text-[10px] text-gray-800">{{ $asset->assetCategory->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[7px] font-bold text-[#1e3a5f] uppercase tracking-wider">Serial Number</p>
                        <p class="text-[10px] text-gray-800">{{ Str::limit($asset->serial_number ?? '-', 18) }}</p>
                    </div>
                    <div>
                        <p class="text-[7px] font-bold text-[#1e3a5f] uppercase tracking-wider">Location</p>
                        <p class="text-[10px] text-gray-800">{{ Str::limit($asset->location ?? $asset->vessel_name ?? '-', 20) }}</p>
                    </div>
                    <div>
                        <p class="text-[7px] font-bold text-[#1e3a5f] uppercase tracking-wider">Responsible</p>
                        <p class="text-[10px] text-gray-800">{{ Str::limit($asset->assignedUser->name ?? '-', 18) }}</p>
                    </div>
                </div>
                <!-- QR Center with Logo -->
                <div class="w-[28%] flex items-center justify-center p-2">
                    <div class="relative">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=H&data={{ urlencode(url('/assets/' . $asset->id)) }}" class="w-[90px] h-[90px]">
                        <!-- Logo overlay - small enough (20%) to not break QR scan with ECC=H (30% tolerance) -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-[22px] h-[22px] bg-white rounded p-[2px] shadow-sm">
                                @if($asset->company && $asset->company->logo)
                                <img src="{{ asset('storage/' . $asset->company->logo) }}" class="w-full h-full object-contain">
                                @else
                                <div class="w-full h-full bg-[#1e3a5f] rounded-sm flex items-center justify-center">
                                    <i class="fas fa-ship text-white" style="font-size:8px"></i>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Right Info -->
                <div class="w-[35%] bg-gray-50 p-3 flex flex-col justify-center space-y-3">
                    <div class="text-center">
                        <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-1">
                            <i class="fas fa-qrcode text-blue-600 text-xs"></i>
                        </div>
                        <p class="text-[9px] font-bold text-[#1e3a5f]">SCAN ME</p>
                        <p class="text-[7px] text-gray-500 leading-tight">for asset info, history, maintenance & more</p>
                    </div>
                    <div class="text-center">
                        <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-1">
                            <i class="fas fa-shield-alt text-green-600 text-xs"></i>
                        </div>
                        <p class="text-[9px] font-bold text-[#1e3a5f]">PROTECT</p>
                        <p class="text-[7px] text-gray-500 leading-tight">Report if lost or damaged</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex h-[35px]">
                <div class="w-[65%] bg-amber-500 flex items-center px-3">
                    <span class="text-white text-[8px] font-bold">⚠ THIS ASSET IS THE PROPERTY OF {{ strtoupper($asset->company->full_name ?? 'PT AMARIN SHIP MANAGEMENT') }}</span>
                </div>
                <div class="w-[35%] bg-[#1e3a5f] flex items-center justify-center">
                    <span class="text-white text-[8px] font-bold">DO NOT REMOVE THIS STICKER</span>
                </div>
            </div>
        </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">Ukuran cetak: 100mm × 60mm. QR code mengarah ke detail asset di ITSM Portal.</p>
</div>
@endsection
