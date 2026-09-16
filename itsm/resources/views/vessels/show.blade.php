@extends('layouts.app')
@section('title', $vesselName)
@section('header', 'Vessel: ' . $vesselName)

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
        <p class="text-2xl font-bold text-gray-800">{{ $stats['total_assets'] }}</p>
        <p class="text-xs text-gray-500">Total Assets</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
        <p class="text-2xl font-bold text-green-600">{{ $stats['in_use'] }}</p>
        <p class="text-xs text-gray-500">In Use</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ $stats['maintenance'] }}</p>
        <p class="text-xs text-gray-500">Maintenance</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm text-center">
        <p class="text-2xl font-bold text-orange-600">{{ $stats['open_tickets'] }}</p>
        <p class="text-xs text-gray-500">Open Tickets</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Assets -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="p-5 border-b"><h3 class="text-sm font-semibold text-gray-700"><i class="fas fa-server mr-2 text-purple-500"></i>Assets on {{ $vesselName }}</h3></div>
        <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
            @forelse($assets as $asset)
            <a href="{{ route('assets.show', $asset) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->name }}</p>
                    <p class="text-xs text-gray-500">{{ $asset->asset_tag }} • {{ $asset->assetCategory->name }}</p>
                </div>
                @php $c = ['available'=>'bg-green-100 text-green-800','in_use'=>'bg-blue-100 text-blue-800','maintenance'=>'bg-yellow-100 text-yellow-800']; @endphp
                <span class="text-xs font-medium px-2 py-1 rounded-full {{ $c[$asset->status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst(str_replace('_',' ',$asset->status)) }}</span>
            </a>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">No assets</p>
            @endforelse
        </div>
    </div>

    <!-- Tickets -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="p-5 border-b"><h3 class="text-sm font-semibold text-gray-700"><i class="fas fa-ticket-alt mr-2 text-blue-500"></i>Tickets from {{ $vesselName }}</h3></div>
        <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
            @forelse($tickets as $ticket)
            <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div>
                    <p class="text-sm font-medium text-primary-600">{{ $ticket->ticket_number }}</p>
                    <p class="text-xs text-gray-600">{{ Str::limit($ticket->title, 40) }}</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: {{ $ticket->priority->color }}20; color: {{ $ticket->priority->color }}">{{ $ticket->priority->name }}</span>
            </a>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">No tickets</p>
            @endforelse
        </div>
        @if($tickets->hasPages())
        <div class="px-4 py-3 border-t">{{ $tickets->links() }}</div>
        @endif
    </div>
</div>
@endsection
