@extends('layouts.app')
@section('title', 'Reports & Analytics')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="text-sm text-gray-500 mt-1">Monitor IT team performance and service metrics comprehensively.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('export.tickets.excel', ['period' => $period]) }}" class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            <i class="fas fa-file-excel text-green-600"></i> Excel
        </a>
        <a href="{{ route('export.tickets.pdf', ['period' => $period]) }}" class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            <i class="fas fa-file-pdf text-red-500"></i> PDF
        </a>
    </div>
</div>

<!-- Period Tabs -->
<div class="bg-white rounded-xl border border-gray-100 p-1.5 inline-flex gap-1 mb-8">
    @foreach(['week' => 'This Week', 'month' => 'This Month', 'quarter' => 'Quarter', 'year' => 'This Year'] as $key => $label)
    <a href="?period={{ $key }}" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $period == $key ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">{{ $label }}</a>
    @endforeach
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-ticket-alt text-blue-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_tickets'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Tickets</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-green-600">{{ $stats['resolved_tickets'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Resolved</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-indigo-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_resolution_time'] }}<span class="text-sm font-normal text-gray-400 ml-0.5">min</span></p>
        <p class="text-xs text-gray-500 mt-1">Avg Resolution</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-star text-amber-500 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_rating'] }}<span class="text-sm font-normal text-gray-400 ml-0.5">/5</span></p>
        <p class="text-xs text-gray-500 mt-1">Avg Rating</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 {{ $stats['sla_compliance'] >= 90 ? 'bg-green-50' : 'bg-red-50' }} rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt {{ $stats['sla_compliance'] >= 90 ? 'text-green-600' : 'text-red-600' }} text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold {{ $stats['sla_compliance'] >= 90 ? 'text-green-600' : 'text-red-600' }}">{{ $stats['sla_compliance'] }}%</p>
        <p class="text-xs text-gray-500 mt-1">SLA Compliance</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-bolt text-purple-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['first_response_avg'] }}<span class="text-sm font-normal text-gray-400 ml-0.5">min</span></p>
        <p class="text-xs text-gray-500 mt-1">Avg 1st Response</p>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">
    <!-- Ticket Trend (wider) -->
    <div class="lg:col-span-3 bg-white rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Ticket Trend</h3>
                <p class="text-xs text-gray-500 mt-0.5">Daily ticket count for this period</p>
            </div>
        </div>
        <canvas id="trendChart" height="200"></canvas>
    </div>

    <!-- By Category (narrower) -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Category Distribution</h3>
                <p class="text-xs text-gray-500 mt-0.5">Tickets by category</p>
            </div>
        </div>
        @if($byCategory->count() > 0)
        <div class="space-y-3">
            @php $maxCount = $byCategory->max('count') ?: 1; @endphp
            @foreach($byCategory as $item)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-700">{{ $item->name }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $item->count }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full bg-brand-500" style="width: {{ round(($item->count / $maxCount) * 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex items-center justify-center h-40 text-gray-400 text-sm">
            <p>No category data yet</p>
        </div>
        @endif
    </div>
</div>

<!-- Technician Performance -->
<div class="bg-white rounded-xl border border-gray-100">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Technician Performance</h3>
            <p class="text-xs text-gray-500 mt-0.5">Rating and resolution time per technician</p>
        </div>
        <a href="{{ route('export.performance.excel', ['period' => $period]) }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:text-brand-700">
            <i class="fas fa-download"></i> Export
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Technician</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Resolved</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Rating</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Response</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Resolution</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Professionalism</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Avg Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($techPerformance as $tech)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-brand-100 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-brand-700">{{ strtoupper(substr($tech->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $tech->name }}</p>
                                <p class="text-xs text-gray-400">{{ ucfirst($tech->role) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-gray-900">{{ $tech->resolved_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="inline-flex items-center gap-1 bg-amber-50 px-2.5 py-1 rounded-lg">
                            <i class="fas fa-star text-amber-400 text-[10px]"></i>
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($tech->avg_rating ?? 0, 1) }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php $val = $tech->avg_response ?? 0; @endphp
                        <div class="flex items-center justify-center gap-1">
                            <div class="w-12 bg-gray-100 rounded-full h-1.5"><div class="h-1.5 rounded-full bg-blue-500" style="width:{{ ($val/5)*100 }}%"></div></div>
                            <span class="text-xs text-gray-600">{{ number_format($val, 1) }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php $val = $tech->avg_resolution ?? 0; @endphp
                        <div class="flex items-center justify-center gap-1">
                            <div class="w-12 bg-gray-100 rounded-full h-1.5"><div class="h-1.5 rounded-full bg-green-500" style="width:{{ ($val/5)*100 }}%"></div></div>
                            <span class="text-xs text-gray-600">{{ number_format($val, 1) }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php $val = $tech->avg_professionalism ?? 0; @endphp
                        <div class="flex items-center justify-center gap-1">
                            <div class="w-12 bg-gray-100 rounded-full h-1.5"><div class="h-1.5 rounded-full bg-purple-500" style="width:{{ ($val/5)*100 }}%"></div></div>
                            <span class="text-xs text-gray-600">{{ number_format($val, 1) }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm text-gray-700">{{ round($tech->avg_resolution_time ?? 0) }}<span class="text-xs text-gray-400 ml-0.5">min</span></span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center text-gray-400">
                            <i class="fas fa-chart-bar text-3xl mb-2"></i>
                        <p class="text-sm font-medium">No performance data yet</p>
                            <p class="text-xs mt-1">Data will appear once tickets have been resolved and rated</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        const labels = {!! json_encode($dailyTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!};
        const data = {!! json_encode($dailyTrend->pluck('count')) !!};

        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tickets',
                    data: data,
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    borderColor: '#2563eb',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(37, 99, 235, 0.3)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 12 },
                        bodyFont: { size: 11 },
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { size: 11 }, color: '#94a3b8', stepSize: 1 },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#94a3b8' },
                        border: { display: false }
                    }
                }
            }
        });
    }
</script>
@endpush
