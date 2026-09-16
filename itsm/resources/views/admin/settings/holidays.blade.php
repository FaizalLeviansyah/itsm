@extends('layouts.app')
@section('title', 'SLA Calendar - Hari Libur')
@section('header', 'SLA Calendar - Hari Libur')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Add Holiday -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Tambah Hari Libur</h3>
        <p class="text-xs text-gray-500 mb-4">Hari libur akan dikecualikan dari perhitungan SLA. Weekend (Sabtu-Minggu) otomatis dikecualikan.</p>
        <form action="{{ route('admin.settings.holidays.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">Nama Hari Libur *</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500" placeholder="e.g. Tahun Baru, Idul Fitri, HUT RI">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Tanggal *</label>
                <input type="date" name="date" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_recurring" value="1" class="rounded border-gray-300 text-primary-600">
                    <span class="text-sm text-gray-600">Berulang setiap tahun (tanggal & bulan sama)</span>
                </label>
            </div>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2.5 rounded-lg text-sm hover:bg-primary-700 transition w-full">
                <i class="fas fa-plus mr-2"></i> Tambah
            </button>
        </form>
    </div>

    <!-- Holidays List -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Daftar Hari Libur</h3>
        <div class="space-y-2 max-h-96 overflow-y-auto">
            @forelse($holidays as $holiday)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $holiday->name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $holiday->date->format('d M Y') }}
                        @if($holiday->is_recurring) <span class="text-primary-600">(Recurring)</span> @endif
                    </p>
                </div>
                <form action="{{ route('admin.settings.holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm"><i class="fas fa-trash"></i></button>
                </form>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">Belum ada hari libur</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
