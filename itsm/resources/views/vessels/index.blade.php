@extends('layouts.app')
@section('title', 'Location Dashboard')
@section('header', 'IT Location Dashboard')

@section('content')

<!-- Tab Buttons -->
<div class="flex items-center space-x-3 mb-6 bg-white p-2 rounded-xl border border-gray-100 shadow-sm w-fit">
    <button id="btn-all" onclick="switchTab('all')" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all flex items-center gap-2 bg-gray-800 text-white shadow-md">
        <i class="fas fa-globe"></i>
        <span>All</span>
        <span class="bg-white/20 text-white px-2 py-0.5 rounded-full text-xs font-bold ml-1">{{ $allTotals['locations'] }}</span>
    </button>

    <button id="btn-vessels" onclick="switchTab('vessels')" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all flex items-center gap-2 bg-transparent text-gray-600 hover:bg-gray-100">
        <i class="fas fa-ship"></i>
        <span>Vessels</span>
        <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-xs font-bold ml-1">{{ $vesselTotals['locations'] }}</span>
    </button>

    <button id="btn-offices" onclick="switchTab('offices')" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all flex items-center gap-2 bg-transparent text-gray-600 hover:bg-gray-100">
        <i class="fas fa-building"></i>
        <span>Office / Shore</span>
        <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-xs font-bold ml-1">{{ $officeTotals['locations'] }}</span>
    </button>
</div>

<!-- ================= ALL VIEW (Default) ================= -->
<div id="view-all">
    <!-- Stats All -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Locations</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $allTotals['locations'] }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-globe text-gray-700 text-lg"></i>
                </div>
            </div>
        </div>
        <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Assets</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $allTotals['assets'] }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-server text-indigo-600 text-lg"></i>
                </div>
            </div>
        </div>
        <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Open Tickets</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ $allTotals['open_tickets'] }}</p>
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
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $allTotals['maintenance'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-tools text-red-600 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- All Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($allStats as $item)
        <a href="{{ route('vessels.show', $item['name']) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition group block">
            <div class="flex items-center space-x-3 mb-4">
                @if($item['type'] === 'vessel')
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-ship text-white text-lg"></i>
                    </div>
                @else
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-building text-white text-lg"></i>
                    </div>
                @endif
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 group-hover:text-blue-600">{{ $item['name'] }}</h3>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-gray-800">{{ $item['total_assets'] }}</p>
                    <p class="text-xs text-gray-500">Assets</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-orange-600">{{ $item['open_tickets'] }}</p>
                    <p class="text-xs text-gray-500">Open Tickets</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-green-600">{{ $item['assets_in_use'] }}</p>
                    <p class="text-xs text-gray-500">In Use</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-red-600">{{ $item['assets_maintenance'] }}</p>
                    <p class="text-xs text-gray-500">Maintenance</p>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-12">
            <i class="fas fa-globe text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No data available yet.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- ================= VESSEL VIEW ================= -->
<div id="view-vessels" style="display: none;">
    <!-- Stats Vessel -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Vessels</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $vesselTotals['locations'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $vesselTotals['assets'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-server text-blue-600 text-lg"></i>
                </div>
            </div>
        </div>
        <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Open Tickets</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ $vesselTotals['open_tickets'] }}</p>
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
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $vesselTotals['maintenance'] }}</p>
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
        <a href="{{ route('vessels.show', $vessel['name']) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition group block">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-ship text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 group-hover:text-blue-600">{{ $vessel['name'] }}</h3>
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
                    <p class="text-lg font-bold text-red-600">{{ $vessel['assets_maintenance'] }}</p>
                    <p class="text-xs text-gray-500">Maintenance</p>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-12">
            <i class="fas fa-ship text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No vessel data yet.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- ================= OFFICE VIEW ================= -->
<div id="view-offices" style="display: none;">
    <!-- Stats Office -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Offices</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $officeTotals['locations'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building text-purple-600 text-lg"></i>
                </div>
            </div>
        </div>
        <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Office Assets</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $officeTotals['assets'] }}</p>
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
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ $officeTotals['open_tickets'] }}</p>
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
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $officeTotals['maintenance'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-tools text-red-600 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Office Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($officeStats as $office)
        <a href="{{ route('vessels.show', $office['name']) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition group block">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 group-hover:text-purple-600">{{ $office['name'] }}</h3>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-gray-800">{{ $office['total_assets'] }}</p>
                    <p class="text-xs text-gray-500">Assets</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-orange-600">{{ $office['open_tickets'] }}</p>
                    <p class="text-xs text-gray-500">Open Tickets</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-green-600">{{ $office['assets_in_use'] }}</p>
                    <p class="text-xs text-gray-500">In Use</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <p class="text-lg font-bold text-red-600">{{ $office['assets_maintenance'] }}</p>
                    <p class="text-xs text-gray-500">Maintenance</p>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-12">
            <i class="fas fa-building text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No office/shore data yet.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Script untuk 3 Tabs (All, Vessels, Offices) -->
<script>
    function switchTab(tab) {
        const views = ['all', 'vessels', 'offices'];
        
        // Base classes untuk tombol
        const activeClass = 'px-5 py-2.5 rounded-lg text-sm font-semibold transition-all flex items-center gap-2 text-white shadow-md'.split(' ');
        const inactiveClass = 'px-5 py-2.5 rounded-lg text-sm font-semibold transition-all flex items-center gap-2 bg-transparent text-gray-600 hover:bg-gray-100'.split(' ');

        views.forEach(v => {
            const viewEl = document.getElementById('view-' + v);
            const btnEl = document.getElementById('btn-' + v);
            const badgeEl = btnEl.querySelector('span:last-child');

            // Hapus class lama terlebih dahulu
            btnEl.className = '';

            if (v === tab) {
                viewEl.style.display = 'block';
                
                // Set background color dinamis sesuai tab aktif
                let bgColor = '';
                if (v === 'all') bgColor = 'bg-gray-800';
                if (v === 'vessels') bgColor = 'bg-blue-600';
                if (v === 'offices') bgColor = 'bg-purple-600';
                
                btnEl.classList.add(...activeClass, bgColor);
                badgeEl.className = 'bg-white/20 text-white px-2 py-0.5 rounded-full text-xs font-bold ml-1';
            } else {
                viewEl.style.display = 'none';
                btnEl.classList.add(...inactiveClass);
                badgeEl.className = 'bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-xs font-bold ml-1';
            }
        });
    }
</script>

@endsection