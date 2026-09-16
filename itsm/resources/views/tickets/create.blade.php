@extends('layouts.app')
@section('title', 'Create New Ticket')

@section('content')
<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
    <a href="{{ route('tickets.index') }}" class="hover:text-brand-600">Tickets</a>
    <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
    <span class="text-brand-600 font-medium">Create New Ticket</span>
</div>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">New Ticket Form</h1>
    <p class="text-sm text-gray-500 mt-1">Fill in the details below to get technical assistance.</p>
</div>

<form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form (Left 2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-6">
                <!-- Subject -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Subject *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                        placeholder="e.g. Cannot access office VPN">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Category & Priority -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Category *</label>
                        <select name="category_id" id="category_id" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white" onchange="loadSubCategories(this.value)">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <!-- Sub Category (dynamic) -->
                        <div id="subcategory-wrap" class="mt-2 hidden">
                            <select name="sub_category_id" id="sub_category_id" class="no-select2 w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                                <option value="">Select Sub-Category</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Priority *</label>
                        <div class="flex gap-2">
                            @foreach($priorities as $pri)
                            <label class="flex-1">
                                <input type="radio" name="priority_id" value="{{ $pri->id }}" class="peer hidden" {{ old('priority_id') == $pri->id ? 'checked' : '' }}>
                                <div class="border-2 border-gray-200 rounded-lg py-2.5 text-center cursor-pointer transition text-sm font-medium text-gray-600 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 hover:border-gray-300">
                                    {{ $pri->name }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('priority_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Type & Impact -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Request Type *</label>
                        <select name="type" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                            <option value="incident" {{ old('type','incident') == 'incident' ? 'selected' : '' }}>🚨 Incident</option>
                            <option value="service_request" {{ old('type') == 'service_request' ? 'selected' : '' }}>🛠 Service Request</option>
                            <option value="problem" {{ old('type') == 'problem' ? 'selected' : '' }}>🔍 Problem</option>
                            <option value="change_request" {{ old('type') == 'change_request' ? 'selected' : '' }}>📝 Change Request</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Impact</label>
                        <select name="impact" class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                            <option value="low">Low - Just me</option>
                            <option value="medium">Medium - Team/Department</option>
                            <option value="high">High - Many users</option>
                            <option value="critical">Critical - Entire organization</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Full Description *</label>
                    <textarea name="description" rows="5" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                        placeholder="Describe your issue in detail. Include steps already taken...">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Attachments -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Upload Attachments</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-brand-400 transition cursor-pointer relative group">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-gray-100 group-hover:bg-brand-50 rounded-full flex items-center justify-center mb-3 transition">
                                <i class="fas fa-cloud-upload-alt text-xl text-gray-400 group-hover:text-brand-500 transition"></i>
                            </div>
                            <p class="text-sm text-gray-600 font-medium">Click to upload or drag files here</p>
                            <p class="text-xs text-gray-400 mt-1">PNG, JPG, PDF up to 10MB</p>
                        </div>
                        <input type="file" name="attachments[]" multiple class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('tickets.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
                    Submit Ticket <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Sidebar (Right 1/3) -->
        <div class="space-y-5">
            <!-- Location & Vessel -->
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Additional Info</h4>
                
                <!-- BAGIAN YANG DITAMBAHKAN: READ ONLY COMPANY -->
                <div class="mb-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dari Perusahaan : </label>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ auth()->user()->company->name ?? 'Perusahaan Tidak Terdeteksi' }}
                    </p>
                </div>
                <!-- AKHIR BAGIAN YANG DITAMBAHKAN -->

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Location</label>
                        <input type="text" name="location" value="{{ old('location') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Floor/Room">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Vessel</label>
                        <input type="text" name="vessel_name" value="{{ old('vessel_name') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Vessel name (if applicable)">
                        <p class="text-[11px] text-gray-400 mt-1">Leave blank if not from a vessel</p>
                    </div>
                </div>
            </div>

            <!-- Related Assets -->
            @if($assets->count() > 0)
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Related Assets</h4>
                <select name="assets[]" multiple class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    @foreach($assets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->asset_tag }} - {{ $asset->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1.5">Type to search for an asset</p>
            </div>
            @endif

            <!-- Help Tips -->
            <div class="bg-blue-50 rounded-xl border border-blue-100 p-5">
                <h4 class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-3">
                    <i class="fas fa-lightbulb text-blue-500 mr-1"></i> Tips
                </h4>
                <ul class="space-y-2 text-xs text-blue-700">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-blue-400 mt-0.5"></i>
                        <span>Describe the issue in detail so technicians can help faster</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-blue-400 mt-0.5"></i>
                        <span>Attach relevant screenshots or files</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-blue-400 mt-0.5"></i>
                        <span>Choose the priority that matches your issue's urgency</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-blue-400 mt-0.5"></i>
                        <span>Change Requests require approval from IT Head</span>
                    </li>
                </ul>
            </div>

            <!-- SLA Info -->
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">SLA Targets</h4>
                <div class="space-y-2.5">
                    @foreach($priorities as $pri)
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-xs text-gray-600">
                            <span class="w-2 h-2 rounded-full" style="background:{{ $pri->color }}"></span>
                            {{ $pri->name }}
                        </span>
                        <span class="text-xs font-medium text-gray-800">{{ $pri->sla_hours }}h resolution</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function loadSubCategories(categoryId) {
    const wrap = document.getElementById('subcategory-wrap');
    const select = document.getElementById('sub_category_id');

    if (!categoryId) {
        wrap.classList.add('hidden');
        select.innerHTML = '<option value="">Select Sub-Category</option>';
        return;
    }

    fetch('/api/subcategories/' + categoryId)
        .then(r => r.json())
        .then(data => {
            if (data.length > 0) {
                select.innerHTML = '<option value="">Select Sub-Category</option>';
                data.forEach(sub => {
                    select.innerHTML += '<option value="' + sub.id + '">' + sub.name + '</option>';
                });
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
            }
        })
        .catch(() => wrap.classList.add('hidden'));
}
</script>
@endpush