@extends('layouts.app')
@section('title', 'Vessel Dashboard')
@section('header', 'Vessel IT Dashboard')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Vessels</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_vessels'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-ship text-blue-600 text-lg"></i>
            </div>
        </div>
    </div>
    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Vessel Assets</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_vessel_assets'] }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-server text-purple-600 text-lg"></i>
            </div>
        </div>
    </div>
    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Open Tickets</p>
                <p class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['vessel_open_tickets'] }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-ticket-alt text-orange-600 text-lg"></i>
            </div>
        </div>
    </div>
    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Maintenance</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['vessel_maintenance_assets'] }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-tools text-red-600 text-lg"></i>
            </div>
        </div>
    </div>
</div>

<!-- Vessel Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($vesselStats as $vessel)
    <a href="{{ route('vessels.show', $vessel['name']) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition group">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-ship text-white text-lg"></i>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-800 group-hover:text-primary-600">{{ $vessel['name'] }}</h3>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-lg p-2 text-center">
                <p class="text-lg font-bold text-gray-800">{{ $vessel['total_assets'] }}</p>
                <p class="text-xs text-gray-500">Assets</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2 text-center">
                <p class="text-lg font-bold text-orange-600">{{ $vessel['open_tickets'] }}</p>
                <p class="text-xs text-gray-500">Open Tickets</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2 text-center">
                <p class="text-lg font-bold text-green-600">{{ $vessel['assets_in_use'] }}</p>
                <p class="text-xs text-gray-500">In Use</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2 text-center">
                <p class="text-lg font-bold text-yellow-600">{{ $vessel['assets_maintenance'] }}</p>
                <p class="text-xs text-gray-500">Maintenance</p>
            </div>
        </div>
    </a>
    @empty
    <div class="col-span-full text-center py-12">
        <i class="fas fa-ship text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">No vessel data yet. Add a vessel_name to an asset or ticket.</p>
    </div>
    @endforelse
</div>
@endsection
