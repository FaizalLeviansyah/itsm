@extends('layouts.app')
@section('title', $article->title)
@section('header', 'Knowledge Base')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8">
        <div class="flex items-center space-x-2 mb-4">
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-primary-100 text-primary-700">{{ $article->category->name }}</span>
            <span class="text-xs text-gray-400">{{ $article->created_at->format('d M Y') }}</span>
            <span class="text-xs text-gray-400">•</span>
            <span class="text-xs text-gray-400">{{ $article->views }} views</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $article->title }}</h1>

        <div class="flex items-center space-x-3 mb-6 pb-6 border-b">
            <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                <span class="text-primary-700 font-semibold text-xs">{{ strtoupper(substr($article->author->name, 0, 2)) }}</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">{{ $article->author->name }}</p>
                <p class="text-xs text-gray-500">{{ $article->author->position ?? $article->author->role }}</p>
            </div>
        </div>

        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
            {!! nl2br(e($article->content)) !!}
        </div>

        <div class="mt-8 pt-6 border-t flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Was this article helpful?</span>
                <form action="{{ route('knowledge.helpful', $article) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1.5 rounded-lg text-sm transition">
                        <i class="fas fa-thumbs-up mr-1"></i> Yes ({{ $article->helpful_count }})
                    </button>
                </form>
            </div>
            <a href="{{ route('knowledge.index') }}" class="text-sm text-primary-600 hover:text-primary-700">← Back</a>
        </div>
    </div>

    @if($related->count() > 0)
    <div class="mt-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Related Articles</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($related as $rel)
            <a href="{{ route('knowledge.show', $rel) }}" class="bg-white rounded-lg border p-4 hover:border-primary-300 transition">
                <p class="text-sm font-medium text-gray-700">{{ $rel->title }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $rel->views }} views</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
