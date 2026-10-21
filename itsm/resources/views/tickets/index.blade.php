@extends('layouts.app')
@section('title', 'All Tickets')

@section('content')
<!-- Page Header -->
<!-- Page Header -->
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ isset($isMyTickets) && $isMyTickets ? 'My Assigned Tickets' : 'All Tickets' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Manage and track all organizational service requests</p>
    </div>
</div>

<!-- TAB NAVIGASI ALL TICKETS & MY TICKETS UNTUK ADMIN/TEKNISI -->
@if(auth()->user()->role !== 'user')
<div class="flex items-center gap-2 mb-4 border-b border-gray-200 pb-3">
    <a href="{{ route('tickets.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ !isset($isMyTickets) ? 'bg-brand-500 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
        <i class="fas fa-list mr-1.5"></i> All Tickets
    </a>
    <a href="{{ route('tickets.my') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ isset($isMyTickets) && $isMyTickets ? 'bg-brand-500 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' }}">
        <i class="fas fa-user-shield mr-1.5"></i> My Tickets
    </a>
</div>
@endif

<!-- Advanced & Smart Filters (Langsung Tampil Real-Time) -->
<div class="bg-white rounded-xl border border-gray-200/80 p-4 mb-6 shadow-xs">
    <form method="GET" action="{{ route('tickets.index') }}" class="space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <!-- Status -->
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                    <option value="">All Statuses</option>
                    @foreach(['open','assigned','in_progress','pending','resolved','closed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Priority -->
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Priority</label>
                <select name="priority" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                    <option value="">All Priorities</option>
                    @foreach($priorities as $p)
                        <option value="{{ $p->id }}" {{ request('priority') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Category</label>
                <select name="category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- IT Technician / Assignee (Real-Time Dropdown) -->
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">IT Technician</label>
                <select name="technician_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                    <option value="">All Technicians</option>
                    <option value="unassigned" {{ request('technician_id') === 'unassigned' ? 'selected' : '' }}>⚠️ Unassigned Only</option>
                    @foreach($technicians ?? [] as $tech)
                        <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Vendor / Third-Party -->
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Vendor</label>
                <select name="vendor_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                    <option value="">All Vendors</option>
                    <option value="none" {{ request('vendor_id') === 'none' ? 'selected' : '' }}>Internal IT Only</option>
                    @foreach($vendors ?? [] as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search & Action Buttons -->
            <div class="flex items-center gap-2 lg:col-span-6 pt-2 border-t border-gray-100 justify-between">
                <div class="relative flex-1 max-w-lg">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Smart Search (ID, Title, Email, Dept, Name, Vessel, Vendor)..." 
                           class="w-full pl-8 pr-3 py-2 border border-gray-200 rounded-lg text-xs bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-2xs">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-semibold py-2 px-4 rounded-lg text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-filter text-[10px]"></i> Apply Filter
                    </button>
                    @if(request()->hasAny(['status','priority','category','technician_id','vendor_id','search']))
                        <a href="{{ route('tickets.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium py-2 px-3 rounded-lg text-xs transition flex items-center gap-1">
                            <i class="fas fa-undo text-[10px]"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
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
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    @can('manageTickets')
                    <th class="px-3 py-3 w-10">
                        <input type="checkbox" id="select-all-tickets" onchange="toggleAllTickets(this)" class="w-4 h-4 rounded border-gray-300 text-brand-600">
                    </th>
                    @endcan
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">ID</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Subject</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Proof</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Priority</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Category</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Requester</th>
                    <!-- Pindahkan Company ke dalam <tr> di sini -->
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Company</th>
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

                    <!-- BAGIAN YANG DITAMBAHKAN: Data Kolom Proof (Menampilkan Preview Gambar/File) -->
                    <!-- BAGIAN KOLOM PROOF (PREVIEW POPUP GAMBAR & VIDEO) -->
                    <td class="px-6 py-4">
                        @if($ticket->attachments && $ticket->attachments->count() > 0)
                            <div class="flex gap-1">
                                @foreach($ticket->attachments->where('ticket_comment_id', null)->take(2) as $att)
                                <a href="{{ route('tickets.downloadAttachment', ['ticket' => $ticket->id, 'attachment' => $att->id]) }}?mode=view" target="_blank">
                                    @php
                                        $ext = pathinfo($att->path, PATHINFO_EXTENSION);
                                        $viewUrl = route('tickets.attachment.download', ['ticketId' => $ticket->id, 'attachmentId' => $att->id, 'mode' => 'view']);
                                        $downloadUrl = route('tickets.attachment.download', ['ticketId' => $ticket->id, 'attachmentId' => $att->id]);
                                        $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        $isVideo = in_array(strtolower($ext), ['mp4', 'webm', 'mov']);
                                    @endphp

                                    @if($isImg)
                                        <button type="button" 
                                                onclick="event.stopPropagation(); openPreviewModal('{{ $viewUrl }}', '{{ $downloadUrl }}', '{{ addslashes($att->original_name) }}', '{{ $ext }}')" 
                                                class="block focus:outline-none" 
                                                title="Preview {{ $att->original_name }}">
                                            <img src="{{ $viewUrl }}" class="w-9 h-9 object-cover rounded border border-gray-200 hover:scale-110 transition-transform">
                                        </button>
                                    @elseif($isVideo)
                                        <button type="button" 
                                                onclick="event.stopPropagation(); openPreviewModal('{{ $viewUrl }}', '{{ $downloadUrl }}', '{{ addslashes($att->original_name) }}', '{{ $ext }}')" 
                                                class="w-9 h-9 bg-gray-900 border border-gray-200 rounded flex items-center justify-center hover:bg-black text-white transition focus:outline-none" 
                                                title="Play {{ $att->original_name }}">
                                            <i class="fas fa-play text-xs"></i>
                                        </button>
                                    @else
                                        <button type="button" 
                                                onclick="event.stopPropagation(); openPreviewModal('{{ $viewUrl }}', '{{ $downloadUrl }}', '{{ addslashes($att->original_name) }}', '{{ $ext }}')" 
                                                class="w-9 h-9 bg-gray-50 border border-gray-200 rounded flex items-center justify-center hover:bg-gray-100 transition focus:outline-none" 
                                                title="{{ $att->original_name }}">
                                            <i class="fas fa-paperclip text-gray-400 text-xs"></i>
                                        </button>
                                    @endif
                                @endforeach

                                @if($ticket->attachments->count() > 2)
                                    <span class="text-[10px] text-gray-500 self-center ml-1">+{{ $ticket->attachments->count() - 2 }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-gray-300">-</span>
                        @endif
                    </td>
                    <!-- AKHIR BAGIAN YANG DITAMBAHKAN -->

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
                        @php
                            $reqUser = $ticket->requester;
                            $reqName = $reqUser->full_name ?? $reqUser->name ?? 'Unknown User';
                            
                            // Deteksi apakah ini pelaporan dari Vessel / Kapal
                            $isVesselAccount = ($reqUser->source === 'vessel_api') 
                                || str_contains(strtolower($reqUser->department ?? ''), 'vessel')
                                || str_contains(strtolower($reqUser->position ?? ''), 'vessel')
                                || !empty($ticket->vessel_name);

                            // Tentukan Nama Kapal yang tampil
                            $vesselLabel = !empty($ticket->vessel_name) 
                                ? $ticket->vessel_name 
                                : (str_contains($reqUser->position ?? '', 'Vessel - ') 
                                    ? str_replace('Vessel - ', '', $reqUser->position) 
                                    : ($reqUser->department ?? 'Vessel'));
                        @endphp
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 {{ $isVesselAccount ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }} rounded-full flex items-center justify-center">
                                    <span class="text-[9px] font-bold">
                                        {{ strtoupper(substr($reqName, 0, 2)) }}
                                    </span>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">{{ Str::limit($reqName, 22) }}</span>
                            </div>

                            <!-- BADGE IDENTITAS KAPAL (MNC/VESSEL) -->
                            @if($isVesselAccount)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded w-max ml-8">
                                    <i class="fas fa-ship text-[9px] text-amber-600"></i> {{ Str::limit($vesselLabel, 20) }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $companyObj = $ticket->company ?? ($ticket->requester->company ?? null);
                            $companyName = is_object($companyObj) ? ($companyObj->name ?? '-') : ($companyObj ?? '-');
                            $lowerComp = strtolower($companyName);
                            
                            $colorClass = 'text-blue-600';
                            if ($ticket->vendor || str_contains($lowerComp, 'vendor')) {
                                $colorClass = 'text-emerald-600';
                            } elseif (str_contains($lowerComp, 'caraka')) {
                                $colorClass = 'text-red-600';
                            } elseif (str_contains($lowerComp, 'crewing')) {
                                $colorClass = 'text-amber-500';
                            }
                        @endphp
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-[9px] font-bold {{ $colorClass }}">
                                        {{ $companyName !== '-' ? strtoupper(substr($companyName, 0, 2)) : '-' }}
                                    </span>
                                </div>
                                <span class="text-sm font-bold {{ $colorClass }}">
                                    {{ Str::limit($companyName, 18) }}
                                </span>
                            </div>
                            @if($ticket->vendor)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-700 bg-purple-50 border border-purple-200 px-1.5 py-0.5 rounded w-max mt-0.5">
                                    <i class="fas fa-handshake text-[9px]"></i> {{ Str::limit($ticket->vendor->name, 14) }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($ticket->assignedTechnicians && $ticket->assignedTechnicians->count() > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($ticket->assignedTechnicians as $index => $tech)
                                    @php
                                        $badgeColors = [
                                            'bg-blue-50 text-blue-700 border-blue-200',
                                            'bg-purple-50 text-purple-700 border-purple-200',
                                            'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'bg-amber-50 text-amber-700 border-amber-200',
                                            'bg-rose-50 text-rose-700 border-rose-200'
                                        ];
                                        $colorClass = $badgeColors[$index % count($badgeColors)];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $colorClass }}">
                                        {{ Str::limit($tech->name, 15) }}
                                    </span>
                                @endforeach
                            </div>
                        @elseif($ticket->assignee)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ Str::limit($ticket->assignee->name, 15) }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400 italic">Unassigned</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                        {{ $ticket->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <!-- Ubah colspan dari 9 menjadi 11 agar span memenuhi seluruh lebar tabel -->
                    <td colspan="11" class="px-6 py-16 text-center">
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