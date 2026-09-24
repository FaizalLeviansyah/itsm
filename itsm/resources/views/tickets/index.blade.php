@extends('layouts.app')
@section('title', 'All Tickets')

@section('content')
<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">All Tickets</h1>
        <p class="text-sm text-gray-500 mt-1">Manage and track all organizational service requests</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('tickets.index') }}" class="hidden sm:inline-flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            <i class="fas fa-filter text-xs"></i> More Filters
        </a>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase">
            <span>Status</span>
        </div>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            <option value="">All Statuses</option>
            @foreach(['open','assigned','in_progress','pending','resolved','closed','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>

        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase">
            <span>Priority</span>
        </div>
        <select name="priority" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            <option value="">All Priorities</option>
            @foreach($priorities as $p)
            <option value="{{ $p->id }}" {{ request('priority') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>

        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase">
            <span>Category</span>
        </div>
        <select name="category" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>

        <div class="flex-1 min-w-[180px]">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
        </div>

        <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Filter</button>
        @if(request()->hasAny(['status','priority','category','search','type']))
        <a href="{{ route('tickets.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
        @endif

        <div class="ml-auto text-xs text-gray-400">
            Showing {{ $tickets->count() }} of {{ $tickets->total() }} tickets
        </div>
    </form>
</div>

<!-- Batch Actions Bar -->
@can('manageTickets')
<div id="ticket-batch-bar" class="hidden mb-4 bg-brand-50 border border-brand-200 rounded-xl px-5 py-3 flex items-center justify-between animate-slide-up">
    <span class="text-sm font-medium text-brand-700"><span id="ticket-count">0</span> tickets selected</span>
    <form id="bulk-form" action="{{ route('tickets.bulk') }}" method="POST" class="flex items-center gap-2">
        @csrf
        <div id="bulk-ids"></div>
        <select name="action" required class="border border-brand-200 rounded-lg px-3 py-1.5 text-sm bg-white">
            <option value="assign">Assign To...</option>
            
            <!-- Tampilkan opsi Close All hanya untuk Admin -->
            @if(Auth::user()->role === 'admin')
                <option value="close">Close All</option>
            @endif
            
            <option value="cancel">Cancel All</option>
        </select>
        <select name="assign_to" class="border border-brand-200 rounded-lg px-3 py-1.5 text-sm bg-white">
            @foreach(\App\Models\User::whereIn('role',['technician','admin'])->where('is_active',true)->get() as $tech)
            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-brand-500 text-white px-4 py-1.5 rounded-lg text-sm font-medium hover:bg-brand-600 transition">Apply</button>
        <button type="button" onclick="clearTicketSelection()" class="text-sm text-gray-500 hover:text-gray-700 ml-1">Cancel</button>
    </form>
</div>
@endcan

<!-- Tickets Table -->
<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    @can('manageTickets')
                    <th class="px-3 py-3 w-10">
                        <input type="checkbox" id="select-all-tickets" onchange="toggleAllTickets(this)" class="w-4 h-4 rounded border-gray-300 text-brand-600">
                    </th>
                    @endcan
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">ID</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Subject</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Priority</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Category</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Requester</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Technician</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr class="table-row border-b border-gray-50 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                    @can('manageTickets')
                    <td class="px-3 py-4" onclick="event.stopPropagation()">
                        <input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}" onchange="updateTicketSelection()" class="ticket-cb w-4 h-4 rounded border-gray-300 text-brand-600">
                    </td>
                    @endcan
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-brand-600">{{ $ticket->ticket_number }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900">{{ Str::limit($ticket->title, 40) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($ticket->description, 50) }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @php $priorityStyles = ['Critical'=>'bg-red-50 text-red-700 border-red-200','High'=>'bg-orange-50 text-orange-700 border-orange-200','Medium'=>'bg-amber-50 text-amber-700 border-amber-200','Low'=>'bg-gray-50 text-gray-600 border-gray-200']; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium border {{ $priorityStyles[$ticket->priority->name] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                            {{ $ticket->priority->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @php $sc = ['open'=>'bg-blue-50 text-blue-700 border-blue-200','assigned'=>'bg-sky-50 text-sky-700 border-sky-200','in_progress'=>'bg-indigo-50 text-indigo-700 border-indigo-200','pending'=>'bg-amber-50 text-amber-700 border-amber-200','resolved'=>'bg-green-50 text-green-700 border-green-200','closed'=>'bg-gray-50 text-gray-600 border-gray-200','cancelled'=>'bg-red-50 text-red-700 border-red-200']; @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium border {{ $sc[$ticket->status] ?? '' }}">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                        @if($ticket->is_overdue)
                        <span class="inline-flex items-center ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200 animate-pulse">⚠ SLA</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $ticket->category->name }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                <span class="text-[9px] font-bold text-gray-600">{{ strtoupper(substr($ticket->requester->name, 0, 2)) }}</span>
                            </div>
                            <span class="text-sm text-gray-700">{{ Str::limit($ticket->requester->name, 15) }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                <span class="text-[9px] font-bold text-gray-600">
                                    {{ $ticket->company ? strtoupper(substr($ticket->company->name, 0, 2)) : '-' }}
                                </span>
                            </div>
                            <span class="text-sm text-gray-700">
                                {{ $ticket->company ? Str::limit($ticket->company->name, 15) : '-' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($ticket->assignee)
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 bg-brand-100 rounded-full flex items-center justify-center">
                                <span class="text-[9px] font-bold text-brand-700">{{ strtoupper(substr($ticket->assignee->name, 0, 2)) }}</span>
                            </div>
                            <span class="text-sm text-gray-700">{{ Str::limit($ticket->assignee->name, 12) }}</span>
                        </div>
                        @else
                        <span class="text-xs text-gray-400 italic">Unassigned</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center">
                        <i class="fas fa-inbox text-4xl text-gray-200 mb-3"></i>
                        <p class="text-gray-500 font-medium">No tickets found</p>
                        <p class="text-sm text-gray-400 mt-1">Create a new ticket to get IT support</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $tickets->firstItem() }}-{{ $tickets->lastItem() }} of {{ $tickets->total() }}</p>
        <div>{{ $tickets->withQueryString()->links() }}</div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleAllTickets(el) {
    document.querySelectorAll('.ticket-cb').forEach(cb => cb.checked = el.checked);
    updateTicketSelection();
}
function updateTicketSelection() {
    const checked = document.querySelectorAll('.ticket-cb:checked');
    const bar = document.getElementById('ticket-batch-bar');
    const count = document.getElementById('ticket-count');
    const bulkIds = document.getElementById('bulk-ids');
    if (bar) {
        count.textContent = checked.length;
        bar.classList.toggle('hidden', checked.length === 0);
        bulkIds.innerHTML = '';
        checked.forEach(cb => {
            bulkIds.innerHTML += '<input type="hidden" name="ticket_ids[]" value="' + cb.value + '">';
        });
    }
}
function clearTicketSelection() {
    document.querySelectorAll('.ticket-cb').forEach(cb => cb.checked = false);
    document.getElementById('select-all-tickets').checked = false;
    updateTicketSelection();
}
</script>
@endpush
