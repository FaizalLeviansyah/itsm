@extends('layouts.app')
@section('title', 'Auto-Assign Rules')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Auto-Assign Rules</h1>
    <p class="text-sm text-gray-500 mt-1">Ticket otomatis ditugaskan ke teknisi berdasarkan kategori, company, vessel, atau sumber user.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Tambah Rule</h3>
        <form action="{{ route('admin.settings.auto-assign.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Kategori *</label>
                <select name="category_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Sub-Kategori</label>
                <select name="sub_category_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">Semua (opsional)</option>
                    @foreach($categories as $cat)
                        @foreach($cat->subCategories as $sub)
                        <option value="{{ $sub->id }}">{{ $cat->name }} → {{ $sub->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Company</label>
                <select name="company_id" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="">Semua Company (opsional)</option>
                    @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
                <p class="text-[10px] text-gray-400 mt-0.5">Hanya ticket dari company ini</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Vessel</label>
                <input type="text" name="vessel_name" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm" placeholder="Nama vessel (opsional)">
                <p class="text-[10px] text-gray-400 mt-0.5">Hanya ticket dari vessel ini</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Sumber User</label>
                <select name="source_type" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    <option value="all">Semua (Employee + Vessel)</option>
                    <option value="employee">Employee saja</option>
                    <option value="vessel">Vessel crew saja</option>
                </select>
                <p class="text-[10px] text-gray-400 mt-0.5">Misal: ticket dari vessel → assign ke teknisi marine</p>
            </div>
            <hr class="border-gray-100">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Assign ke *</label>
                <select name="assign_to" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-white">
                    @foreach($technicians as $tech)<option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>@endforeach
                </select>
            </div>
            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white py-2.5 rounded-lg text-sm font-semibold transition">Tambah Rule</button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-5">
        <!-- Info box -->
        <div class="bg-blue-50 rounded-xl border border-blue-100 p-4">
            <h4 class="text-xs font-semibold text-blue-700 mb-1"><i class="fas fa-lightbulb mr-1"></i> Cara Kerja</h4>
            <p class="text-xs text-blue-600 leading-relaxed">Rule yang lebih spesifik diprioritas duluan. Misal: rule "Network + Vessel ETERNAL OIL" lebih prioritas dari rule "Network saja". Jika tidak ada yang cocok, ticket tetap open tanpa assign.</p>
        </div>

        <!-- Rules List -->
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Active Rules</h3>
            <div class="space-y-3">
                @forelse($rules as $rule)
                <div class="flex items-start justify-between p-4 border border-gray-100 rounded-lg">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-medium bg-brand-50 text-brand-700 px-2 py-0.5 rounded">{{ $rule->category->name }}</span>
                            @if($rule->subCategory)
                            <span class="text-xs text-gray-400">→</span>
                            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $rule->subCategory->name }}</span>
                            @endif
                            @if($rule->company)
                            <span class="text-xs bg-cyan-50 text-cyan-700 px-2 py-0.5 rounded"><i class="fas fa-building mr-0.5"></i>{{ $rule->company->name }}</span>
                            @endif
                            @if($rule->vessel_name)
                            <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded"><i class="fas fa-ship mr-0.5"></i>{{ $rule->vessel_name }}</span>
                            @endif
                            @if($rule->source_type !== 'all')
                            <span class="text-xs bg-purple-50 text-purple-700 px-2 py-0.5 rounded"><i class="fas fa-user mr-0.5"></i>{{ ucfirst($rule->source_type) }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mt-1.5">→ Assign ke <span class="font-semibold">{{ $rule->assignee->name }}</span></p>
                    </div>
                    <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Active</span>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-8">Belum ada rule. Tambahkan rule pertama di form sebelah.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
