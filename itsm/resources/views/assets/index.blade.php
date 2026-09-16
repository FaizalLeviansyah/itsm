@extends('layouts.app')
@section('title', 'Assets')

@section('content')
<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Asset Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage all IT devices and assets in the organization</p>
    </div>
    <div class="flex items-center gap-2">
        @can('manageSettings')
        <form action="{{ route('assets.soc.sync') }}" method="POST" class="inline" onsubmit="return confirm('Sync all endpoints from SOC? This may create new assets or update existing ones.')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-3 py-2.5 border border-green-200 bg-green-50 rounded-lg text-sm font-medium text-green-700 hover:bg-green-100 transition">
                <i class="fas fa-sync-alt text-xs"></i> Sync SOC
            </button>
        </form>
        <a href="{{ route('assets.import') }}" class="inline-flex items-center gap-2 px-3 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            <i class="fas fa-file-import text-xs"></i> Import CSV
        </a>
        <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition">
            <i class="fas fa-plus text-xs"></i> Add Asset
        </a>
        @endcan
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl border border-gray-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search asset tag, name, serial..." class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
        </div>
        <select name="status" onchange="this.form.submit()" class="no-select2 border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand-500/20">
            <option value="">All Statuses</option>
            @foreach(['available'=>'🟢 Available','in_use'=>'🔵 In Use','maintenance'=>'🟡 Maintenance','retired'=>'⚪ Retired','disposed'=>'🔴 Disposed'] as $val => $label)
            <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="category" onchange="this.form.submit()" class="no-select2 border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand-500/20">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">Filter</button>
        @if(request()->hasAny(['search','status','category']))
        <a href="{{ route('assets.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
        @endif
    </form>
</div>

<!-- Batch Actions Bar (hidden until selection) -->
<div id="batch-bar" class="hidden mb-4 bg-brand-50 border border-brand-200 rounded-xl px-5 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="text-sm font-medium text-brand-700"><span id="selected-count">0</span> asset(s) selected</span>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="printSelectedStickers()" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
            <i class="fas fa-print text-xs"></i> Print Sticker
        </button>
        <button type="button" onclick="clearSelection()" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800">
            <i class="fas fa-times"></i> Cancel
        </button>
    </div>
</div>

<!-- Assets Table -->
<form id="batch-form" action="{{ route('assets.sticker.batch') }}" method="POST" target="_blank">
    @csrf
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" id="select-all" onchange="toggleAll(this)" class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        </th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Asset</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Category</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Company</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Status</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">SOC</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Location / Vessel</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Assigned To</th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                        <td class="px-4 py-4">
                            <input type="checkbox" name="assets[]" value="{{ $asset->id }}" onchange="updateSelection()" class="asset-checkbox w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        </td>
                        <td class="px-6 py-4 cursor-pointer" onclick="window.location='{{ route('assets.show', $asset) }}'">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-purple-50 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-{{ $asset->assetCategory->icon ?? 'cube' }} text-purple-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $asset->name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $asset->asset_tag }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $asset->assetCategory->name }}</td>
                        <td class="px-6 py-4">
                            @if($asset->company)
                            <span class="text-xs font-medium bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $asset->company->code ?? $asset->company->name }}</span>
                            @else
                            <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php $sc = ['available'=>'bg-green-50 text-green-700 border-green-200','in_use'=>'bg-blue-50 text-blue-700 border-blue-200','maintenance'=>'bg-amber-50 text-amber-700 border-amber-200','retired'=>'bg-gray-50 text-gray-600 border-gray-200','disposed'=>'bg-red-50 text-red-700 border-red-200']; @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium border {{ $sc[$asset->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$asset->status)) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($asset->soc_endpoint_id)
                                @php $socColors = ['online'=>'bg-green-500','offline'=>'bg-red-500','unknown'=>'bg-gray-400']; @endphp
                                <span class="inline-flex items-center gap-1.5" title="Last seen: {{ $asset->soc_last_seen?->diffForHumans() ?? 'Never' }}">
                                    <span class="w-2 h-2 rounded-full {{ $socColors[$asset->soc_status] ?? 'bg-gray-400' }}"></span>
                                    <span class="text-xs text-gray-500">{{ ucfirst($asset->soc_status ?? 'unknown') }}</span>
                                </span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $asset->location ?? $asset->vessel_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $asset->assignedUser->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('assets.show', $asset) }}" class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-100 text-gray-500 hover:text-brand-600" title="View">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('assets.sticker', $asset) }}" class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-100 text-gray-500 hover:text-purple-600" title="Sticker">
                                    <i class="fas fa-qrcode text-xs"></i>
                                </a>
                                @can('manageSettings')
                                <a href="{{ route('assets.edit', $asset) }}" class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-100 text-gray-500 hover:text-amber-600" title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
                            <i class="fas fa-server text-4xl text-gray-200 mb-3"></i>
                            <p class="text-gray-500 font-medium">No assets found</p>
                            <p class="text-sm text-gray-400 mt-1">Add your first asset to start tracking</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">{{ $assets->firstItem() }}-{{ $assets->lastItem() }} of {{ $assets->total() }}</p>
            <div>{{ $assets->withQueryString()->links() }}</div>
        </div>
        @endif
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleAll(el) {
    document.querySelectorAll('.asset-checkbox').forEach(cb => cb.checked = el.checked);
    updateSelection();
}

function updateSelection() {
    const checked = document.querySelectorAll('.asset-checkbox:checked');
    const bar = document.getElementById('batch-bar');
    const count = document.getElementById('selected-count');
    count.textContent = checked.length;
    bar.classList.toggle('hidden', checked.length === 0);
}

function printSelectedStickers() {
    const checked = document.querySelectorAll('.asset-checkbox:checked');
    if (checked.length === 0) return alert('Select at least 1 asset');
    document.getElementById('batch-form').submit();
}

function clearSelection() {
    document.querySelectorAll('.asset-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('select-all').checked = false;
    updateSelection();
}
</script>
@endpush
