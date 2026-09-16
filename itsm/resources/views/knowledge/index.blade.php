@extends('layouts.app')
@section('title', 'Knowledge Base')
@section('header', 'Knowledge Base')

@section('content')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
    <div class="flex flex-wrap gap-3 items-end justify-between">
        <form method="GET" class="flex flex-wrap gap-3 items-end flex-1">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..." class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <select name="category" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-700"><i class="fas fa-search mr-1"></i> Search</button>
        </form>
        @can('manageTickets')
        <a href="{{ route('knowledge.create') }}" class="bg-accent-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-accent-700 transition">
            <i class="fas fa-plus mr-1"></i> New Article
        </a>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($articles as $article)
    <a href="{{ route('knowledge.show', $article) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition group">
        <div class="flex items-center space-x-2 mb-3">
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-primary-100 text-primary-700">{{ $article->category->name }}</span>
            @if($article->status === 'draft')
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Draft</span>
            @endif
        </div>
        <h3 class="text-sm font-semibold text-gray-800 group-hover:text-primary-600 transition line-clamp-2">{{ $article->title }}</h3>
        <p class="text-xs text-gray-500 mt-2 line-clamp-3">{{ Str::limit(strip_tags($article->content), 120) }}</p>
        <div class="flex items-center justify-between mt-4 pt-3 border-t">
            <span class="text-xs text-gray-400">{{ $article->author->name }}</span>
            <div class="flex items-center space-x-3 text-xs text-gray-400">
                <span><i class="fas fa-eye mr-1"></i>{{ $article->views }}</span>
                <span><i class="fas fa-thumbs-up mr-1"></i>{{ $article->helpful_count }}</span>
            </div>
        </div>
    </a>
    @empty
    <div class="col-span-full text-center py-12">
        <i class="fas fa-book-open text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">No articles yet</p>
    </div>
    @endforelse
</div>

@if($articles->hasPages())
<div class="mt-6">{{ $articles->withQueryString()->links() }}</div>
@endif
@endsection
