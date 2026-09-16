@extends('layouts.app')
@section('title', 'Search: ' . $q)

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Search Results</h1>
    <p class="text-sm text-gray-500 mt-1">Showing results for: <span class="font-medium text-gray-700">"{{ $q }}"</span></p>
</div>

<div class="space-y-6">
    <!-- Tickets -->
    @if(isset($tickets) && $tickets->count() > 0)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2"><i class="fas fa-ticket-alt text-brand-500"></i> Tickets ({{ $tickets->count() }})</h3>
        <div class="space-y-2">
            @foreach($tickets as $ticket)
            <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div>
                    <span class="text-sm font-mono text-brand-600">{{ $ticket->ticket_number }}</span>
                    <span class="text-sm text-gray-700 ml-2">{{ Str::limit($ticket->title, 50) }}</span>
                </div>
                <span class="flex items-center gap-1.5 text-xs"><span class="w-2 h-2 rounded-full" style="background:{{ $ticket->priority->color }}"></span>{{ $ticket->priority->name }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Assets -->
    @if(isset($assets) && $assets->count() > 0)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2"><i class="fas fa-server text-purple-500"></i> Assets ({{ $assets->count() }})</h3>
        <div class="space-y-2">
            @foreach($assets as $asset)
            <a href="{{ route('assets.show', $asset) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div>
                    <span class="text-sm font-mono text-purple-600">{{ $asset->asset_tag }}</span>
                    <span class="text-sm text-gray-700 ml-2">{{ $asset->name }}</span>
                </div>
                <span class="text-xs text-gray-400">{{ $asset->assetCategory->name }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Users -->
    @if(isset($users) && $users->count() > 0)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2"><i class="fas fa-users text-green-500"></i> Users ({{ $users->count() }})</h3>
        <div class="space-y-2">
            @foreach($users as $user)
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-8 h-8 bg-brand-100 rounded-full flex items-center justify-center"><span class="text-xs font-bold text-brand-700">{{ strtoupper(substr($user->name, 0, 2)) }}</span></div>
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $user->email }} • {{ ucfirst($user->role) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Articles -->
    @if(isset($articles) && $articles->count() > 0)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2"><i class="fas fa-book-open text-amber-500"></i> Knowledge Base ({{ $articles->count() }})</h3>
        <div class="space-y-2">
            @foreach($articles as $article)
            <a href="{{ route('knowledge.show', $article) }}" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <p class="text-sm font-medium text-gray-800">{{ $article->title }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit(strip_tags($article->content), 80) }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- No Results -->
    @if((!isset($tickets) || $tickets->count() === 0) && (!isset($assets) || $assets->count() === 0) && (!isset($users) || $users->count() === 0) && (!isset($articles) || $articles->count() === 0))
    <div class="bg-white rounded-xl border border-gray-100 p-16 text-center">
        <i class="fas fa-search text-4xl text-gray-200 mb-3"></i>
        <p class="text-gray-500 font-medium">No results found</p>
        <p class="text-sm text-gray-400 mt-1">Try a different keyword or be more specific</p>
    </div>
    @endif
</div>
@endsection
