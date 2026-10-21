@extends('layouts.app')
@section('title', 'Import Ebook & Literature PDF')
@section('header', 'Knowledge Base - Import Ebook')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <div class="mb-6 pb-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-file-pdf text-red-500"></i> Import Digital Ebook / PDF Literature
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">Unggah dokumen PDF literatur atau manual book agar dapat dibaca langsung secara interaktif oleh tim.</p>
            </div>
            <a href="{{ route('knowledge.index') }}" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">
                ← Kembali
            </a>
        </div>

        <form action="{{ route('knowledge.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Judul Ebook / Literatur *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                           placeholder="Contoh: Manual Book Operasional Sistem Jaringan Kapal 2026...">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Kategori *</label>
                        <select name="category_id" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 bg-white">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Status Publikasi *</label>
                        <select name="status" required class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 bg-white">
                            <option value="published">Published (Langsung Dapat Dibaca)</option>
                            <option value="draft">Draft (Simpan Sementara)</option>
                        </select>
                    </div>
                </div>

                <!-- File Upload PDF Box -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">File Dokumen PDF Ebook *</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-primary-500 transition bg-gray-50/50 relative group cursor-pointer">
                        <div class="flex flex-col items-center pointer-events-none">
                            <div class="w-12 h-12 bg-red-50 text-red-600 rounded-full flex items-center justify-center mb-2 shadow-xs group-hover:scale-110 transition">
                                <i class="fas fa-file-pdf text-xl"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-700">Klik untuk memilih file PDF atau seret ke sini</p>
                            <p class="text-xs text-gray-400 mt-1">Format PDF (Maksimal 50MB)</p>
                        </div>
                        <input type="file" name="pdf_file" accept=".pdf" required 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                               onchange="document.getElementById('file-name-display').innerText = this.files[0] ? 'File terpilih: ' + this.files[0].name : ''">
                    </div>
                    <p id="file-name-display" class="text-xs font-semibold text-primary-600 mt-2"></p>
                    @error('pdf_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Catatan / Sinopsis Singkat (Opsional)</label>
                    <textarea name="content" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary-500" placeholder="Ringkasan isi ebook..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('knowledge.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                        <i class="fas fa-cloud-upload-alt"></i> Import & Simpan Ebook
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection