@extends('layouts.app')
@section('title', 'Problem Management')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Problem Management</h1>
        <p class="text-sm text-gray-500 mt-1">Track root causes and prevent recurring incidents</p>
    </div>
    <a href="{{ route('problems.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition">
        <i class="fas fa-plus text-xs"></i> New Problem
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search problems..." class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm">
            </div>
        </div>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
            <option value="">All Status</option>
            @foreach(['open','investigating','known_error','resolved','closed'] as $s)
            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-brand-500 text-white px-4 py-2.5 rounded-lg text-sm font-medium">Filter</button>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Problem</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Category</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Impact</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase px-6 py-3">Incidents</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Owner</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($problems as $problem)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 cursor-pointer" onclick="window.location='{{ route('problems.show', $problem) }}'">
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-brand-600">{{ $problem->problem_number }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($problem->title, 40) }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $problem->category->name }}</td>
                    <td class="px-6 py-4">
                        @php $psc = ['open'=>'bg-blue-50 text-blue-700 border-blue-200','investigating'=>'bg-indigo-50 text-indigo-700 border-indigo-200','known_error'=>'bg-amber-50 text-amber-700 border-amber-200','resolved'=>'bg-green-50 text-green-700 border-green-200','closed'=>'bg-gray-50 text-gray-600 border-gray-200']; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium border {{ $psc[$problem->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$problem->status)) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="flex items-center gap-1.5 text-xs font-medium" style="color:{{ $problem->priority->color }}">
                            <span class="w-2 h-2 rounded-full" style="background:{{ $problem->priority->color }}"></span>{{ $problem->priority->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-gray-800">{{ $problem->affected_incidents }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $problem->owner->name }}</td>
                    <td class="px-6 py-4 text-xs text-gray-500">{{ $problem->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <i class="fas fa-bug text-4xl text-gray-200 mb-3"></i>
                        <p class="text-gray-500">No problem records yet</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($problems->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $problems->links() }}</div>
    @endif
</div>
@endsection
