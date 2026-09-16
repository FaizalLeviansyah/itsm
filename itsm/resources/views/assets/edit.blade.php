@extends('layouts.app')
@section('title', 'Edit Asset')

@section('content')
<div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
    <a href="{{ route('assets.index') }}" class="hover:text-brand-600">Assets</a>
    <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
    <a href="{{ route('assets.show', $asset) }}" class="hover:text-brand-600">{{ $asset->asset_tag }}</a>
    <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
    <span class="text-brand-600 font-medium">Edit</span>
</div>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Edit Asset</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $asset->asset_tag }} - {{ $asset->name }}</p>
</div>

<form action="{{ route('assets.update', $asset) }}" method="POST">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Basic Information</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Asset Name *</label>
                            <input type="text" name="name" value="{{ old('name', $asset->name) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Category *</label>
                            <select name="asset_category_id" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $asset->asset_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Manufacturer</label>
                            <input type="text" name="manufacturer" value="{{ old('manufacturer', $asset->manufacturer) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Model</label>
                            <input type="text" name="model" value="{{ old('model', $asset->model) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Serial Number</label>
                            <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Description</label>
                        <textarea name="description" rows="2" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">{{ old('description', $asset->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Assignment -->
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Placement & Assignment</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                            <select name="status" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                                @foreach(['available'=>'🟢 Available','in_use'=>'🔵 In Use','maintenance'=>'🟡 Maintenance','retired'=>'⚪ Retired','disposed'=>'🔴 Disposed'] as $val => $label)
                                <option value="{{ $val }}" {{ $asset->status == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Assign to User</label>
                            <select name="assigned_to" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                                <option value="">None</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $asset->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Location</label>
                            <input type="text" name="location" value="{{ old('location', $asset->location) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Vessel</label>
                            <input type="text" name="vessel_name" value="{{ old('vessel_name', $asset->vessel_name) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">IP Address</label>
                            <input type="text" name="ip_address" value="{{ old('ip_address', $asset->ip_address) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">MAC Address</label>
                            <input type="text" name="mac_address" value="{{ old('mac_address', $asset->mac_address) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial -->
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Purchase & Warranty</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Purchase Date</label>
                        <input type="date" name="purchase_date" value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Purchase Cost</label>
                        <input type="number" name="purchase_cost" value="{{ old('purchase_cost', $asset->purchase_cost) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Warranty Expiry</label>
                        <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry', $asset->warranty_expiry?->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm">{{ old('notes', $asset->notes) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('assets.show', $asset) }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Update Asset
                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Company</h4>
                <select name="company_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $asset->company_id == $company->id ? 'selected' : '' }}>{{ $company->name }} ({{ $company->code }})</option>
                    @endforeach
                </select>
            </div>

            <div class="bg-gray-50 rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Asset Info</h4>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between"><span class="text-gray-500">Asset Tag</span><span class="font-mono font-bold text-gray-800">{{ $asset->asset_tag }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Created</span><span class="text-gray-700">{{ $asset->created_at->format('d M Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Last Updated</span><span class="text-gray-700">{{ $asset->updated_at->diffForHumans() }}</span></div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
