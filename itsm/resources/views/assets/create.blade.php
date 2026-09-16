@extends('layouts.app')
@section('title', 'Add Asset')

@section('content')
<div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
    <a href="{{ route('assets.index') }}" class="hover:text-brand-600">Assets</a>
    <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
    <span class="text-brand-600 font-medium">Add New Asset</span>
</div>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Add New Asset</h1>
    <p class="text-sm text-gray-500 mt-1">Register a new IT device or asset into the system.</p>
</div>

<form action="{{ route('assets.store') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Basic Information</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Asset Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g. Dell Latitude 5420 Laptop">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Category *</label>
                            <select name="asset_category_id" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('asset_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Manufacturer</label>
                            <input type="text" name="manufacturer" value="{{ old('manufacturer') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g. Dell, HP, Cisco">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Model</label>
                            <input type="text" name="model" value="{{ old('model') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g. Latitude 5420">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Serial Number</label>
                            <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g. CN0XJ2J-WSL001">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Description</label>
                        <textarea name="description" rows="2" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Additional description or specifications...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Assignment & Location -->
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Placement & Assignment</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Status *</label>
                            <select name="status" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                                <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>🟢 Available</option>
                                <option value="in_use" {{ old('status') == 'in_use' ? 'selected' : '' }}>🔵 In Use</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>🟡 Maintenance</option>
                                <option value="retired" {{ old('status') == 'retired' ? 'selected' : '' }}>⚪ Retired</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Assign to User</label>
                            <select name="assigned_to" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                                <option value="">None (unassigned)</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->department ?? $user->role }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Location</label>
                            <input type="text" name="location" value="{{ old('location') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g. Head Office - IT Dept, Floor 3">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Vessel (if on a ship)</label>
                            <input type="text" name="vessel_name" value="{{ old('vessel_name') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g. ETERNAL OIL I">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">IP Address</label>
                            <input type="text" name="ip_address" value="{{ old('ip_address') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="192.168.1.x">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">MAC Address</label>
                            <input type="text" name="mac_address" value="{{ old('mac_address') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="AA:BB:CC:DD:EE:FF">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial -->
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Purchase & Warranty Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Purchase Date</label>
                        <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Purchase Cost</label>
                        <input type="number" name="purchase_cost" value="{{ old('purchase_cost') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Warranty Expiry</label>
                        <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('assets.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Save Asset
                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-5">
            <!-- Company -->
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Company / Ownership</h4>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Company *</label>
                    <select name="company_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }} ({{ $company->code }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Asset tag will be generated based on the selected company</p>
                </div>
            </div>

            <!-- Asset Tag Preview -->
            <div class="bg-blue-50 rounded-xl border border-blue-100 p-5">
                <h4 class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-3">
                    <i class="fas fa-tag text-blue-500 mr-1"></i> Asset Tag
                </h4>
                <p class="text-sm text-blue-700">Asset tag will be automatically generated after saving, based on the selected company.</p>
                <div class="mt-3 bg-white border border-blue-200 rounded-lg px-3 py-2 text-center">
                    <span class="text-sm font-mono font-bold text-gray-800">ASM-ASM-00001</span>
                </div>
                <p class="text-[10px] text-blue-500 mt-1 text-center">Format: {PREFIX}-{CODE}-{SEQUENTIAL}</p>
            </div>

            <!-- Sticker Info -->
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                    <i class="fas fa-qrcode text-gray-400 mr-1"></i> Asset Sticker
                </h4>
                <p class="text-xs text-gray-500 leading-relaxed">After saving, you can print a QR code sticker from the asset detail page. The sticker contains asset information and a QR code for scanning.</p>
            </div>

            <!-- Field Guide -->
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                    <i class="fas fa-info-circle text-gray-400 mr-1"></i> Field Guide
                </h4>
                <ul class="space-y-2 text-xs text-gray-500">
                    <li><span class="font-medium text-gray-700">Serial Number</span> — Unique identifier from the manufacturer</li>
                    <li><span class="font-medium text-gray-700">Vessel</span> — Fill in if the asset is placed on a ship</li>
                    <li><span class="font-medium text-gray-700">IP/MAC</span> — For network devices or servers</li>
                    <li><span class="font-medium text-gray-700">Company</span> — Asset ownership</li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection
