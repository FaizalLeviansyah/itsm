@extends('layouts.app')
@section('title', 'Kategori Ticket')
@section('header', 'Kategori Ticket')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Add Category -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Tambah Kategori</h3>
        <form action="{{ route('admin.settings.categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">Nama Kategori *</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="e.g. Hardware, Software, Network">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Icon (FontAwesome)</label>
                    <input type="text" name="icon" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="e.g. fas fa-laptop">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Warna</label>
                    <input type="color" name="color" value="#3B82F6" class="w-full h-10 border border-gray-300 rounded-lg">
                </div>
            </div>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2.5 rounded-lg text-sm hover:bg-primary-700 transition w-full">
                <i class="fas fa-plus mr-2"></i> Tambah Kategori
            </button>
        </form>

        <hr class="my-6">

        <h3 class="text-sm font-semibold text-gray-700 mb-4">Tambah Sub-Kategori</h3>
        <form action="{{ route('admin.settings.subcategories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">Kategori Induk *</label>
                <select name="category_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Nama Sub-Kategori *</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="bg-accent-600 text-white px-4 py-2.5 rounded-lg text-sm hover:bg-accent-700 transition w-full">
                <i class="fas fa-plus mr-2"></i> Tambah Sub-Kategori
            </button>
        </form>
    </div>

    <!-- Categories List -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Daftar Kategori</h3>
        <div class="space-y-3">
            @forelse($categories as $category)
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 rounded-full" style="background: {{ $category->color }}"></div>
                        <span class="text-sm font-medium text-gray-700">{{ $category->name }}</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ $category->tickets_count ?? 0 }} tickets</span>
                </div>
                @if($category->subCategories->count() > 0)
                <div class="mt-2 ml-6 space-y-1">
                    @foreach($category->subCategories as $sub)
                    <p class="text-xs text-gray-500">• {{ $sub->name }}</p>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">Belum ada kategori</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
