@extends('layouts.app')
@section('title', 'New Audit')

@section('content')
<div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
    <a href="{{ route('audits.index') }}" class="hover:text-brand-600">Audit</a>
    <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
    <span class="text-brand-600 font-medium">Create New Audit</span>
</div>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Create New Audit</h1>
    <p class="text-sm text-gray-500 mt-1">Select the audit scope — all assets matching the filters will be added to the audit checklist.</p>
</div>

<form action="{{ route('audits.store') }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Audit Title *</label>
                    <input type="text" name="title" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g. Audit IT Asset Q3 2026 - Head Office">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Scheduled Date *</label>
                        <input type="date" name="scheduled_date" required value="{{ now()->format('Y-m-d') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Company</label>
                        <select name="company_id" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="">All Companies</option>
                            @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Location (filter)</label>
                        <input type="text" name="location" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g. Head Office">
                        <p class="text-[11px] text-gray-400 mt-0.5">Leave blank for all locations</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Vessel (filter)</label>
                        <input type="text" name="vessel_name" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="e.g. ETERNAL OIL I">
                        <p class="text-[11px] text-gray-400 mt-0.5">Leave blank if not a vessel audit</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Instructions or notes for the auditor..."></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('audits.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-clipboard-check"></i> Create Audit
                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-5">
            <!-- How it works -->
            <div class="bg-blue-50 rounded-xl border border-blue-100 p-5">
                <h4 class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-3">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i> How Audit Works
                </h4>
                <ol class="text-xs text-blue-700 space-y-2 list-decimal list-inside leading-relaxed">
                    <li>Create audit → system auto-generates asset checklist based on filters</li>
                    <li>Start audit → go to the asset location</li>
                    <li>Scan QR code on asset sticker → automatically recorded as "found"</li>
                    <li>Update condition (good/fair/poor/damaged/missing) per asset</li>
                    <li>Complete audit → view result report</li>
                </ol>
            </div>

            <!-- Filter Explanation -->
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                    <i class="fas fa-filter text-gray-400 mr-1"></i> About Filters
                </h4>
                <ul class="space-y-2 text-xs text-gray-500 leading-relaxed">
                    <li><span class="font-medium text-gray-700">Company</span> — Only assets belonging to a specific company</li>
                    <li><span class="font-medium text-gray-700">Location</span> — Filter by placement location</li>
                    <li><span class="font-medium text-gray-700">Vessel</span> — Only assets on a specific vessel</li>
                    <li class="pt-1 border-t border-gray-100 text-gray-400">If all filters are empty, all active assets will be included in the checklist</li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection
