@extends('layouts.app')
@section('title', 'Maintenance Schedules')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Preventive Maintenance</h1>
    <p class="text-sm text-gray-500 mt-1">Jadwalkan maintenance berkala untuk asset. Ticket otomatis dibuat saat jadwal tiba.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Tambah Jadwal</h3>
        <form action="{{ route('admin.settings.maintenance.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Judul Maintenance *</label>
                <input type="text" name="title" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm" placeholder="e.g. Cleaning server, VSAT check">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Asset *</label>
                <select name="asset_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">Pilih Asset</option>
                    @foreach($assets as $asset)<option value="{{ $asset->id }}">{{ $asset->asset_tag }} - {{ $asset->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Frekuensi *</label>
                <select name="frequency" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="weekly">Mingguan</option>
                    <option value="monthly" selected>Bulanan</option>
                    <option value="quarterly">3 Bulan</option>
                    <option value="semi_annual">6 Bulan</option>
                    <option value="annual">Tahunan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Jadwal Berikutnya *</label>
                <input type="date" name="next_due_date" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Assign ke</label>
                <select name="assigned_to" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">Auto (admin)</option>
                    @foreach($technicians as $tech)<option value="{{ $tech->id }}">{{ $tech->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm" placeholder="Prosedur maintenance..."></textarea>
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="auto_create_ticket" value="1" checked class="rounded border-gray-300 text-brand-600">
                <span class="text-xs text-gray-600">Auto-create ticket saat jadwal tiba</span>
            </label>
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white py-2.5 rounded-lg text-sm font-semibold transition">Tambah Jadwal</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Jadwal Aktif</h3>
        <div class="space-y-3">
            @forelse($schedules as $schedule)
            <div class="flex items-center justify-between p-4 border rounded-lg {{ $schedule->next_due_date->isPast() ? 'border-red-200 bg-red-50' : 'border-gray-100' }}">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $schedule->title }}</p>
                    <p class="text-xs text-gray-500">{{ $schedule->asset->asset_tag }} - {{ $schedule->asset->name }}</p>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                        <span><i class="fas fa-redo mr-1"></i>{{ ucfirst(str_replace('_',' ',$schedule->frequency)) }}</span>
                        @if($schedule->assignee)<span><i class="fas fa-user mr-1"></i>{{ $schedule->assignee->name }}</span>@endif
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold {{ $schedule->next_due_date->isPast() ? 'text-red-600' : 'text-gray-700' }}">
                        {{ $schedule->next_due_date->format('d M Y') }}
                    </p>
                    <p class="text-[10px] text-gray-400">{{ $schedule->next_due_date->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-8">Belum ada jadwal maintenance</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
