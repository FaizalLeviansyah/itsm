@extends('layouts.app')
@section('title', 'Import Assets')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Import Assets from CSV/Excel</h1>
    <p class="text-sm text-gray-500 mt-1">Upload a file to import multiple assets at once.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-6">
        <form action="{{ route('assets.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Company</label>
                <select name="company_id" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm bg-white">
                    <option value="">No Company</option>
                    @foreach(\App\Models\Company::where('is_active', true)->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-0.5">Asset tag will be generated based on the company</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">File CSV/Excel *</label>
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-brand-400 transition cursor-pointer relative group">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 bg-gray-100 group-hover:bg-green-50 rounded-full flex items-center justify-center mb-3 transition">
                            <i class="fas fa-file-excel text-xl text-gray-400 group-hover:text-green-500 transition"></i>
                        </div>
                        <p class="text-sm text-gray-600 font-medium">Click to select a file or drag it here</p>
                        <p class="text-xs text-gray-400 mt-1">CSV, XLS, XLSX — max 5MB</p>
                    </div>
                    <input type="file" name="file" required accept=".csv,.xlsx,.xls" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t">
                <a href="{{ route('assets.import.template') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                    <i class="fas fa-download mr-1"></i> Download Template CSV
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition">
                    <i class="fas fa-upload mr-1"></i> Import
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-5">
        <div class="bg-blue-50 rounded-xl border border-blue-100 p-5">
            <h4 class="text-xs font-semibold text-blue-700 uppercase mb-3"><i class="fas fa-info-circle mr-1"></i> Format File</h4>
            <p class="text-xs text-blue-600 mb-2">Supported columns:</p>
            <ul class="text-xs text-blue-700 space-y-1">
                <li><code class="bg-blue-100 px-1 rounded">name</code> * (required)</li>
                <li><code class="bg-blue-100 px-1 rounded">category</code> — category name</li>
                <li><code class="bg-blue-100 px-1 rounded">manufacturer</code></li>
                <li><code class="bg-blue-100 px-1 rounded">model</code></li>
                <li><code class="bg-blue-100 px-1 rounded">serial_number</code></li>
                <li><code class="bg-blue-100 px-1 rounded">status</code> — available/in_use/maintenance</li>
                <li><code class="bg-blue-100 px-1 rounded">location</code></li>
                <li><code class="bg-blue-100 px-1 rounded">vessel</code></li>
                <li><code class="bg-blue-100 px-1 rounded">ip_address</code></li>
                <li><code class="bg-blue-100 px-1 rounded">mac_address</code></li>
                <li><code class="bg-blue-100 px-1 rounded">notes</code></li>
            </ul>
        </div>
    </div>
</div>
@endsection
