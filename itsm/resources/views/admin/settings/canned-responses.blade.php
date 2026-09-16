@extends('layouts.app')
@section('title', 'Canned Responses')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Canned Responses</h1>
    <p class="text-sm text-gray-500 mt-1">Template jawaban cepat untuk teknisi. Hemat waktu untuk masalah umum.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Tambah Template</h3>
        <form action="{{ route('admin.settings.canned-responses.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Judul *</label>
                <input type="text" name="title" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm" placeholder="e.g. Reset Password">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Kategori (opsional)</label>
                <select name="category_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Konten *</label>
                <textarea name="content" rows="5" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm" placeholder="Tuliskan template jawaban..."></textarea>
            </div>
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white py-2.5 rounded-lg text-sm font-semibold transition">Tambah</button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-3">
        @forelse($responses as $response)
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">{{ $response->title }}</h4>
                    <p class="text-xs text-gray-400">{{ $response->category->name ?? 'Semua' }} • Dipakai {{ $response->usage_count }}x</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3">{{ Str::limit($response->content, 150) }}</p>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
            <i class="fas fa-comment-dots text-4xl text-gray-200 mb-3"></i>
            <p class="text-gray-500">Belum ada template response</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
