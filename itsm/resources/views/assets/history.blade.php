@extends('layouts.app')
@section('title', 'History - ' . $asset->name)

@section('content')
<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('assets.show', $asset) }}" class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Asset History</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $asset->asset_tag }}</span>
                {{ $asset->name }}
            </p>
        </div>
    </div>
    <a href="{{ route('assets.show', $asset) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
        <i class="fas fa-eye text-xs"></i> View Detail
    </a>
</div>

@php
$actionConfig = [
    'created'        => ['color' => 'bg-green-500',  'light' => 'bg-green-50 border-green-200',  'icon' => 'fa-plus',          'badge' => 'bg-green-100 text-green-800',  'label' => 'Created'],
    'assigned'       => ['color' => 'bg-blue-500',   'light' => 'bg-blue-50 border-blue-200',    'icon' => 'fa-user',          'badge' => 'bg-blue-100 text-blue-800',    'label' => 'Assigned'],
    'moved'          => ['color' => 'bg-orange-500', 'light' => 'bg-orange-50 border-orange-200','icon' => 'fa-map-marker-alt','badge' => 'bg-orange-100 text-orange-800','label' => 'Moved'],
    'status_changed' => ['color' => 'bg-yellow-500', 'light' => 'bg-yellow-50 border-yellow-200','icon' => 'fa-exchange-alt',  'badge' => 'bg-yellow-100 text-yellow-800','label' => 'Status Changed'],
    'updated'        => ['color' => 'bg-purple-500', 'light' => 'bg-purple-50 border-purple-200','icon' => 'fa-edit',          'badge' => 'bg-purple-100 text-purple-800','label' => 'Updated'],
    'deleted'        => ['color' => 'bg-red-500',    'light' => 'bg-red-50 border-red-200',      'icon' => 'fa-trash',         'badge' => 'bg-red-100 text-red-800',      'label' => 'Deleted'],
];
$fieldLabels = [
    'name' => 'Name', 'status' => 'Status', 'assigned_to' => 'User',
    'location' => 'Location', 'vessel_name' => 'Vessel', 'company_id' => 'Company',
    'manufacturer' => 'Manufacturer', 'model' => 'Model', 'serial_number' => 'Serial Number',
    'ip_address' => 'IP Address', 'mac_address' => 'MAC Address',
    'asset_category_id' => 'Category', 'description' => 'Description',
    'purchase_date' => 'Purchase Date', 'purchase_cost' => 'Purchase Cost',
    'warranty_expiry' => 'Warranty', 'notes' => 'Notes',
];
@endphp

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <!-- Stats bar -->
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap gap-4">
        <div class="text-sm text-gray-600">
            <span class="font-semibold text-gray-900">{{ $histories->total() }}</span> total history entries
        </div>
        <div class="flex flex-wrap gap-2 ml-auto">
            @foreach(['assigned'=>'Assigned','moved'=>'Moved','status_changed'=>'Status Changed'] as $act => $lbl)
            @php $cnt = $histories->getCollection()->where('action',$act)->count(); @endphp
            @if($cnt > 0)
            <span class="text-xs px-2 py-0.5 rounded-full {{ $actionConfig[$act]['badge'] }}">{{ $lbl }}: {{ $cnt }}</span>
            @endif
            @endforeach
        </div>
    </div>

    <!-- Timeline -->
    <div class="p-6">
        @forelse($histories as $history)
        @php $cfg = $actionConfig[$history->action] ?? ['color'=>'bg-gray-500','light'=>'bg-gray-50 border-gray-200','icon'=>'fa-circle','badge'=>'bg-gray-100 text-gray-700','label'=>ucfirst($history->action)]; @endphp
        <div class="flex gap-4 mb-6 last:mb-0">
            <!-- Icon column -->
            <div class="flex flex-col items-center">
                <div class="w-9 h-9 rounded-full {{ $cfg['color'] }} flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fas {{ $cfg['icon'] }} text-white text-sm"></i>
                </div>
                @if(!$loop->last)
                <div class="w-0.5 bg-gray-200 flex-1 mt-2"></div>
                @endif
            </div>

            <!-- Content -->
            <div class="flex-1 pb-6 last:pb-0">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-gray-900">{{ $history->user->name }}</span>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $cfg['badge'] }}">
                            {{ $cfg['label'] }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-400 flex items-center gap-1">
                        <i class="fas fa-clock"></i>
                        <span title="{{ $history->created_at->format('d M Y, H:i:s') }}">
                            {{ $history->created_at->diffForHumans() }}
                        </span>
                        <span class="text-gray-300 mx-1">•</span>
                        <span>{{ $history->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>

                <p class="text-sm text-gray-700 mb-2">{{ $history->description }}</p>

                @if($history->changes && count($history->changes) > 0)
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left px-3 py-2 font-medium text-gray-500 w-1/4">Field</th>
                                <th class="text-left px-3 py-2 font-medium text-gray-500 w-[37.5%]">Before</th>
                                <th class="text-left px-3 py-2 font-medium text-gray-500 w-[37.5%]">After</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history->changes as $field => $change)
                            <tr class="border-b border-gray-100 last:border-0">
                                <td class="px-3 py-2 font-medium text-gray-600">
                                    {{ $fieldLabels[$field] ?? ucfirst(str_replace('_',' ',$field)) }}
                                </td>
                                <td class="px-3 py-2">
                                    @if($change['old'])
                                    <span class="inline-flex items-center gap-1 text-red-600">
                                        <span class="line-through">{{ $change['old'] }}</span>
                                    </span>
                                    @else
                                    <span class="text-gray-400 italic">Empty</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if($change['new'])
                                    <span class="text-green-700 font-medium">{{ $change['new'] }}</span>
                                    @else
                                    <span class="text-gray-400 italic">Empty</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <i class="fas fa-history text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-500 font-medium">No history yet</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($histories->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-sm text-gray-500">
            {{ $histories->firstItem() }}–{{ $histories->lastItem() }} of {{ $histories->total() }} entries
        </p>
        <div>{{ $histories->links() }}</div>
    </div>
    @endif
</div>
@endsection
