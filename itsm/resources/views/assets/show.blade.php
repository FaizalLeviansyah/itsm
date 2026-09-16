@extends('layouts.app')
@section('title', $asset->name)

@section('content')
<!-- Active Audit Banner -->
@if(isset($activeAuditItem) && $activeAuditItem && $activeAuditItem->scan_status === 'pending')
<div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-5 text-white">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-clipboard-check text-lg"></i>
            </div>
            <div>
                <h3 class="font-semibold">🔍 Audit In Progress</h3>
                <p class="text-blue-100 text-sm">{{ $activeAuditItem->audit->title }} ({{ $activeAuditItem->audit->audit_number }})</p>
            </div>
        </div>
        <form action="{{ route('audits.scan', $activeAuditItem->audit) }}" method="POST" class="flex items-center gap-2">
            @csrf
            <input type="hidden" name="asset_id" value="{{ $asset->id }}">
            <select name="condition" required class="no-select2 bg-white/20 border border-white/30 rounded-lg px-3 py-2 text-sm text-white placeholder-blue-200 focus:ring-2 focus:ring-white/50">
                <option value="good" class="text-gray-800">✅ Good</option>
                <option value="fair" class="text-gray-800">🔵 Fair</option>
                <option value="poor" class="text-gray-800">🟡 Poor</option>
                <option value="damaged" class="text-gray-800">🔴 Damaged</option>
                <option value="missing" class="text-gray-800">❌ Missing</option>
            </select>
            <button type="submit" class="bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-50 transition">
                <i class="fas fa-check mr-1"></i> Confirm
            </button>
        </form>
    </div>
