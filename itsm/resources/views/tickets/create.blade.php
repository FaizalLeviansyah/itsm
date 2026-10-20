@extends('layouts.app')
@section('title', 'Create New Ticket')

@push('styles')
<!-- Select2 CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Custom Styling Select2 agar serasi dengan Tailwind UI */
    .select2-container--default .select2-selection--single {
        border-color: #fcd34d !important;
        border-width: 2px !important;
        border-radius: 0.5rem !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
    }
</style>
@endpush

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
                <!-- BAGIAN YANG DITAMBAHKAN: On Behalf Of (Khusus Admin/Teknisi) -->
                    @if(in_array(auth()->user()->role, ['admin', 'technician']))
                    <div class="p-4 bg-amber-50/80 border-2 border-amber-300 rounded-xl shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-amber-900 uppercase tracking-wide flex items-center gap-1.5">
                                <i class="fas fa-user-friends text-amber-600"></i> Request On Behalf Of (Opsional)
                            </label>
                            <!-- Filter Toggle Office / Vessel dengan Warna Kontras & Elegan -->
                            <select onchange="filterUserLocation(this.value)" class="text-xs font-bold border-2 border-amber-400 rounded-lg px-3 py-1.5 bg-amber-500 text-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 cursor-pointer shadow">
                                <option value="all" class="bg-white text-gray-800 font-normal">📁 Semua User</option>
                                <option value="office" class="bg-white text-gray-800 font-normal">🏢 Office (On-Shore)</option>
                                <option value="vessel" class="bg-white text-gray-800 font-normal">🚢 Vessel (Off-Shore)</option>
                            </select>
                        </div>
                        
                       <!-- On Behalf Select (Dengan event Listener onchange) -->
                       <!-- On Behalf Select dengan Search Box Select2 -->
                       <select name="requester_id" id="on_behalf_select" class="w-full select2-searchable" onchange="handleRequesterChange(this)" {{ old('vendor_id') ? 'disabled' : '' }}>
                            <option value="" data-location="all" data-company="{{ auth()->user()->company->name ?? 'PT Amarin Ship Management' }}" data-requester-name="{{ auth()->user()->name }}" data-is-vendor="false" data-user-type="Office Employee">-- Buat Tiket Untuk Diri Sendiri ({{ auth()->user()->name }}) --</option>
                            @foreach(\App\Models\User::where('id', '!=', auth()->id())->where('is_active', true)->orderBy('name')->get() as $u)
                                @php
                                    $isVendor = str_contains(strtolower($u->email), 'vendor') || ($u->company && str_contains(strtolower($u->company->name), 'vendor')) || str_contains(strtolower($u->position ?? ''), 'vendor');
                                    $isVessel = str_contains(strtolower($u->name), 'mt ') || str_contains(strtolower($u->email), 'vessel') || str_contains(strtolower($u->position ?? ''), 'vessel');
                                    
                                    $locationType = $isVendor ? 'vendor' : ($isVessel ? 'vessel' : 'office');
                                    
                                    $displayName = $u->name;
                                    if (preg_match('/^[P]?\d+$/', $displayName)) {
                                        $displayName = !empty($u->position) && !preg_match('/^[P]?\d+$/', $u->position) 
                                            ? $u->position 
                                            : ucwords(str_replace(['.', '_'], ' ', explode('@', $u->email)[0]));
                                    }

                                    $jobTitle = $u->job_title ?? ($u->position ?? '');
                                    $companyName = $u->company->name ?? 'PT Amarin Ship Management';
                                    
                                    $optionText = $displayName;
                                    if ($jobTitle && strtolower($jobTitle) !== strtolower($displayName)) { 
                                        $optionText .= ' - ' . $jobTitle; 
                                    }
                                    if (!empty($u->email)) { 
                                        $optionText .= ' (' . $u->email . ')'; 
                                    }
                                    $optionText .= ' [' . $companyName . ']';
                                @endphp
                                <option value="{{ $u->id }}" data-location="{{ $locationType }}" data-company="{{ $companyName }}" data-requester-name="{{ $displayName }}" data-is-vendor="{{ $isVendor ? 'true' : 'false' }}" data-user-type="{{ $isVendor ? 'Vendor / External Partner' : ($isVessel ? 'Vessel Crew' : 'Office Employee') }}" {{ old('requester_id') == $u->id ? 'selected' : '' }}>
                                    {{ $optionText }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Sidebar Additional Info Company (Dinamis dengan ID display_company_text) -->
                        <!-- Kotak Informasi PT di bawah dropdown On Behalf -->
                        <div class="mt-3 bg-white/90 p-2.5 rounded-lg border border-amber-200">
                            <span class="text-[11px] font-medium text-amber-900">From Company : </span>
                            <span class="text-xs font-bold text-brand-600 company-display-text">
                                {{ auth()->user()->company->name ?? 'PT Amarin Ship Management' }}
                            </span>
                        </div>
                        
                        <p class="text-[11px] text-amber-800 font-medium mt-2">
                            💡 *Tip: Type a name, email address, or job title to perform a quick search. Tickets under another user's name will require <strong>Approval</strong>.*
                        </p>
                    </div>
                @endif
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
                        <select name="request_type" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                            <option value="incident" {{ old('request_type','incident') == 'incident' ? 'selected' : '' }}>🚨 Incident</option>
                            <option value="service_request" {{ old('request_type') == 'service_request' ? 'selected' : '' }}>🛠 Service Request</option>
                            <option value="problem" {{ old('request_type') == 'problem' ? 'selected' : '' }}>🔍 Problem</option>
                            <option value="change_request" {{ old('request_type') == 'change_request' ? 'selected' : '' }}>📝 Change Request</option>
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
                        <div class="flex flex-col items-center pointer-events-none">
                            <div class="w-12 h-12 bg-gray-100 group-hover:bg-brand-50 rounded-full flex items-center justify-center mb-3 transition">
                                <i class="fas fa-cloud-upload-alt text-xl text-gray-400 group-hover:text-brand-500 transition"></i>
                            </div>
                            <p class="text-sm text-gray-600 font-medium">Click to upload or drag files here</p>
                            <p class="text-xs text-gray-400 mt-1">PNG, JPG, PDF, MP4, AVI up to 20MB</p>
                        </div>
                        <input type="file" id="file-upload" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.mp4,.mov,.avi" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFiles()">
                    </div>
                    
                    <!-- Kontainer Preview File -->
                    <div id="file-preview-container" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 hidden"></div>
                    
                    @error('attachments.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                
                <!-- Sidebar Additional Info (Full Adaptive UX CLEAR) -->
                <!-- Sidebar Additional Info (English UI & Full Requester Info) -->
                <div class="mb-4 bg-gray-50 p-3.5 rounded-lg border border-gray-200 space-y-3">
                    <!-- Requester Name -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-0.5">Ticket Requester :</label>
                        <p class="text-sm font-bold text-gray-900" id="display_requester_name">
                            {{ request('requester_id') ? \App\Models\User::find(request('requester_id'))?->name : auth()->user()->name }}
                        </p>
                    </div>

                    <!-- Requester Company -->
                    <div class="pt-2 border-t border-gray-200/80">
                        <label class="block text-xs font-medium text-gray-500 mb-0.5">From Company :</label>
                        <p class="text-sm font-bold text-brand-600 company-display-text">
                            {{ auth()->user()->company->name ?? 'PT Amarin Ship Management' }}
                        </p>
                    </div>

                    <!-- Requester Type Badge -->
                    <div class="flex items-center justify-between pt-2 border-t border-gray-200/80">
                        <span class="text-xs text-gray-500">Requester Category :</span>
                        <span id="requester_type_badge" class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                            <i class="fas fa-user-check text-[10px]"></i> Office Employee
                        </span>
                    </div>

                    <!-- IT Staff Logged / Created By (Only shown when Admin/Tech logs On-Behalf) -->
                    @if(in_array(auth()->user()->role, ['admin', 'technician']))
                    <div class="pt-2 border-t border-gray-200/80">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 flex items-center gap-1">
                            <i class="fas fa-user-shield text-brand-600"></i> CREATED BY (IT OFFICER):
                        </label>
                        <div class="flex items-center gap-2 bg-white p-2 rounded border border-gray-200 shadow-xs">
                            <div class="w-7 h-7 bg-brand-500 text-white rounded-full flex items-center justify-center text-[10px] font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div class="text-xs">
                                <span class="font-bold text-gray-900 block">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-brand-600 font-semibold block capitalize">IT {{ auth()->user()->role === 'admin' ? 'Administrator' : 'Technician' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

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

                        <!-- DROPDOWN VENDOR / THIRD-PARTY (DENGAN PREVIEW REAL-TIME) -->
            <!-- VENDOR / THIRD-PARTY SOURCE (EKSTERNAL) -->
            <div class="bg-white rounded-xl border-2 border-amber-300 p-5 bg-amber-50/40 shadow-xs">
                <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                    <i class="fas fa-building-user text-amber-600"></i> Vendor / 3rd Party Source
                </h4>
                <select name="vendor_id" id="vendor_select" onchange="handleVendorChange(this)" class="w-full border border-amber-300 rounded-lg px-3 py-2.5 text-sm bg-white font-medium" {{ old('requester_id') ? 'disabled' : '' }}>
                    <option value="">-- Tanpa Vendor (Internal / On Behalf) --</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" 
                                data-name="{{ $v->name }}" 
                                data-code="{{ $v->code ?? '' }}" 
                                data-pic="{{ $v->contact_person ?? '-' }}" 
                                data-phone="{{ $v->phone ?? '-' }}"
                                {{ old('vendor_id') == $v->id ? 'selected' : '' }}>
                            {{ $v->name }} {{ $v->code ? '('.$v->code.')' : '' }}
                        </option>
                    @endforeach
                </select>

                <!-- REAL-TIME DETAIL VENDOR CARD -->
                <div id="vendor_preview_box" class="mt-3 bg-white p-3 rounded-lg border border-amber-300 shadow-xs hidden">
                    <div class="flex items-center justify-between border-b border-amber-100 pb-1.5 mb-2">
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 bg-amber-100 text-amber-900 rounded border border-amber-200">
                            <i class="fas fa-handshake mr-1"></i> DELEGATED VENDOR
                        </span>
                        <span class="text-xs font-bold text-amber-800" id="vendor_code_display"></span>
                    </div>
                    <p class="text-xs font-bold text-gray-900" id="vendor_name_display"></p>
                    <div class="mt-2 pt-2 border-t border-gray-100 text-[11px] text-gray-600 space-y-1">
                        <p class="flex items-center gap-1.5">
                            <i class="fas fa-user-tier text-amber-600 w-3.5"></i> PIC: <span class="font-semibold text-gray-800" id="vendor_pic_display">-</span>
                        </p>
                        <p class="flex items-center gap-1.5">
                            <i class="fas fa-phone-alt text-amber-600 w-3.5"></i> Telp: <span class="font-semibold text-gray-800" id="vendor_phone_display">-</span>
                        </p>
                    </div>
                </div>

                <p class="text-[11px] text-amber-800 mt-2">Pilih ini jika tiket bersumber langsung dari pelaporan/ekskalasi Vendor pihak ketiga.</p>
            </div>

            @if($assets->count() > 0)
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Related Asset</h4>
                <select name="asset_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                    <option value="">-- Select Asset (Optional) --</option>
                    @foreach($assets as $asset)
                    <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                        {{ $asset->asset_tag }} - {{ $asset->name }}
                    </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1.5">Select related device or hardware</p>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let selectedFiles = [];

// Inisialisasi Select2 dengan Search Box Instan & Real-time Color Update
$(document).ready(function() {
    const $select = $('#on_behalf_select').select2({
        placeholder: "-- Type name, email, or job title to search --",
        allowClear: true,
        width: '100%'
    });

    $select.on('change', function() {
        updateOnBehalfCompany(this);
    });
});

function previewFiles() {
    const input = document.getElementById('file-upload');
    const container = document.getElementById('file-preview-container');
    
    // Masukkan file baru ke dalam array penampung
    Array.from(input.files).forEach(file => {
        selectedFiles.push(file);
    });

    updateFileInputAndPreview();
}

function updateOnBehalfCompany(selectEl) {
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    if (!selectedOption) return;

    const companyName   = selectedOption.getAttribute('data-company') || "PT Amarin Ship Management";
    const requesterName = selectedOption.getAttribute('data-requester-name') || "{{ auth()->user()->name }}";
    const isVendor      = selectedOption.getAttribute('data-is-vendor') === 'true';
    const userType      = selectedOption.getAttribute('data-user-type') || 'Office Employee';

    // 1. Update Nama Requester di Sidebar
    const requesterDisplay = document.getElementById('display_requester_name');
    if (requesterDisplay) {
        requesterDisplay.innerText = requesterName;
    }

    // 2. Update Warna Nama Perusahaan (Amarin=Biru, Crewing=Kuning, Caraka=Merah, Vendor=Hijau)
    document.querySelectorAll('.company-display-text').forEach(el => {
        el.innerText = companyName;
        el.classList.remove('text-brand-600', 'text-blue-600', 'text-amber-500', 'text-red-600', 'text-emerald-600');
        
        const lowerComp = companyName.toLowerCase();
        if (isVendor || lowerComp.includes('vendor')) {
            el.classList.add('text-emerald-600');
        } else if (lowerComp.includes('caraka')) {
            el.classList.add('text-red-600');
        } else if (lowerComp.includes('crewing')) {
            el.classList.add('text-amber-500');
        } else {
            el.classList.add('text-blue-600');
        }
    });

    // 3. Update Badge Requester Category
    const badgeEl = document.getElementById('requester_type_badge');
    if (badgeEl) {
        if (isVendor) {
            badgeEl.className = 'inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs animate-pulse';
            badgeEl.innerHTML = '<i class="fas fa-handshake text-[10px]"></i> ' + userType;
        } else if (userType.includes('Vessel')) {
            badgeEl.className = 'inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 shadow-xs';
            badgeEl.innerHTML = '<i class="fas fa-ship text-[10px]"></i> ' + userType;
        } else {
            badgeEl.className = 'inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 shadow-xs';
            badgeEl.innerHTML = '<i class="fas fa-user-check text-[10px]"></i> ' + userType;
        }
    }
}

function filterUserLocation(location) {
    const select = document.getElementById('on_behalf_select');
    const options = select.querySelectorAll('option:not([value=""])'); // Ambil semua opsi kecuali default
    
    // Kembalikan ke default (Diri Sendiri) setiap kali filter diubah
    select.value = "";

    options.forEach(option => {
        if (location === 'all' || option.dataset.location === location) {
            option.hidden = false;
            option.disabled = false;
        } else {
            option.hidden = true;
            option.disabled = true;
        }
    });
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFileInputAndPreview();
}

function updateFileInputAndPreview() {
    const container = document.getElementById('file-preview-container');
    const input = document.getElementById('file-upload');
    
    // Perbarui data pada input file menggunakan DataTransfer
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });
    input.files = dataTransfer.files;

    // Render ulang tampilan preview
    container.innerHTML = '';
    if (selectedFiles.length > 0) {
        container.classList.remove('hidden');
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const isImage = file.type.startsWith('image/');
                const previewContent = isImage 
                    ? `<img src="${e.target.result}" class="w-full h-20 object-cover rounded-lg border border-gray-200">`
                    : `<div class="w-full h-20 bg-gray-100 rounded-lg border border-gray-200 flex flex-col items-center justify-center p-2 text-center">
                           <i class="fas fa-file-alt text-gray-400 text-xl mb-1"></i>
                           <span class="text-[10px] text-gray-500 truncate w-full">${file.name}</span>
                       </div>`;

                const card = document.createElement('div');
                card.className = 'relative group/item bg-white p-1 rounded-lg border border-gray-200';
                card.innerHTML = `
                    ${previewContent}
                    <button type="button" onclick="removeFile(${index})" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow hover:bg-red-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(card);
            }
            reader.readAsDataURL(file);
        });
    } else {
        container.classList.add('hidden');
    }
}

// Function untuk update Live Preview Vendor saat dropdown dipilih
function updateVendorPreview(selectEl) {
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const previewBox = document.getElementById('vendor_preview_box');
    const nameDisplay = document.getElementById('vendor_name_display');
    const codeDisplay = document.getElementById('vendor_code_display');
    const contactDisplay = document.getElementById('vendor_contact_display');

    if (!selectedOption || !selectedOption.value) {
        if (previewBox) previewBox.classList.add('hidden');
        return;
    }

    const vendorName = selectedOption.text.split('(')[0].trim();
    const vendorCode = selectedOption.getAttribute('data-code');
    const vendorPic  = selectedOption.getAttribute('data-pic');
    const vendorPhone = selectedOption.getAttribute('data-phone');

    if (nameDisplay) nameDisplay.innerText = vendorName;
    if (codeDisplay) codeDisplay.innerText = vendorCode ? `(${vendorCode})` : '';
    if (contactDisplay) contactDisplay.innerText = `PIC: ${vendorPic} | Telp: ${vendorPhone}`;

    if (previewBox) previewBox.classList.remove('hidden');
}

// Jalankan saat pertama kali halaman dimuat untuk mengecek jika ada old('vendor_id')
$(document).ready(function() {
    const vendorSelect = document.getElementById('vendor_select');
    if (vendorSelect && vendorSelect.value) {
        updateVendorPreview(vendorSelect);
    }
});

// Logika Eksklusif: Jika Vendor dipilih, update Badge Kuning, Detail Card Vendor, & nonaktifkan On Behalf
function handleVendorChange(vendorSelect) {
    const onBehalfSelect = $('#on_behalf_select');
    const badgeEl = document.getElementById('requester_type_badge');
    const previewBox = document.getElementById('vendor_preview_box');
    
    if (vendorSelect.value) {
        onBehalfSelect.val(null).trigger('change');
        onBehalfSelect.prop('disabled', true);
        
        const selectedOpt = vendorSelect.options[vendorSelect.selectedIndex];
        const vName = selectedOpt.getAttribute('data-name');
        const vCode = selectedOpt.getAttribute('data-code');
        const vPic = selectedOpt.getAttribute('data-pic') || '-';
        const vPhone = selectedOpt.getAttribute('data-phone') || '-';

        // 1. Update Additional Info Sidebar
        document.getElementById('display_requester_name').innerText = `[Vendor Partner] ${vName} ${vCode ? '('+vCode+')' : ''}`;
        
        // From Company tetap menggunakan Perusahaan User / Vendor info
        const compText = document.querySelector('.company-display-text');
        if (compText) {
            compText.innerText = `Third-Party Vendor (${vName})`;
            compText.className = 'text-sm font-bold text-amber-600 company-display-text';
        }

        // 2. Update Requester Category Badge ke Kuning/Amber
        if (badgeEl) {
            badgeEl.className = 'inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 shadow-xs';
            badgeEl.innerHTML = '<i class="fas fa-handshake text-[10px]"></i> Third-Party / Vendor';
        }

        // 3. Update Real-time Detail Card Vendor di Bawah Dropdown
        document.getElementById('vendor_name_display').innerText = vName;
        document.getElementById('vendor_code_display').innerText = vCode ? `(${vCode})` : '';
        document.getElementById('vendor_pic_display').innerText = vPic;
        document.getElementById('vendor_phone_display').innerText = vPhone;
        if (previewBox) previewBox.classList.remove('hidden');

    } else {
        onBehalfSelect.prop('disabled', false);
        document.getElementById('display_requester_name').innerText = "{{ auth()->user()->name }}";
        
        const compText = document.querySelector('.company-display-text');
        if (compText) {
            compText.innerText = "{{ auth()->user()->company->name ?? 'PT Amarin Ship Management' }}";
            compText.className = 'text-sm font-bold text-brand-600 company-display-text';
        }

        // Reset Badge ke Office Employee (Biru)
        if (badgeEl) {
            badgeEl.className = 'inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200';
            badgeEl.innerHTML = '<i class="fas fa-user-check text-[10px]"></i> Office Employee';
        }

        // Sembunyikan Detail Card Vendor
        if (previewBox) previewBox.classList.add('hidden');
    }
}

// Logika Eksklusif: Jika On Behalf dipilih, kembalikan state
function handleRequesterChange(selectEl) {
    const vendorSelect = document.getElementById('vendor_select');
    const badgeEl = document.getElementById('requester_type_badge');
    const previewBox = document.getElementById('vendor_preview_box');
    const selectedOpt = selectEl.options[selectEl.selectedIndex];

    if (selectEl.value) {
        vendorSelect.value = "";
        vendorSelect.disabled = true;
        if (previewBox) previewBox.classList.add('hidden');

        const reqName = selectedOpt.getAttribute('data-requester-name');
        const compName = selectedOpt.getAttribute('data-company');
        const userType = selectedOpt.getAttribute('data-user-type') || 'Office Employee';
        const isVessel = userType.includes('Vessel');

        document.getElementById('display_requester_name').innerText = reqName;
        
        const compText = document.querySelector('.company-display-text');
        if (compText) compText.innerText = compName;

        // Update Badge Sesuai User On Behalf
        if (badgeEl) {
            if (isVessel) {
                badgeEl.className = 'inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 shadow-xs';
                badgeEl.innerHTML = '<i class="fas fa-ship text-[10px]"></i> ' + userType;
            } else {
                badgeEl.className = 'inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 shadow-xs';
                badgeEl.innerHTML = '<i class="fas fa-user-check text-[10px]"></i> ' + userType;
            }
        }
    } else {
        vendorSelect.disabled = false;
        document.getElementById('display_requester_name').innerText = "{{ auth()->user()->name }}";
        
        const compText = document.querySelector('.company-display-text');
        if (compText) compText.innerText = "{{ auth()->user()->company->name ?? 'PT Amarin Ship Management' }}";

        if (badgeEl) {
            badgeEl.className = 'inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200';
            badgeEl.innerHTML = '<i class="fas fa-user-check text-[10px]"></i> Office Employee';
        }
    }
}

// Cek otomatis saat halaman dimuat jika ada old('vendor_id')
$(document).ready(function() {
    const vendorSelect = document.getElementById('vendor_select');
    if (vendorSelect && vendorSelect.value) {
        handleVendorChange(vendorSelect);
    }
});c

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