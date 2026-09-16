@extends('layouts.app')
@section('title', 'Kategori Asset')
@section('header', 'Kategori Asset')

@section('content')
<div class="space-y-6">
    <!-- Tombol Sync SOC -->
    @can('manageSettings')
    <div class="flex justify-end">
        <form action="{{ route('assets.soc.sync') }}" method="POST" onsubmit="return confirm('Sync all endpoints from SOC?')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700 transition shadow-sm">
                <i class="fas fa-sync-alt"></i> Sync SOC
            </button>
        </form>
    </div>
    @endcan

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Add -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Tambah Kategori Asset</h3>
            <form action="{{ route('admin.settings.asset-categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Nama *</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="e.g. Laptop, Printer, Server, Network">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Icon (FontAwesome class)</label>
                    <input type="text" name="icon" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="e.g. laptop, print, server, network-wired">
                </div>
                <button type="submit" class="bg-primary-600 text-white px-4 py-2.5 rounded-lg text-sm hover:bg-primary-700 transition w-full">
                    <i class="fas fa-plus mr-2"></i> Tambah
                </button>
            </form>
        </div>

        <!-- List -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Daftar Kategori</h3>
            <div class="space-y-3">
                @forelse($categories as $cat)
                <div class="flex items-center justify-between p-4 border rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-{{ $cat->icon ?? 'cube' }} text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $cat->name }}</p>
                            <p class="text-xs text-gray-500">{{ $cat->assets_count }} assets</p>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">Belum ada kategori</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection