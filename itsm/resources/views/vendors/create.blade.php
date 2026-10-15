@extends('layouts.app')
@section('title', 'Add New Vendor')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-gray-100 p-6 shadow-xs">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Add New Vendor</h2>

    <form action="{{ route('admin.vendors.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Vendor Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. PT Satcom Maritime" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Vendor Code</label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. VND-SAT" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Contact Person (PIC)</label>
                <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="PIC Name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="support@vendor.com" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Phone / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+62812xxxxxxxx" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Address</label>
            <textarea name="address" rows="3" placeholder="Vendor office address..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('address') }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" id="is_active" checked class="rounded border-gray-300 text-brand-600">
            <label for="is_active" class="text-sm text-gray-700 font-medium">Active</label>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('admin.vendors.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-semibold hover:bg-brand-600">Save Vendor</button>
        </div>
    </form>
</div>
@endsection