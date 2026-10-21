@extends('layouts.app')
@section('title', 'Amarin Ebook Reader')
@section('header', 'Knowledge Base - Amarin Ebook')

@push('styles')
<style>
    .ebook-reader-container {
        background-color: #f1f5f9;
        min-height: 82vh;
    }
    .pdf-frame-wrapper {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<!-- Search, Filter & Import Action Bar -->
<div class="bg-white rounded-xl border border-gray-200 shadow-xs p-4 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('knowledge.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="flex-1 min-w-[240px] relative">
                <i class="fas fa-search absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul ebook atau literatur..." 
                       class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 bg-white">
            </div>

            <select name="category" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3.5 py-2 text-sm bg-white font-medium text-gray-700 min-w-[180px]">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <!-- Dropdown Pilihan Ebook (Jika ada lebih dari 1 Ebook) -->
            @if($articles->count() > 0)
            <select name="article_id" onchange="this.form.submit()" class="border border-amber-300 bg-amber-50 rounded-lg px-3.5 py-2 text-sm font-bold text-amber-900 min-w-[220px]">
                @foreach($articles as $art)
                <option value="{{ $art->id }}" {{ ($activeArticle && $activeArticle->id == $art->id) ? 'selected' : '' }}>
                    📖 {{ $art->title }}
                </option>
                @endforeach
            </select>
            @endif

            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-900 transition flex items-center gap-1.5 shadow-xs">
                <i class="fas fa-filter text-xs"></i> Filter
            </button>
        </form>

        <!-- Tombol Import Ebook PDF (Dipastikan Selalu Muncul untuk Admin & Teknisi) -->
        @if(in_array(auth()->user()->role, ['admin', 'technician']))
        <a href="{{ route('knowledge.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2 shadow-sm">
            <i class="fas fa-file-upload"></i> Import Ebook PDF
        </a>
        @endif
    </div>
</div>

<!-- CONTAINER EBOOK READER AMARIN -->
@if($activeArticle &&$activeArticle->pdf_path)
<div class="bg-slate-100 rounded-2xl border border-slate-200 p-4 sm:p-6 ebook-reader-container relative shadow-inner">
    
    <!-- TOOLBAR HEADER EBOOK (Sesuai Desain Screenshot) -->
    <div class="bg-white/95 backdrop-blur-md rounded-xl p-3 border border-slate-200/80 shadow-xs mb-4 flex flex-wrap items-center justify-between gap-3 sticky top-2 z-20">
        
        <!-- Judul & Info Dokumen -->
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-red-100 text-red-600 rounded-lg flex items-center justify-center font-bold text-base shadow-xs">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div>
                <h2 class="text-sm font-extrabold text-gray-900 leading-tight uppercase tracking-wide">
                    {{ $activeArticle->title }}
                </h2>
                <p class="text-[11px] text-gray-500 font-medium">
                    {{ $activeArticle->category->name }} • {{$activeArticle->created_at->format('d M Y') }}
                </p>
            </div>
        </div>

        <!-- Tombol Aksi & Navigasi Halaman -->
        <div class="flex items-center flex-wrap gap-2">
            <!-- Reset / Reset View -->
            <button onclick="reloadPdfViewer()" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-xs">
                <i class="fas fa-sync-alt"></i> Kembali
            </button>

            <!-- Navigasi Halaman -->
            <div class="flex items-center bg-slate-100 p-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700">
                <button onclick="changePdfPage(-1)" class="px-2.5 py-1 hover:bg-white rounded transition text-slate-600">
                    <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                </button>
                <span class="px-2 text-slate-900 font-extrabold">
                    <input type="number" id="reader-page-num" value="1" min="1" onchange="jumpPdfPage(this.value)" class="w-10 text-center py-0.5 bg-white border border-slate-300 rounded font-bold text-xs">
                </span>
                <button onclick="changePdfPage(1)" class="px-2.5 py-1 hover:bg-white rounded transition text-slate-600">
                    Selanjutnya <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>

            <!-- Cari Teks di PDF -->
            <div class="relative flex items-center">
                <input type="text" id="reader-search-input" onkeydown="if(event.key==='Enter') searchPdfText()" placeholder="Cari teks di PDF..." 
                       class="pl-3 pr-8 py-1.5 border border-slate-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-brand-500 w-40">
                <button onclick="searchPdfText()" class="absolute right-2 text-slate-400 hover:text-slate-600">
                    <i class="fas fa-search text-xs"></i>
                </button>
            </div>

            <!-- Fullscreen & Zoom Controls -->
            <button onclick="toggleReaderFullscreen()" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg border border-slate-300 text-xs shadow-xs" title="Fullscreen">
                <i class="fas fa-expand-arrows-alt"></i>
            </button>
            <button onclick="zoomPdf(0.1)" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg border border-slate-300 text-xs shadow-xs" title="Zoom In">
                <i class="fas fa-search-plus"></i>
            </button>
            <button onclick="zoomPdf(-0.1)" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg border border-slate-300 text-xs shadow-xs" title="Zoom Out">
                <i class="fas fa-search-minus"></i>
            </button>
        </div>
    </div>

    <!-- FRAME INTERAKTIF PEMBACA PDF EBOOK -->
    <div id="ebook-reader-frame" class="w-full h-[76vh] bg-white rounded-xl overflow-hidden border border-slate-300 pdf-frame-wrapper relative">
        <iframe id="pdf-active-iframe" 
                src="{{ asset('storage/' . $activeArticle->pdf_path) }}#toolbar=1&navpanes=1&scrollbar=1" 
                class="w-full h-full border-0"></iframe>
    </div>

    <!-- BADGE WAKTU SESI MEMBACA (Floating Widget Bawah) -->
    <div class="fixed bottom-6 left-12 z-30 bg-white/90 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-slate-200 shadow-lg flex items-center gap-2 text-xs text-slate-700 font-bold">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
        <span>SESI MEMBACA: <span id="reading-timer" class="text-brand-600">00:00</span> aktif</span>
    </div>

    <!-- FLOATING BOT ASSISTANT (Sesuai Desain Screenshot) -->
    <div class="fixed bottom-6 right-8 z-30">
        <button onclick="alert('Fitur Tanya AI Ebook siap membantu Anda memahami isi literatur ini!')" 
                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-bold text-xs shadow-xl flex items-center gap-2 transition hover:scale-105">
            <i class="fas fa-robot text-sm"></i> Tanya AI
        </button>
    </div>
</div>

@else
<!-- TAMPILAN JIKA BELUM ADA EBOOK TER-IMPORT -->
<div class="col-span-full text-center py-20 bg-white rounded-xl border border-gray-200 p-8 shadow-xs">
    <div class="w-20 h-20 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
        <i class="fas fa-book-open"></i>
    </div>
    <h3 class="text-lg font-bold text-gray-800">Belum Ada Ebook atau Literatur PDF</h3>
    <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">
        Silakan klik tombol <strong class="text-brand-600">Import Ebook PDF</strong> di pojok kanan atas untuk mulai mengunggah file literatur digital.
    </p>
</div>
@endif

@endsection

@push('scripts')
<script>
let currentPdfPage = 1;
const activePdfUrl = "{{ $activeArticle ? asset('storage/' . $activeArticle->pdf_path) : '' }}";

function reloadPdfViewer() {
    if (!activePdfUrl) return;
    document.getElementById('pdf-active-iframe').src = activePdfUrl + "#toolbar=1&navpanes=1";
    document.getElementById('reader-page-num').value = 1;
    currentPdfPage = 1;
}

function jumpPdfPage(pageNum) {
    if (!activePdfUrl) return;
    currentPdfPage = parseInt(pageNum) || 1;
    document.getElementById('pdf-active-iframe').src = activePdfUrl + "#page=" + currentPdfPage + "&toolbar=1&navpanes=1";
}

function changePdfPage(direction) {
    currentPdfPage += direction;
    if (currentPdfPage < 1) currentPdfPage = 1;
    document.getElementById('reader-page-num').value = currentPdfPage;
    jumpPdfPage(currentPdfPage);
}

function searchPdfText() {
    if (!activePdfUrl) return;
    const query = document.getElementById('reader-search-input').value.trim();
    if (query !== '') {
        document.getElementById('pdf-active-iframe').src = activePdfUrl + "#search=" + encodeURIComponent(query) + "&toolbar=1&navpanes=1";
    }
}

function toggleReaderFullscreen() {
    const frame = document.getElementById('ebook-reader-frame');
    if (!document.fullscreenElement) {
        if (frame.requestFullscreen) frame.requestFullscreen();
        else if (frame.webkitRequestFullscreen) frame.webkitRequestFullscreen();
    } else {
        if (document.exitFullscreen) document.exitFullscreen();
    }
}

// Timer Sesi Membaca Sederhana
let secondsRead = 0;
setInterval(() => {
    secondsRead++;
    const mins = String(Math.floor(secondsRead / 60)).padStart(2, '0');
    const secs = String(secondsRead % 60).padStart(2, '0');
    const timerEl = document.getElementById('reading-timer');
    if (timerEl) timerEl.innerText = `${mins}:${secs}`;
}, 1000);
</script>
@endpush