@extends('layouts.app')
@section('title', 'Scan Asset - ' . $asset->asset_tag)

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 p-6 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-qrcode text-green-600 text-2xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-900">Asset Found!</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $asset->asset_tag }}</p>

        <div class="bg-gray-50 rounded-lg p-4 mt-4 text-left space-y-2">
            <div class="flex justify-between text-sm"><span class="text-gray-500">Name</span><span class="font-medium text-gray-800">{{ $asset->name }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Category</span><span class="text-gray-800">{{ $asset->assetCategory->name ?? '-' }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Location</span><span class="text-gray-800">{{ $asset->location ?? '-' }}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Serial</span><span class="text-gray-800">{{ $asset->serial_number ?? '-' }}</span></div>
        </div>

        <form action="{{ route('audits.scan', $audit) }}" method="POST" class="mt-6 space-y-3 text-left">
            @csrf
            <input type="hidden" name="asset_id" value="{{ $asset->id }}">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Asset Condition *</label>
                <select name="condition" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                    <option value="good">✅ Good - Functioning well</option>
                    <option value="fair">🔵 Fair - Slightly worn but functional</option>
                    <option value="poor">🟡 Poor - Needs repair</option>
                    <option value="damaged">🔴 Damaged - Broken</option>
                    <option value="missing">❌ Missing - Not found</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm" placeholder="Additional notes..."></textarea>
            </div>
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg text-sm font-semibold transition">
                <i class="fas fa-check mr-2"></i> Confirm Scan
            </button>
        </form>

        <a href="{{ route('audits.show', $audit) }}" class="inline-block mt-3 text-sm text-brand-600 hover:text-brand-700">← Back to Audit</a>
    </div>
</div>
@endsection
