@extends('layouts.app')
@section('title', 'User Management')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola semua user yang terdaftar di ITSM Portal</p>
    </div>
    
    <div class="flex gap-2">
        @if($unsyncedCount > 0)
        <form action="{{ route('admin.settings.users.sync') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition">
                <i class="fas fa-sync-alt"></i> Sync {{ $unsyncedCount }} Office Users
            </button>
        </form>
        @else
        <form action="{{ route('admin.settings.users.sync') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                <i class="fas fa-sync-alt"></i> Sync Office
            </button>
        </form>
        @endif

        <form action="{{ route('admin.settings.users.sync-vessels') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 border border-blue-200 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-50 transition">
                <i class="fas fa-ship"></i> Sync Vessels
            </button>
        </form>
    </div>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-xl border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex gap-3 items-center">
        <!-- Filter Tipe User (Semua User / Office / Vessel) -->
        <div class="w-44">
            <select name="user_type" id="user_type_select" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" onchange="handleUserTypeChange(this)">
                <option value="">Semua User</option>
                <option value="office" {{ request('user_type') == 'office' ? 'selected' : '' }}>Office</option>
                <option value="vessel" {{ request('user_type') == 'vessel' ? 'selected' : '' }}>Vessel</option>
            </select>
        </div>

        <!-- Filter Pilihan PT (Hanya muncul jika Office atau Semua User dipilih) -->
        <!-- Filter Pilihan PT (Hanya muncul jika Office atau Semua User dipilih) -->
        <div class="w-64" id="company_filter_wrapper">
            <select name="company_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" onchange="this.form.submit()">
                <option value="">Semua PT Office</option>
                <option value="1" {{ request('company_id') == '1' ? 'selected' : '' }}>PT Amarin Ship Management</option>
                <option value="2" {{ request('company_id') == '2' ? 'selected' : '' }}>PT Caraka Tirta Pratama</option>
                <option value="3" {{ request('company_id') == '3' ? 'selected' : '' }}>PT Amarin Crewing Services</option>
            </select>
        </div>

        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user..." class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
        </div>
        <button type="submit" class="bg-brand-500 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            Cari
        </button>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">User</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Email</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Department</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Perusahaan (PT)</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Role</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Status</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Source</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-brand-100 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-brand-700">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->position ?? '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->department ?? $user->job_title ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @php
                            $companyName = $user->company->name ?? 'No Company';
                        @endphp

                        @if(str_contains($companyName, 'Ship Management'))
                            <!-- Hijau untuk ASM -->
                            <span class="px-2.5 py-1 bg-green-50 text-green-800 border border-green-200 rounded-md font-medium text-xs">
                                {{ $companyName }}
                            </span>
                        @elseif(str_contains($companyName, 'Crewing'))
                            <!-- Kuning untuk ACS -->
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-md font-medium text-xs">
                                {{ $companyName }}
                            </span>
                        @else
                            <!-- Merah untuk CTP / Lainnya -->
                            <span class="px-2.5 py-1 bg-red-50 text-red-800 border border-red-200 rounded-md font-medium text-xs">
                                {{ $companyName }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <form action="{{ route('admin.settings.users.role', $user) }}" method="POST" class="inline">
                            @csrf @method('PUT')
                            <select name="role" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:ring-brand-500 bg-white">
                                @foreach(['admin', 'technician', 'user'] as $role)
                                <option value="{{ $role }}" {{ $user->role == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <form action="{{ route('admin.settings.users.toggle', $user) }}" method="POST" class="inline">
                            @csrf @method('PUT')
                            <button type="submit" class="text-xs px-2.5 py-1 rounded-full font-medium border {{ $user->is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 font-medium">{{ $user->source }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $users->withQueryString()->links() }}</div>
    @endif
</div>

<script>
    function handleUserTypeChange(selectElement) {
        const wrapper = document.getElementById('company_filter_wrapper');
        if (selectElement.value === 'vessel') {
            wrapper.style.display = 'none';
            // Reset company filter when switching to vessel so it doesn't conflict
            wrapper.querySelector('select').value = '';
        } else {
            wrapper.style.display = 'block';
        }
        selectElement.form.submit();
    }

    // Jalankan saat load halaman untuk memastikan state awal sesuai
    document.addEventListener("DOMContentLoaded", function() {
        const typeSelect = document.getElementById('user_type_select');
        const wrapper = document.getElementById('company_filter_wrapper');
        if (typeSelect.value === 'vessel') {
            wrapper.style.display = 'none';
        }
    });
</script>
@endsection