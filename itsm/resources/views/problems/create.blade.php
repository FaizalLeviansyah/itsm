@extends('layouts.app')
@section('title', 'New Problem')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Record New Problem</h1>
    <p class="text-sm text-gray-500 mt-1">Document the root cause of recurring incidents</p>
</div>

<form action="{{ route('problems.store') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-6 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm" placeholder="e.g. Recurring network timeout on VSAT connection">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Category *</label>
                    <select name="category_id" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                        <option value="">Select</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Priority *</label>
                    <select name="priority_id" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                        @foreach($priorities as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Impact *</label>
                    <select name="impact" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Description *</label>
                <textarea name="description" rows="5" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm" placeholder="Describe the problem, symptoms, and recurring pattern...">{{ old('description') }}</textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('problems.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold">Create Problem</button>
            </div>
        </div>
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Link Related Incidents</h4>
                <select name="incidents[]" multiple class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" size="8">
                    @foreach($incidents as $inc)
                    <option value="{{ $inc->id }}">{{ $inc->ticket_number }} - {{ Str::limit($inc->title, 30) }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Ctrl+click to select multiple</p>
            </div>
            <div class="bg-amber-50 rounded-xl border border-amber-100 p-5">
                <h4 class="text-xs font-semibold text-amber-700 mb-2"><i class="fas fa-lightbulb mr-1"></i> ITIL Problem Process</h4>
                <ol class="text-xs text-amber-700 space-y-1 list-decimal list-inside">
                    <li>Identify & log the problem</li>
                    <li>Investigate root cause</li>
                    <li>Document workaround (if any)</li>
                    <li>Find permanent solution</li>
                    <li>Close & review</li>
                </ol>
            </div>
        </div>
    </div>
</form>
@endsection
