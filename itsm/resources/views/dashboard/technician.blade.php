@extends('layouts.app')
@section('title', 'My Workboard')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Workboard</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome back, {{ Auth::user()->name }}. Here are your tasks for today.</p>
    </div>
    <div class="text-right">
        <p class="text-xs text-gray-400">{{ now()->format('l, d M Y') }}</p>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 text-center">
        <p class="text-2xl font-bold text-brand-600">{{ $stats['assigned_to_me'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mt-1">My Tickets</p>
    </div>
    <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 text-center">
        <p class="text-2xl font-bold text-indigo-600">{{ $stats['in_progress'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mt-1">In Progress</p>
    </div>
    <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 text-center">
        <p class="text-2xl font-bold text-green-600">{{ $stats['resolved_today'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mt-1">Resolved Today</p>
    </div>
    <div class="stat-card bg-white rounded-xl p-4 border {{ $stats['overdue'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-100' }} text-center">
        <p class="text-2xl font-bold {{ $stats['overdue'] > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $stats['overdue'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mt-1">Overdue</p>
    </div>
    <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 text-center">
        <p class="text-2xl font-bold text-amber-600">{{ $stats['avg_rating'] }}<span class="text-sm">/5</span></p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mt-1">My Rating</p>
    </div>
    <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 text-center">
        <p class="text-2xl font-bold text-gray-800">{{ $stats['total_resolved_month'] }}</p>
        <p class="text-[10px] text-gray-500 uppercase tracking-wide mt-1">This Month</p>
    </div>
</div>

<!-- Overdue Alert -->
@if($overdueTickets->count() > 0)
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
    <div class="flex items-center gap-2 mb-3">
        <i class="fas fa-exclamation-triangle text-red-500"></i>
        <h3 class="text-sm font-semibold text-red-800">⚠️ {{ $overdueTickets->count() }} Ticket Overdue</h3>
    </div>
    <div class="space-y-2">
        @foreach($overdueTickets->take(5) as $ticket)
        <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between p-2 bg-white rounded-lg border border-red-100 hover:border-red-300 transition">
            <div>
                <span class="text-xs font-mono text-red-600">{{ $ticket->ticket_number }}</span>
                <span class="text-sm text-gray-700 ml-2">{{ Str::limit($ticket->title, 40) }}</span>
            </div>
            <span class="text-xs text-red-600 font-medium">{{ $ticket->due_date->diffForHumans() }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif

<!-- My Tickets -->
<div class="bg-white rounded-xl border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900">My Tickets</h3>
        <a href="{{ route('tickets.index', ['view' => 'assigned']) }}" class="text-sm text-brand-600 font-medium hover:text-brand-700">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Ticket</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Requester</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Priority</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">SLA</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myTickets as $ticket)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                    <td class="px-6 py-3">
                        <p class="text-sm font-semibold text-brand-600">{{ $ticket->ticket_number }}</p>
                        <p class="text-xs text-gray-500">{{ Str::limit($ticket->title, 35) }}</p>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600">{{ $ticket->requester->name }}</td>
                    <td class="px-6 py-3">
                        <span class="flex items-center gap-1.5 text-xs font-medium">
                            <span class="w-2 h-2 rounded-full" style="background:{{ $ticket->priority->color }}"></span>{{ $ticket->priority->name }}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        @php $sc = ['open'=>'bg-blue-50 text-blue-700','assigned'=>'bg-sky-50 text-sky-700','in_progress'=>'bg-indigo-50 text-indigo-700','pending'=>'bg-amber-50 text-amber-700','resolved'=>'bg-green-50 text-green-700']; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-medium {{ $sc[$ticket->status] ?? 'bg-gray-50 text-gray-600' }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    </td>
                    <td class="px-6 py-3">
                        @if($ticket->due_date)
                            @if($ticket->is_overdue)
                            <span class="text-xs font-bold text-red-600">{{ $ticket->due_date->diffForHumans() }}</span>
                            @else
                            <span class="text-xs text-gray-500">{{ $ticket->due_date->diffForHumans() }}</span>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No active tickets. 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
