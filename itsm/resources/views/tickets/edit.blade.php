@extends('layouts.app')
@section('title', 'Edit Ticket')

@section('content')
<div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
    <a href="{{ route('tickets.show', $ticket) }}" class="hover:text-brand-600">{{ $ticket->ticket_number }}</a>
    <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
    <span class="text-brand-600 font-medium">Edit</span>
</div>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Edit Ticket</h1>
    <p class="text-sm text-gray-500 mt-1">Update your ticket information.</p>
</div>

<div class="max-w-3xl">
    <form action="{{ route('tickets.update', $ticket) }}" method="POST">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-5">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Subject *</label>
                <input type="text" name="title" value="{{ old('title', $ticket->title) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Category</label>
                    <select name="category_id" class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm bg-white">
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $ticket->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Priority</label>
                    <select name="priority_id" class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm bg-white">
                        @foreach($priorities as $pri)
                        <option value="{{ $pri->id }}" {{ $ticket->priority_id == $pri->id ? 'selected' : '' }}>{{ $pri->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Location</label>
                    <input type="text" name="location" value="{{ old('location', $ticket->location) }}" class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Impact</label>
                    <select name="impact" class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm bg-white">
                        @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','critical'=>'Critical'] as $val => $label)
                        <option value="{{ $val }}" {{ $ticket->impact == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Description *</label>
                <textarea name="description" rows="6" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm">{{ old('description', $ticket->description) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('tickets.show', $ticket) }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition">
                <i class="fas fa-save mr-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
