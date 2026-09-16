@extends('layouts.app')
@section('title', $ticket->ticket_number)

@section('content')
<!-- Ticket Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <span class="text-sm text-gray-500">TICKET {{ $ticket->ticket_number }}</span>
            @php $priorityBg = ['Critical'=>'bg-red-100 text-red-700','High'=>'bg-orange-100 text-orange-700','Medium'=>'bg-amber-100 text-amber-700','Low'=>'bg-gray-100 text-gray-600']; @endphp
            <span class="text-xs font-bold uppercase px-2 py-0.5 rounded {{ $priorityBg[$ticket->priority->name] ?? 'bg-gray-100 text-gray-600' }}">{{ $ticket->priority->name }} Priority</span>
            @if($ticket->is_overdue)
            <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded animate-pulse">⚠️ SLA BREACH</span>
            @endif
        </div>
        <h1 class="text-xl font-bold text-gray-900">{{ $ticket->title }}</h1>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
        @if(in_array($ticket->status, ['open', 'assigned']) && $ticket->requester_id === Auth::id())
        <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            <i class="fas fa-edit text-xs text-gray-400"></i> Edit
        </a>
        @endif
        @can('manageTickets')
        <button onclick="document.getElementById('assign-modal').classList.toggle('hidden')" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            <i class="fas fa-user-plus text-xs text-gray-400"></i> Assign
        </button>
        <button onclick="document.getElementById('status-modal').classList.toggle('hidden')" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            <i class="fas fa-exchange-alt text-xs text-gray-400"></i> Change Status
        </button>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Description -->
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-gray-500 text-sm"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Issue Description</h3>
            </div>
            <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $ticket->description }}</div>

            @if($ticket->attachments->count() > 0)
            <div class="mt-5 pt-5 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Attachments ({{ $ticket->attachments->count() }})</p>
                <div class="space-y-2">
                    @foreach($ticket->attachments as $att)
                    <a href="{{ asset('storage/' . $att->path) }}" target="_blank" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="w-8 h-8 bg-white border rounded flex items-center justify-center"><i class="fas fa-file text-gray-400 text-xs"></i></div>
                        <span class="text-sm text-gray-700 flex-1">{{ $att->original_name }}</span>
                        <i class="fas fa-download text-gray-400 text-xs"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Rating (required) -->
        @if($ticket->status === 'resolved' && $ticket->requester_id === Auth::id() && !$ticket->rating)
        <div class="bg-white rounded-xl border-2 border-amber-300 p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fas fa-star text-amber-500"></i></div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">⭐ Rating Required</h3>
                    <p class="text-xs text-gray-500">The ticket will not close until you submit a rating.</p>
                </div>
            </div>
            <form action="{{ route('tickets.rate', $ticket) }}" method="POST" class="space-y-4 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Overall Rating *</label>
                    <div class="flex gap-1" id="star-rating">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" onclick="setRating({{ $i }})" class="star text-4xl text-gray-200 hover:text-amber-400 transition cursor-pointer" data-value="{{ $i }}">★</button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating-value" required>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Response Speed</label>
                        <select name="response_rating" class="no-select2 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            <option value="">-</option>
                            @for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }} ★</option>@endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Solution Quality</label>
                        <select name="resolution_rating" class="no-select2 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            <option value="">-</option>
                            @for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }} ★</option>@endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Professionalism</label>
                        <select name="professionalism_rating" class="no-select2 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            <option value="">-</option>
                            @for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }} ★</option>@endfor
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Feedback (optional)</label>
                    <textarea name="feedback" rows="3" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm" placeholder="Add additional comments..."></textarea>
                </div>
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-lg text-sm font-semibold transition">
                    Submit Rating & Close Ticket
                </button>
            </form>
        </div>
        @endif

        <!-- Rating Display -->
        @if($ticket->rating)
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-amber-400 text-lg">@for($i=1;$i<=5;$i++){!! $i <= $ticket->rating->rating ? '★' : '<span class="text-gray-200">★</span>' !!}@endfor</span>
                <span class="text-sm font-semibold text-gray-700">{{ $ticket->rating->rating }}/5</span>
            </div>
            @if($ticket->rating->feedback)<p class="text-sm text-gray-600 italic">"{{ $ticket->rating->feedback }}"</p>@endif
            @if($ticket->rating->resolution_time_minutes)<p class="text-xs text-gray-400 mt-2">Resolution time: {{ floor($ticket->rating->resolution_time_minutes/60) }}h {{ $ticket->rating->resolution_time_minutes%60 }}m</p>@endif
        </div>
        @endif

        <!-- Reopen Button -->
        @if(in_array($ticket->status, ['resolved', 'closed']) && $ticket->requester_id === Auth::id())
        <div class="bg-white rounded-xl border border-red-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-red-50 rounded-lg flex items-center justify-center"><i class="fas fa-redo text-red-500 text-sm"></i></div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Issue not resolved?</h3>
                    <p class="text-xs text-gray-500">Reopen the ticket if the problem persists after resolution.</p>
                </div>
            </div>
            <form action="{{ route('tickets.reopen', $ticket) }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="reason" required placeholder="Explain why the issue is not resolved..." class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Reopen</button>
            </form>
        </div>
        @endif

        <!-- Activity & Comments -->
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-history text-gray-500 text-sm"></i></div>
                    <h3 class="text-base font-semibold text-gray-900">Activity & Conversation</h3>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                @foreach($ticket->histories->take(5) as $history)
                <div class="flex gap-3">
                    <div class="w-8 h-8 bg-blue-50 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-bolt text-blue-500 text-xs"></i>
                    </div>
                    <div class="flex-1 bg-blue-50/50 border border-blue-100 rounded-lg p-3">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">{{ $history->user->name }}</span> •
                            @if($history->field === 'status') changed status to <span class="font-medium">{{ ucfirst(str_replace('_',' ',$history->new_value)) }}</span>
                            @elseif($history->field === 'assigned_to') assigned to <span class="font-medium">{{ $history->new_value }}</span>
                            @else {{ $history->note ?? "updated {$history->field}" }}
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ $history->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach

                @foreach($ticket->comments as $comment)
                <div class="flex gap-3">
                    <div class="w-8 h-8 {{ $comment->is_internal ? 'bg-amber-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="text-[10px] font-bold {{ $comment->is_internal ? 'text-amber-700' : 'text-gray-600' }}">{{ strtoupper(substr($comment->user->name, 0, 2)) }}</span>
                    </div>
                    <div class="flex-1 {{ $comment->is_internal ? 'bg-amber-50 border-amber-100' : 'bg-gray-50 border-gray-100' }} border rounded-lg p-3">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-gray-800">{{ $comment->user->name }}</span>
                            @if($comment->is_internal)<span class="text-[10px] bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded font-medium">Internal</span>@endif
                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-700">{{ $comment->comment }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Add Comment -->
            @if(!in_array($ticket->status, ['closed', 'cancelled']))
            <form action="{{ route('tickets.comment', $ticket) }}" method="POST" class="border-t border-gray-100 pt-5">
                @csrf
                @can('manageTickets')
                @if(isset($cannedResponses) && $cannedResponses->count() > 0)
                <div class="mb-3 flex flex-wrap gap-1.5">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase mr-1 self-center">Quick:</span>
                    @foreach($cannedResponses as $canned)
                    <button type="button" onclick="document.getElementById('comment-box').value='{{ addslashes($canned->content) }}'" class="text-[11px] px-2.5 py-1 bg-gray-100 hover:bg-brand-50 hover:text-brand-700 text-gray-600 rounded-full transition">{{ $canned->title }}</button>
                    @endforeach
                </div>
                @endif
                @endcan
                <div class="flex gap-3">
                    <div class="w-8 h-8 bg-brand-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-[10px] font-bold text-brand-700">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="flex-1">
                        <textarea name="comment" id="comment-box" rows="3" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Write a note or reply..."></textarea>
                        <div class="flex items-center justify-between mt-3">
                            @can('manageTickets')
                            <label class="flex items-center gap-2 text-sm text-gray-500">
                                <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                Internal note
                            </label>
                            @else<div></div>@endcan
                            <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                                Send Reply
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-5">
        <!-- Requester Info -->
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Requester Info</p>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold text-gray-600">{{ strtoupper(substr($ticket->requester->name, 0, 2)) }}</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $ticket->requester->name }}</p>
                    <p class="text-xs text-gray-500">{{ $ticket->requester->position ?? $ticket->requester->role }}</p>
                </div>
            </div>
            <div class="space-y-2 text-sm">
                @if($ticket->requester->department)<div class="flex justify-between"><span class="text-gray-500">Department</span><span class="text-gray-800 font-medium">{{ $ticket->requester->department }}</span></div>@endif
                @if($ticket->location)<div class="flex justify-between"><span class="text-gray-500">Location</span><span class="text-gray-800 font-medium">{{ $ticket->location }}</span></div>@endif
                @if($ticket->requester->phone)<div class="flex justify-between"><span class="text-gray-500">Contact</span><span class="text-brand-600 font-medium">{{ $ticket->requester->phone }}</span></div>@endif
            </div>
        </div>

        <!-- Assets -->
        @if($ticket->assets->count() > 0)
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Device Details</p>
            @foreach($ticket->assets as $asset)
            <a href="{{ route('assets.show', $asset) }}" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-purple-100 rounded flex items-center justify-center"><i class="fas fa-server text-purple-600 text-xs"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $asset->name }}</p>
                        <p class="text-xs text-gray-500">{{ $asset->asset_tag }}</p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif

        <!-- SLA Info -->
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">SLA Target</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Resolution Time</span>
                    @if($ticket->due_date && !in_array($ticket->status, ['resolved','closed']))
                        @if($ticket->is_overdue)
                        <span class="text-sm font-bold text-red-600">Overdue</span>
                        @else
                        <span class="text-sm font-bold text-green-600">{{ $ticket->due_date->diffForHumans(null, true) }} remaining</span>
                        @endif
                    @else
                        <span class="text-sm text-gray-600">-</span>
                    @endif
                </div>
                @if($ticket->due_date)
                <div class="w-full bg-gray-100 rounded-full h-2">
                    @php
                        $totalTime = $ticket->created_at->diffInMinutes($ticket->due_date);
                        $elapsed = $ticket->created_at->diffInMinutes(now());
                        $progress = $totalTime > 0 ? min(100, ($elapsed / $totalTime) * 100) : 0;
                    @endphp
                    <div class="h-2 rounded-full {{ $progress > 90 ? 'bg-red-500' : ($progress > 70 ? 'bg-amber-500' : 'bg-brand-500') }}" style="width: {{ $progress }}%"></div>
                </div>
                @endif
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">SLA Level</span>
                    <span class="text-gray-700 font-medium">{{ $ticket->priority->name }} ({{ $ticket->priority->sla_hours }}h Resolution)</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Status</span>
                    @php $sc = ['open'=>'text-blue-600','assigned'=>'text-sky-600','in_progress'=>'text-indigo-600','pending'=>'text-amber-600','resolved'=>'text-green-600','closed'=>'text-gray-500','cancelled'=>'text-red-600']; @endphp
                    <span class="font-medium {{ $sc[$ticket->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                </div>
                @if($ticket->assignee)
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Assignee</span>
                    <span class="text-gray-700 font-medium">{{ $ticket->assignee->name }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Ticket Meta -->
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Ticket Details</p>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between"><span class="text-gray-500">Created</span><span class="text-gray-700">{{ $ticket->created_at->format('d M Y H:i') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Type</span><span class="text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->type)) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Category</span><span class="text-gray-700">{{ $ticket->category->name }}</span></div>
                @if($ticket->vessel_name)<div class="flex justify-between"><span class="text-gray-500">Vessel</span><span class="text-gray-700">{{ $ticket->vessel_name }}</span></div>@endif
                @if($ticket->resolved_at)<div class="flex justify-between"><span class="text-gray-500">Resolved</span><span class="text-green-600">{{ $ticket->resolved_at->format('d M Y H:i') }}</span></div>@endif
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
@can('manageTickets')
<div id="assign-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assign Ticket</h3>
        <form action="{{ route('tickets.assign', $ticket) }}" method="POST">
            @csrf
            <select name="assigned_to" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm mb-4">
                <option value="">Select Technician</option>
                @foreach($technicians as $tech)
                <option value="{{ $tech->id }}" {{ $ticket->assigned_to == $tech->id ? 'selected' : '' }}>{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                @endforeach
            </select>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('assign-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-lg text-sm">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-semibold">Assign</button>
            </div>
        </form>
    </div>
</div>

<!-- Status Modal -->
<div id="status-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Status</h3>
        <form action="{{ route('tickets.status', $ticket) }}" method="POST">
            @csrf
            <select name="status" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm mb-3">
                @foreach(['open','assigned','in_progress','pending','resolved','closed','cancelled'] as $s)
                <option value="{{ $s }}" {{ $ticket->status == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <textarea name="resolution_notes" rows="3" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm mb-4" placeholder="Resolution notes (optional)..."></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('status-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-lg text-sm">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-semibold">Update</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
function setRating(value) {
    document.getElementById('rating-value').value = value;
    document.querySelectorAll('.star').forEach((star, i) => {
        star.classList.toggle('text-amber-400', i < value);
        star.classList.toggle('text-gray-200', i >= value);
    });
}
</script>
@endpush
