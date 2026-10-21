@extends('layouts.app')
@section('title', $article->title)
@section('header', 'Knowledge Base')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-primary-100 text-primary-700">{{ $article->category->name }}</span>
                @if($article->pdf_path)
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-700">
                    <i class="fas fa-file-pdf mr-1"></i> Manual Book PDF
                </span>
                @endif
                <span class="text-xs text-gray-400">{{ $article->created_at->format('d M Y') }}</span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs text-gray-400">{{ $article->views }} views</span>
            </div>

            @if($article->pdf_path)
            <div class="flex items-center gap-2">
                <a href="{{ asset('storage/' . $article->pdf_path) }}" download class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    <i class="fas fa-download text-primary-600"></i> Download PDF
                </a>
                <button onclick="togglePdfFullscreen()" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    <i class="fas fa-expand"></i> Fullscreen
                </button>
            </div>
            @endif
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

        <!-- JIKA ARTIKEL MEMILIKI LAMPIRAN PDF MANUAL BOOK -->
        @if($article->pdf_path)
        <div class="mb-6 space-y-3">
            <!-- Toolbar Navigasi & Search Per Word PDF -->
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-1 min-w-[240px]">
                    <div class="relative w-full">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                        <input type="text" id="pdf-search-input" onkeydown="if(event.key==='Enter') executePdfSearch()" placeholder="Search word in PDF..." 
                               class="w-full pl-9 pr-4 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-primary-500 bg-white">
                    </div>
                    <button onclick="executePdfSearch()" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold rounded-lg transition">
                        Find
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button id="prev-page" onclick="changePdfPage(-1)" class="px-3 py-1 bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 rounded text-xs font-medium shadow-xs">
                        <i class="fas fa-chevron-left mr-1"></i> Prev
                    </button>
                    <span class="text-xs font-semibold text-gray-700">
                        Page <input type="number" id="page-num-input" value="1" min="1" onchange="jumpToPage(this.value)" class="w-12 text-center py-0.5 border border-gray-300 rounded text-xs font-bold inline-block">
                    </span>
                    <button id="next-page" onclick="changePdfPage(1)" class="px-3 py-1 bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 rounded text-xs font-medium shadow-xs">
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>

            <!-- PDF Container Viewer -->
            <div id="pdf-container-box" class="w-full h-[75vh] bg-gray-800 rounded-xl overflow-hidden border border-gray-300 shadow-inner">
                <iframe id="pdf-viewer-iframe" src="{{ asset('storage/' . $article->pdf_path) }}#toolbar=1&navpanes=1&scrollbar=1" class="w-full h-full border-0"></iframe>
            </div>
        </div>
        @endif

        <!-- Deskripsi / Konten Teks Artikel -->
        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-xl border border-gray-100">
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

@push('scripts')
<script>
let currentPage = 1;
const pdfBaseUrl = "{{ asset('storage/' . ($article->pdf_path ?? '')) }}";

function togglePdfFullscreen() {
    const box = document.getElementById('pdf-container-box');
    if (!document.fullscreenElement) {
        if (box.requestFullscreen) box.requestFullscreen();
    } else {
        if (document.exitFullscreen) document.exitFullscreen();
    }
}

function executePdfSearch() {
    const keyword = document.getElementById('pdf-search-input').value.trim();
    const iframe = document.getElementById('pdf-viewer-iframe');
    if (keyword !== '') {
        iframe.src = pdfBaseUrl + "#search=" + encodeURIComponent(keyword) + "&toolbar=1&navpanes=1";
    } else {
        iframe.src = pdfBaseUrl + "#toolbar=1&navpanes=1";
    }
}

function jumpToPage(pageNum) {
    currentPage = parseInt(pageNum) || 1;
    const iframe = document.getElementById('pdf-viewer-iframe');
    iframe.src = pdfBaseUrl + "#page=" + currentPage + "&toolbar=1&navpanes=1";
}

function changePdfPage(direction) {
    currentPage += direction;
    if (currentPage < 1) currentPage = 1;
    document.getElementById('page-num-input').value = currentPage;
    jumpToPage(currentPage);
}
</script>
@endpush
@endsection