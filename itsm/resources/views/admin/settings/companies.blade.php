@extends('layouts.app')
@section('title', 'Company Management')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Company Management</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola perusahaan, logo, dan prefix asset tag</p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Panel Informasi & Tombol Sync API -->
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm h-fit">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Informasi Sistem</h3>
        <p class="text-xs text-gray-500 mb-4">Pengelolaan profil perusahaan dikontrol secara terpusat melalui API Pusat (Single Source of Truth). Anda hanya dapat memodifikasi logo lokal.</p>
        
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-xs text-blue-800 space-y-2 mb-4">
            <p class="font-semibold"><i class="fas fa-info-circle mr-1"></i> Mapping API ID:</p>
            <ul class="list-disc list-inside space-y-1 text-blue-900">
                <li><strong>1</strong> : Amarin</li>
                <li><strong>2</strong> : Caraka</li>
                <li><strong>3</strong> : ACS</li>
                <li><strong>4</strong> : Anu</li>
            </ul>
        </div>

        <form action="{{ route('admin.settings.companies.sync') }}" method="POST">
            @csrf
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-2 shadow-sm">
                <i class="fas fa-sync-alt"></i> Tarik Data dari API
            </button>
        </form>
    </div>

    <!-- Companies List -->
    <div class="lg:col-span-2">
        <div class="space-y-4">
            @forelse($companies as $company)
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4 flex-1">
                        <!-- Logo Wrapper dengan Tombol Edit -->
                        <div class="relative group">
                            <div class="w-14 h-14 bg-gray-50 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden border border-gray-100">
                                @if(!empty($company->logo))
                                    <img src="{{ asset('storage/' . $company->logo) }}" class="w-full h-full object-contain p-1" alt="Logo">
                                @else
                                    <i class="fas fa-building text-gray-300 text-xl"></i>
                                @endif
                            </div>
                            <!-- Tombol Edit Logo Hover -->
                            <button onclick="openLogoModal({{ $company->id }}, '{{ $company->name }}')" class="absolute -bottom-1 -right-1 bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:border-blue-300 w-6 h-6 rounded-full flex items-center justify-center shadow-sm text-[10px] transition opacity-0 group-hover:opacity-100" title="Ubah Logo">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-base font-semibold text-gray-900">{{ $company->name }}</h4>
                                <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">ID: {{ $company->id }}</span>
                                <span class="text-xs font-mono bg-blue-50 text-blue-600 px-2 py-0.5 rounded">{{ $company->code }}</span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $company->full_name ?? '-' }}</p>
                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-400 flex-wrap">
                                <span><i class="fas fa-users mr-1"></i>{{ $company->users_count }} users</span>
                                <span><i class="fas fa-server mr-1"></i>{{ $company->assets_count }} assets</span>
                                <span><i class="fas fa-ticket-alt mr-1"></i>{{ $company->tickets_count }} tickets</span>
                                <span><i class="fas fa-tag mr-1"></i>Prefix: {{ $company->asset_prefix }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Badge Central API -->
                    <div class="flex items-center">
                        <span class="text-[10px] font-medium bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md border border-gray-200">
                            <i class="fas fa-lock mr-1 text-gray-400"></i> Central API
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
                <i class="fas fa-building text-4xl text-gray-200 mb-3"></i>
                <p class="text-gray-500 font-medium">Data Perusahaan Kosong</p>
                <p class="text-sm text-gray-400 mt-1">Silakan klik tombol "Tarik Data dari API" di sebelah kiri.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Edit Logo Saja -->
<div id="logoModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-sm w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Edit Logo <span id="modalCompanyName" class="text-blue-600"></span></h3>
            <button onclick="closeLogoModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="logoForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">Pilih File Logo Baru</label>
                <input type="file" name="logo" accept="image/*" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50">
                <p class="text-[10px] text-gray-400 mt-1">Format: PNG, JPG, SVG. Maks 2MB.</p>
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeLogoModal()" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">Upload Logo</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openLogoModal(id, name) {
        document.getElementById('logoForm').action = "/admin/settings/companies/" + id + "/logo";
        document.getElementById('modalCompanyName').innerText = name;
        document.getElementById('logoModal').classList.remove('hidden');
        document.getElementById('logoModal').classList.add('flex');
    }

    function closeLogoModal() {
        document.getElementById('logoModal').classList.remove('flex');
        document.getElementById('logoModal').classList.add('hidden');
    }
</script>
@endpush
@endsection