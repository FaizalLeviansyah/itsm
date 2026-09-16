@extends('layouts.app')
@section('title', $problem->problem_number)

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <span class="text-sm text-gray-500">{{ $problem->problem_number }}</span>
            @php $psc = ['open'=>'bg-blue-50 text-blue-700 border-blue-200','investigating'=>'bg-indigo-50 text-indigo-700 border-indigo-200','known_error'=>'bg-amber-50 text-amber-700 border-amber-200','resolved'=>'bg-green-50 text-green-700 border-green-200','closed'=>'bg-gray-50 text-gray-600 border-gray-200']; @endphp
            <span class="inline-flex px-2.5 py-0.5 rounded text-xs font-medium border {{ $psc[$problem->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$problem->status)) }}</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900">{{ $problem->title }}</h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Description -->
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Problem Description</h3>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $problem->description }}</p>
        </div>

        <!-- Root Cause & Solution Form -->
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Investigation & Resolution</h3>
            <form action="{{ route('problems.update', $problem) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                        @foreach(['open','investigating','known_error','resolved','closed'] as $s)
                        <option value="{{ $s }}" {{ $problem->status == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-red-600 uppercase tracking-wide mb-1.5">🔍 Root Cause Analysis</label>
                    <textarea name="root_cause" rows="4" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm" placeholder="What is the root cause of this problem?">{{ old('root_cause', $problem->root_cause) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-amber-600 uppercase tracking-wide mb-1.5">⚡ Workaround (Temporary Fix)</label>
                    <textarea name="workaround" rows="3" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm" placeholder="Temporary solution that users can apply...">{{ old('workaround', $problem->workaround) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-green-600 uppercase tracking-wide mb-1.5">✅ Permanent Solution</label>
                    <textarea name="solution" rows="3" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm" placeholder="Permanent solution that was implemented...">{{ old('solution', $problem->solution) }}</textarea>
                </div>
                <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition">Update Problem</button>
            </form>
        </div>

        <!-- Linked Incidents -->
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Linked Incidents ({{ $problem->tickets->count() }})</h3>
            </div>
            <div class="space-y-2 mb-4">
                @foreach($problem->tickets as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div>
                        <span class="text-sm font-mono text-brand-600">{{ $ticket->ticket_number }}</span>
                        <span class="text-sm text-gray-600 ml-2">{{ Str::limit($ticket->title, 40) }}</span>
                    </div>
                    <span class="text-xs text-gray-400">{{ $ticket->requester->name }}</span>
                </a>
                @endforeach
            </div>
            @if($availableIncidents->count() > 0)
            <form action="{{ route('problems.link', $problem) }}" method="POST" class="flex gap-2 pt-3 border-t">
                @csrf
                <select name="ticket_id" required class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">Link incident...</option>
                    @foreach($availableIncidents as $inc)
                    <option value="{{ $inc->id }}">{{ $inc->ticket_number }} - {{ Str::limit($inc->title, 35) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded-lg text-sm font-medium">Link</button>
            </form>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Details</h4>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Category</span><span class="text-gray-800 font-medium">{{ $problem->category->name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Priority</span><span class="font-medium" style="color:{{ $problem->priority->color }}">{{ $problem->priority->name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Impact</span><span class="text-gray-800 font-medium">{{ ucfirst($problem->impact) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Owner</span><span class="text-gray-800 font-medium">{{ $problem->owner->name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Identified</span><span class="text-gray-700">{{ $problem->identified_at?->format('d M Y') }}</span></div>
                @if($problem->resolved_at)
                <div class="flex justify-between"><span class="text-gray-500">Resolved</span><span class="text-green-600">{{ $problem->resolved_at->format('d M Y') }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-gray-500">Affected</span><span class="text-gray-800 font-bold">{{ $problem->affected_incidents }} incidents</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