</div>
@elseif(isset($activeAuditItem) && $activeAuditItem && $activeAuditItem->scan_status !== 'pending')
<div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
    <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
        <i class="fas fa-check-circle text-green-600"></i>
    </div>
    <div>
        <p class="text-sm font-medium text-green-800">Asset already scanned in this audit</p>
        <p class="text-xs text-green-600">Condition: {{ ucfirst($activeAuditItem->condition) }} • {{ $activeAuditItem->scanned_at?->diffForHumans() }}</p>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Asset Info -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $asset->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $asset->asset_tag }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('assets.sticker', $asset) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition" title="Print Sticker">
                        <i class="fas fa-qrcode"></i> Sticker
                    </a>
                    @php $assetStatusColors = ['available'=>'bg-green-100 text-green-800','in_use'=>'bg-blue-100 text-blue-800','maintenance'=>'bg-yellow-100 text-yellow-800','retired'=>'bg-gray-100 text-gray-800','disposed'=>'bg-red-100 text-red-800']; @endphp
                    <span class="text-xs font-medium px-3 py-1 rounded-full {{ $assetStatusColors[$asset->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$asset->status)) }}</span>
                </div>
            </div>

            @if($asset->description)
            <p class="text-sm text-gray-600 mb-4">{{ $asset->description }}</p>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Category</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->assetCategory->name }}</p>
                </div>
                @if($asset->manufacturer)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Manufacturer</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->manufacturer }}</p>
                </div>
                @endif
                @if($asset->model)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Model</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->model }}</p>
                </div>
                @endif
                @if($asset->serial_number)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Serial Number</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->serial_number }}</p>
                </div>
                @endif
                @if($asset->ip_address)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">IP Address</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->ip_address }}</p>
                </div>
                @endif
                @if($asset->location)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Location</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->location }}</p>
                </div>
                @endif
                @if($asset->assignedUser)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Assigned To</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->assignedUser->name }}</p>
                </div>
                @endif
                @if($asset->purchase_cost)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Purchase Cost</p>
                    <p class="text-sm font-medium text-gray-700">Rp {{ number_format($asset->purchase_cost, 0, ',', '.') }}</p>
                </div>
                @endif
                @if($asset->warranty_expiry)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Warranty</p>
                    <p class="text-sm font-medium {{ $asset->is_warranty_active ? 'text-green-700' : 'text-red-600' }}">
                        {{ $asset->warranty_expiry->format('d M Y') }}
                        {{ $asset->is_warranty_active ? '(Active)' : '(Expired)' }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- SOC Status (if integrated) -->
        @if($asset->soc_endpoint_id)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-shield-alt text-green-600 mr-2"></i>SOC Integration
                </h3>
                @php $socStatusColors = ['online'=>'bg-green-100 text-green-800','offline'=>'bg-red-100 text-red-800','unknown'=>'bg-gray-100 text-gray-800']; @endphp
                <span class="text-xs font-medium px-3 py-1 rounded-full {{ $socStatusColors[$asset->soc_status] ?? 'bg-gray-100 text-gray-800' }}">
                    <span class="inline-block w-2 h-2 rounded-full mr-1 {{ $asset->soc_status === 'online' ? 'bg-green-500' : ($asset->soc_status === 'offline' ? 'bg-red-500' : 'bg-gray-400') }}"></span>
                    {{ ucfirst($asset->soc_status ?? 'Unknown') }}
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @if($asset->hostname)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Hostname</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->hostname }}</p>
                </div>
                @endif
                @if($asset->antivirus_status)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Antivirus</p>
                    <p class="text-sm font-medium {{ str_contains(strtolower($asset->antivirus_status), 'active') || str_contains(strtolower($asset->antivirus_status), 'enabled') ? 'text-green-700' : 'text-red-600' }}">{{ $asset->antivirus_status }}</p>
                </div>
                @endif
                @if($asset->usb_status)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">USB Status</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->usb_status }}</p>
                </div>
                @endif
                @if($asset->windows_update_status)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Windows Update</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->windows_update_status }}</p>
                </div>
                @endif
                @if($asset->pc_brand)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">PC Brand</p>
                    <p class="text-sm font-medium text-gray-700">{{ $asset->pc_brand }}</p>
                </div>
                @endif
                @if($asset->soc_last_seen)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Last Seen</p>
                    <p class="text-sm font-medium text-gray-700" title="{{ $asset->soc_last_seen->format('d M Y H:i:s') }}">{{ $asset->soc_last_seen->diffForHumans() }}</p>
                </div>
                @endif
                @if($asset->soc_synced_at)
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Last Synced</p>
                    <p class="text-sm font-medium text-gray-700" title="{{ $asset->soc_synced_at->format('d M Y H:i:s') }}">{{ $asset->soc_synced_at->diffForHumans() }}</p>
                </div>
                @endif
            </div>

            @if($asset->pc_specs)
            <div class="mt-4">
                <p class="text-xs text-gray-500 mb-2">PC Specifications</p>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $asset->pc_specs }}</p>
                </div>
            </div>
            @endif

            @if($asset->installed_apps && count($asset->installed_apps) > 0)
            <div class="mt-4">
                <p class="text-xs text-gray-500 mb-2">Installed Applications ({{ count($asset->installed_apps) }})</p>
                <div class="bg-gray-50 rounded-lg p-3 max-h-48 overflow-y-auto">
                    <div class="flex flex-wrap gap-1">
                        @foreach(array_slice($asset->installed_apps, 0, 20) as $app)
                        <span class="inline-flex px-2 py-0.5 bg-white border border-gray-200 rounded text-xs text-gray-600">{{ is_array($app) ? ($app['name'] ?? $app[0] ?? 'Unknown') : $app }}</span>
                        @endforeach
                        @if(count($asset->installed_apps) > 20)
                        <span class="inline-flex px-2 py-0.5 bg-gray-200 rounded text-xs text-gray-600">+{{ count($asset->installed_apps) - 20 }} more</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($asset->paired_hardware && count($asset->paired_hardware) > 0)
            <div class="mt-4">
                <p class="text-xs text-gray-500 mb-2">Paired Hardware</p>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex flex-wrap gap-2">
                        @foreach($asset->paired_hardware as $hw)
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-white border border-gray-200 rounded text-xs text-gray-600">
                            <i class="fas fa-plug text-gray-400"></i>
                            {{ is_array($hw) ? ($hw['name'] ?? $hw['type'] ?? json_encode($hw)) : $hw }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Related Tickets -->
        @if($asset->tickets->count() > 0)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Related Tickets</h3>
            <div class="space-y-2">
                @foreach($asset->tickets as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div>
                        <span class="text-sm font-medium text-primary-600">{{ $ticket->ticket_number }}</span>
                        <span class="text-sm text-gray-600 ml-2">{{ $ticket->title }}</span>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: {{ $ticket->priority->color }}20; color: {{ $ticket->priority->color }}">{{ $ticket->priority->name }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar - History -->
    <div class="space-y-6">
        @can('manageSettings')
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('assets.edit', $asset) }}" class="bg-primary-600 text-white text-center py-2.5 rounded-lg text-sm hover:bg-primary-700 transition">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <button type="button" onclick="document.getElementById('transferModal').classList.remove('hidden')"
                class="bg-orange-500 text-white text-center py-2.5 rounded-lg text-sm hover:bg-orange-600 transition">
                <i class="fas fa-exchange-alt mr-1"></i> Transfer
            </button>
        </div>
        <form action="{{ route('assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Delete this asset?')">
            @csrf @method('DELETE')
            <button type="submit" class="w-full bg-red-600 text-white py-2.5 rounded-lg text-sm hover:bg-red-700 transition">
                <i class="fas fa-trash mr-1"></i> Delete
            </button>
        </form>
        @endcan

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Activity History</h3>
                @if($asset->histories->count() > 5)
                <a href="{{ route('assets.history', $asset) }}" class="text-xs text-primary-600 hover:underline">
                    View all ({{ $asset->histories->count() }})
                </a>
                @endif
            </div>
            <div class="space-y-4">
                @php
                $actionConfig = [
                    'created'        => ['color' => 'bg-green-400',  'icon' => 'fa-plus',        'badge' => 'bg-green-50 text-green-700'],
                    'assigned'       => ['color' => 'bg-blue-400',   'icon' => 'fa-user',        'badge' => 'bg-blue-50 text-blue-700'],
                    'moved'          => ['color' => 'bg-orange-400', 'icon' => 'fa-map-marker-alt','badge' => 'bg-orange-50 text-orange-700'],
                    'status_changed' => ['color' => 'bg-yellow-400', 'icon' => 'fa-exchange-alt','badge' => 'bg-yellow-50 text-yellow-700'],
                    'updated'        => ['color' => 'bg-purple-400', 'icon' => 'fa-edit',        'badge' => 'bg-purple-50 text-purple-700'],
                    'deleted'        => ['color' => 'bg-red-400',    'icon' => 'fa-trash',       'badge' => 'bg-red-50 text-red-700'],
                ];
                @endphp
                @forelse($asset->histories->take(5) as $history)
                @php $cfg = $actionConfig[$history->action] ?? ['color'=>'bg-gray-400','icon'=>'fa-circle','badge'=>'bg-gray-50 text-gray-700']; @endphp
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-7 h-7 rounded-full {{ $cfg['color'] }} flex items-center justify-center">
                        <i class="fas {{ $cfg['icon'] }} text-white text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-medium text-gray-800">{{ $history->user->name }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded {{ $cfg['badge'] }} font-medium">{{ ucfirst(str_replace('_',' ',$history->action)) }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $history->description }}</p>
                        @if($history->changes)
                        <div class="mt-1.5 space-y-1">
                            @foreach($history->changes as $field => $change)
                            @php
                            $labels = ['name'=>'Name','status'=>'Status','assigned_to'=>'User','location'=>'Location','vessel_name'=>'Vessel','company_id'=>'Company','manufacturer'=>'Manufacturer','model'=>'Model','serial_number'=>'Serial Number','ip_address'=>'IP Address','mac_address'=>'MAC Address'];
                            $label = $labels[$field] ?? ucfirst(str_replace('_',' ',$field));
                            @endphp
                            <div class="text-xs text-gray-500 flex items-center gap-1 flex-wrap">
                                <span class="font-medium text-gray-600">{{ $label }}:</span>
                                <span class="bg-red-50 text-red-600 px-1 rounded line-through">{{ $change['old'] ?: '-' }}</span>
                                <i class="fas fa-arrow-right text-gray-400"></i>
                                <span class="bg-green-50 text-green-700 px-1 rounded">{{ $change['new'] ?: '-' }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $history->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">No activity yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
<!-- Transfer Modal -->
@can('manageSettings')
<div id="transferModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('transferModal').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-orange-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Transfer Asset</h3>
                    <p class="text-xs text-gray-500">{{ $asset->name }} · {{ $asset->asset_tag }}</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('transferModal').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('assets.transfer', $asset) }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Current state info -->
            <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-600 space-y-1">
                <div class="flex justify-between">
                    <span>Current user:</span>
                    <span class="font-medium text-gray-800">{{ $asset->assignedUser?->name ?? 'None' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Current location:</span>
                    <span class="font-medium text-gray-800">{{ $asset->location ?? '-' }}</span>
                </div>
                @if($asset->vessel_name)
                <div class="flex justify-between">
                    <span>Current vessel:</span>
                    <span class="font-medium text-gray-800">{{ $asset->vessel_name }}</span>
                </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    New User <span class="text-gray-400 font-normal">(leave blank to unassign)</span>
                </label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    <option value="">— None / Unassign —</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $asset->assigned_to == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">New Location</label>
                <input type="text" name="location" value="{{ $asset->location }}"
                    placeholder="e.g. Server Room Floor 2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Vessel / Ship</label>
                <input type="text" name="vessel_name" value="{{ $asset->vessel_name }}"
                    placeholder="Vessel name (optional)"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Transfer Notes <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea name="notes" rows="2" placeholder="Transfer reason, asset condition, etc..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 resize-none"></textarea>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" onclick="document.getElementById('transferModal').classList.add('hidden')"
                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 transition">
                    <i class="fas fa-exchange-alt mr-1"></i> Save Transfer
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

@if(session('success') || session('info'))
<div id="flash-msg" class="fixed bottom-5 right-5 z-50 px-5 py-3.5 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2
    {{ session('success') ? 'bg-green-600 text-white' : 'bg-blue-600 text-white' }}">
    <i class="fas {{ session('success') ? 'fa-check-circle' : 'fa-info-circle' }}"></i>
    {{ session('success') ?? session('info') }}
</div>
<script>setTimeout(() => document.getElementById('flash-msg')?.remove(), 4000)</script>
@endif

@endsection
