@extends('layouts.app')
@section('title', 'Escalation Rules')
@section('header', 'Escalation Rules')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Add Rule -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Tambah Escalation Rule</h3>
        <p class="text-xs text-gray-500 mb-4">Ticket akan otomatis di-escalate jika tidak ada respons dalam waktu yang ditentukan.</p>
        <form action="{{ route('admin.settings.escalation.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">Prioritas *</label>
                <select name="priority_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    <option value="">Pilih Prioritas</option>
                    @foreach($priorities as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Waktu Escalation (menit) *</label>
                <input type="number" name="escalation_minutes" required min="5" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="e.g. 60">
                <p class="text-xs text-gray-400 mt-1">Jika tidak ada first response dalam X menit, escalate</p>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Escalate ke *</label>
                <select name="escalate_to" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    <option value="">Pilih Teknisi/Admin</option>
                    @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Level *</label>
                <select name="level" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
                    @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}">Level {{ $i }}</option>
                    @endfor
                </select>
                <p class="text-xs text-gray-400 mt-1">Level 1 = pertama, Level 2 = kedua (jika level 1 tidak respons), dst.</p>
            </div>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2.5 rounded-lg text-sm hover:bg-primary-700 transition w-full">
                <i class="fas fa-plus mr-2"></i> Tambah Rule
            </button>
        </form>
    </div>

    <!-- Rules List -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Active Rules</h3>
        <div class="space-y-3">
            @forelse($rules as $rule)
            <div class="p-4 border rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: {{ $rule->priority->color }}20; color: {{ $rule->priority->color }}">{{ $rule->priority->name }}</span>
                            <span class="text-xs font-medium bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">Level {{ $rule->level }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            Escalate ke <span class="font-medium">{{ $rule->escalateTo->name }}</span> setelah <span class="font-medium">{{ $rule->escalation_minutes }} menit</span>
                        </p>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">Belum ada rule. Tambahkan rule untuk mengaktifkan auto-escalation.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
