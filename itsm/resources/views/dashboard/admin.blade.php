@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Operations Overview</h1>
        <p class="text-sm text-gray-500 mt-1">Real-time performance and service desk efficiency metrics.</p>
    </div>
    <div class="flex items-center gap-2">
        @if(isset($companies) && $companies->count() > 0)
        <form method="GET" class="flex items-center gap-2">
            <select name="company_id" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand-500/20">
                <option value="">All Companies</option>
                @foreach($companies as $c)
                <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </form>
        @endif
        <div class="hidden sm:flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600">
            <i class="fas fa-calendar text-gray-400"></i>
            <span>Last 30 Days</span>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100">
        <div class="flex items-start justify-between">
            <div>
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mb-3">
                    <i class="fas fa-folder-open text-blue-600"></i>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Open Tickets</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['open_tickets'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['in_progress'] }} assigned to you</p>
            </div>
            @if($stats['open_tickets'] > 0)
            <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">Active</span>
            @endif
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100">
        <div class="flex items-start justify-between">
            <div>
                <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center mb-3">
                    <i class="fas fa-hourglass-half text-amber-600"></i>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pending Tickets</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['in_progress'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Awaiting response</p>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100">
        <div class="flex items-start justify-between">
            <div>
                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center mb-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Resolved Tickets</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['resolved'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Last 30 days total</p>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+8%↗</span>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100">
        <div class="flex items-start justify-between">
            <div>
                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center mb-3">
                    <i class="fas fa-shield-alt text-indigo-600"></i>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">SLA Met</p>
                @php
                    $total = $stats['total_tickets'] ?: 1;
                    $slaOk = $stats['total_tickets'] - $stats['overdue'];
                    $slaPercent = round(($slaOk / $total) * 100, 1);
                @endphp
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $slaPercent }}%</p>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                    <div class="bg-brand-500 h-1.5 rounded-full" style="width: {{ $slaPercent }}%"></div>
                </div>
            </div>
            <span class="text-xs text-gray-500">Target 98%</span>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Weekly Trend Chart -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-gray-900">Weekly Ticket Trend</h3>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-brand-500 rounded-full"></span>Resolved</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-gray-300 rounded-full"></span>Incoming</span>
            </div>
        </div>
        <canvas id="trendChart" height="180"></canvas>
    </div>

    <!-- Top Categories -->
    <div class="bg-white rounded-xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-5">Top Ticket Categories</h3>
        <div class="space-y-4">
            @foreach($ticketsByPriority as $item)
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-sm text-gray-700">{{ $item->name }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $item->count }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full bg-brand-500" style="width: {{ $stats['total_tickets'] ? round(($item->count / $stats['total_tickets'])*100) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @can('viewReports')
        <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-600 font-medium mt-5 hover:text-brand-700">
            View Detailed Distribution <i class="fas fa-arrow-right text-xs"></i>
        </a>
        @endcan
    </div>
</div>

<!-- Recent Tickets Table -->
<div class="bg-white rounded-xl border border-gray-100">
    <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100">
        <h3 class="text-base font-semibold text-gray-900">Recent Tickets</h3>
        <a href="{{ route('tickets.index') }}" class="text-sm text-brand-600 font-medium hover:text-brand-700">See All Recent Tickets <i class="fas fa-external-link-alt text-xs ml-1"></i></a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Ticket ID</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Subject</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Requester</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Priority</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTickets as $ticket)
                <tr class="table-row border-b border-gray-50 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-brand-600">{{ $ticket->ticket_number }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900">{{ Str::limit($ticket->title, 45) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $ticket->category->name }} • {{ $ticket->created_at->diffForHumans() }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center">
                                <span class="text-[10px] font-bold text-gray-600">{{ strtoupper(substr($ticket->requester->name, 0, 2)) }}</span>
                            </div>
                            <span class="text-sm text-gray-700">{{ $ticket->requester->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $sc = ['open'=>'bg-blue-50 text-blue-700 border-blue-200','assigned'=>'bg-sky-50 text-sky-700 border-sky-200','in_progress'=>'bg-indigo-50 text-indigo-700 border-indigo-200','pending'=>'bg-amber-50 text-amber-700 border-amber-200','resolved'=>'bg-green-50 text-green-700 border-green-200','closed'=>'bg-gray-50 text-gray-600 border-gray-200','cancelled'=>'bg-red-50 text-red-700 border-red-200'];
                        @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium border {{ $sc[$ticket->status] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="flex items-center gap-1.5 text-xs font-medium">
                            <span class="w-2 h-2 rounded-full" style="background:{{ $ticket->priority->color }}"></span>
                            {{ $ticket->priority->name }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p>No tickets yet</p>
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
    const ctx = document.getElementById('trendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($ticketsByStatus->pluck('status')->map(fn($s) => ucfirst(str_replace('_',' ',$s)))) !!},
                datasets: [{
                    label: 'Tickets',
                    data: {!! json_encode($ticketsByStatus->pluck('count')) !!},
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }
</script>
@endpush
