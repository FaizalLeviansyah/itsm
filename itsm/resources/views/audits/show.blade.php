@extends('layouts.app')
@section('title', $audit->audit_number)

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <span class="text-sm text-gray-500">{{ $audit->audit_number }}</span>
            @php $asc = ['planned'=>'bg-gray-50 text-gray-600 border-gray-200','in_progress'=>'bg-blue-50 text-blue-700 border-blue-200','completed'=>'bg-green-50 text-green-700 border-green-200','cancelled'=>'bg-red-50 text-red-700 border-red-200']; @endphp
            <span class="inline-flex px-2.5 py-0.5 rounded text-xs font-medium border {{ $asc[$audit->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$audit->status)) }}</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900">{{ $audit->title }}</h1>
    </div>
    <div class="flex gap-2">
        @if($audit->status === 'planned')
        <form action="{{ route('audits.start', $audit) }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-semibold hover:bg-brand-600 transition">
                <i class="fas fa-play text-xs"></i> Start Audit
            </button>
        </form>
        @elseif($audit->status === 'in_progress')
        <form action="{{ route('audits.complete', $audit) }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                <i class="fas fa-check text-xs"></i> Complete Audit
            </button>
        </form>
        @endif
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
        <p class="text-xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide">Total</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
        <p class="text-xl font-bold text-brand-600">{{ $stats['scanned'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide">Scanned</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
        <p class="text-xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide">Pending</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
        <p class="text-xl font-bold text-green-600">{{ $stats['good'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide">Good</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
        <p class="text-xl font-bold text-orange-600">{{ $stats['damaged'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide">Damaged</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
        <p class="text-xl font-bold text-red-600">{{ $stats['missing'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide">Missing</p>
    </div>
</div>

<!-- Progress Bar -->
<div class="bg-white rounded-xl border border-gray-100 p-5 mb-6">
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700">Progress</span>
        <span class="text-sm font-bold text-brand-600">{{ $audit->progress_percent }}%</span>
    </div>
    <div class="w-full bg-gray-100 rounded-full h-3">
        <div class="h-3 rounded-full bg-brand-500 transition-all" style="width:{{ $audit->progress_percent }}%"></div>
    </div>
</div>

<!-- Asset Checklist -->
<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">Asset Checklist</h3>
        @if($audit->status === 'in_progress')
        <span class="text-xs text-gray-500">Scan QR or update manually below</span>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Asset</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Category</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase px-6 py-3">Scan</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase px-6 py-3">Condition</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Notes</th>
                    @if($audit->status === 'in_progress')
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase px-6 py-3">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($audit->items as $item)
                <tr class="border-b border-gray-50 {{ $item->scan_status === 'pending' ? '' : ($item->condition === 'good' ? 'bg-green-50/30' : ($item->condition === 'missing' ? 'bg-red-50/30' : 'bg-amber-50/30')) }}">
                    <td class="px-6 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $item->asset->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $item->asset->asset_tag }}</p>
                    </td>
                    <td class="px-6 py-3 text-xs text-gray-600">{{ $item->asset->assetCategory->name ?? '-' }}</td>
                    <td class="px-6 py-3 text-center">
                        @if($item->scan_status === 'scanned')
                        <span class="text-green-600"><i class="fas fa-qrcode"></i></span>
                        @elseif($item->scan_status === 'manual')
                        <span class="text-blue-600"><i class="fas fa-hand-pointer"></i></span>
                        @else
                        <span class="text-gray-300"><i class="fas fa-clock"></i></span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-center">
                        @php $cc = ['good'=>'bg-green-100 text-green-700','fair'=>'bg-blue-100 text-blue-700','poor'=>'bg-orange-100 text-orange-700','damaged'=>'bg-red-100 text-red-700','missing'=>'bg-red-200 text-red-800']; @endphp
                        @if($item->scan_status !== 'pending')
                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-medium {{ $cc[$item->condition] ?? '' }}">{{ ucfirst($item->condition) }}</span>
                        @else
                        <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-xs text-gray-500">{{ Str::limit($item->notes, 30) }}</td>
                    @if($audit->status === 'in_progress')
                    <td class="px-6 py-3 text-center">
                        <form action="{{ route('audits.updateItem', [$audit, $item]) }}" method="POST" class="flex items-center gap-1 justify-center">
                            @csrf
                            <select name="condition" class="no-select2 text-[10px] border border-gray-200 rounded px-1.5 py-1 bg-white">
                                <option value="good" {{ $item->condition == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="fair" {{ $item->condition == 'fair' ? 'selected' : '' }}>Fair</option>
                                <option value="poor" {{ $item->condition == 'poor' ? 'selected' : '' }}>Poor</option>
                                <option value="damaged" {{ $item->condition == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="missing" {{ $item->condition == 'missing' ? 'selected' : '' }}>Missing</option>
                            </select>
                            <input type="hidden" name="notes" value="{{ $item->notes }}">
                            <button type="submit" class="w-6 h-6 bg-brand-500 text-white rounded flex items-center justify-center hover:bg-brand-600" title="Update">
                                <i class="fas fa-check text-[8px]"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
