@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<!-- Welcome -->
<div class="bg-gradient-to-r from-brand-500 to-brand-700 rounded-2xl p-7 mb-8">
    <h2 class="text-xl font-bold text-white">Welcome, {{ Auth::user()->name }}!</h2>
    <p class="text-brand-100 mt-1 text-sm">How can we help? Create a new ticket to get IT support.</p>
    <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 mt-4 bg-white text-brand-700 font-semibold px-5 py-2.5 rounded-lg hover:bg-blue-50 transition text-sm">
        <i class="fas fa-plus text-xs"></i> New Ticket
    </a>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100">
        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mb-3">
            <i class="fas fa-ticket-alt text-blue-600"></i>
        </div>
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Tickets</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['my_tickets'] }}</p>
    </div>
    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100">
        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center mb-3">
            <i class="fas fa-folder-open text-amber-600"></i>
        </div>
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Open</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['open_tickets'] }}</p>
    </div>
    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100">
        <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center mb-3">
            <i class="fas fa-spinner text-indigo-600"></i>
        </div>
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">In Progress</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['in_progress'] }}</p>
    </div>
    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100">
        <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center mb-3">
            <i class="fas fa-check-circle text-green-600"></i>
        </div>
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Resolved</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['resolved'] }}</p>
    </div>
</div>

<!-- Pending Ratings -->
@if($pendingRatings->count() > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6">
    <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fas fa-star text-amber-500"></i></div>
        <div>
            <h3 class="text-sm font-semibold text-gray-900">⭐ Rate Your Tickets ({{ $pendingRatings->count() }})</h3>
            <p class="text-xs text-gray-500">You have resolved tickets that require a satisfaction rating.</p>
        </div>
    </div>
    <div class="space-y-2">
        @foreach($pendingRatings as $ticket)
        <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between p-3 bg-white rounded-lg border border-amber-100 hover:border-amber-300 transition">
            <div>
                <span class="text-sm font-semibold text-brand-600">{{ $ticket->ticket_number }}</span>
                <span class="text-sm text-gray-600 ml-2">{{ $ticket->title }}</span>
            </div>
            <span class="text-xs text-amber-600 font-medium">Rate Now →</span>
        </a>
        @endforeach
    </div>
</div>
@endif

<!-- Recent Tickets -->
<div class="bg-white rounded-xl border border-gray-100">
    <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100">
        <h3 class="text-base font-semibold text-gray-900">My Recent Tickets</h3>
        <a href="{{ route('tickets.index') }}" class="text-sm text-brand-600 font-medium hover:text-brand-700">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Ticket</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Priority</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Assignee</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTickets as $ticket)
                <tr class="table-row border-b border-gray-50 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-brand-600">{{ $ticket->ticket_number }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($ticket->title, 35) }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="flex items-center gap-1.5 text-xs font-medium">
                            <span class="w-2 h-2 rounded-full" style="background:{{ $ticket->priority->color }}"></span>{{ $ticket->priority->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @php $sc = ['open'=>'bg-blue-50 text-blue-700 border-blue-200','assigned'=>'bg-sky-50 text-sky-700 border-sky-200','in_progress'=>'bg-indigo-50 text-indigo-700 border-indigo-200','pending'=>'bg-amber-50 text-amber-700 border-amber-200','resolved'=>'bg-green-50 text-green-700 border-green-200','closed'=>'bg-gray-50 text-gray-600 border-gray-200','cancelled'=>'bg-red-50 text-red-700 border-red-200']; @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium border {{ $sc[$ticket->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $ticket->assignee->name ?? 'Unassigned' }}</td>
                    <td class="px-6 py-4 text-xs text-gray-500">{{ $ticket->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <i class="fas fa-inbox text-3xl text-gray-200 mb-2"></i>
                        <p class="text-gray-500">No tickets yet</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
