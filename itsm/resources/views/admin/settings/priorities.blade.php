@extends('layouts.app')
@section('title', 'Prioritas & SLA')
@section('header', 'Prioritas & SLA')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Add Priority -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Tambah Prioritas</h3>
        <form action="{{ route('admin.settings.priorities.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">Nama *</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="e.g. Critical, High, Medium, Low">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">SLA (jam) *</label>
                    <input type="number" name="sla_hours" required min="1" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="24">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Response Time (jam) *</label>
                    <input type="number" name="response_hours" required min="1" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="4">
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Warna</label>
                <input type="color" name="color" value="#6B7280" class="w-full h-10 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2.5 rounded-lg text-sm hover:bg-primary-700 transition w-full">
                <i class="fas fa-plus mr-2"></i> Tambah Prioritas
            </button>
        </form>
    </div>

    <!-- Priorities List -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Daftar Prioritas</h3>
        <div class="space-y-3">
            @forelse($priorities as $priority)
            <div class="flex items-center justify-between p-4 border rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-4 h-4 rounded" style="background: {{ $priority->color }}"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">{{ $priority->name }}</p>
                        <p class="text-xs text-gray-500">SLA: {{ $priority->sla_hours }}h | Response: {{ $priority->response_hours }}h</p>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">Belum ada prioritas</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
