@extends('layouts.app')
@section('title', 'Edit Vendor')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-gray-100 p-6 shadow-xs">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Edit Vendor Information</h2>

    <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Vendor Name *</label>
            <input type="text" name="name" value="{{ old('name', $vendor->name) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Vendor Code</label>
                <input type="text" name="code" value="{{ old('code', $vendor->code) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Contact Person (PIC)</label>
                <input type="text" name="contact_person" value="{{ old('contact_person', $vendor->contact_person) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $vendor->email) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Phone / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Address</label>
            <textarea name="address" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('address', $vendor->address) }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ $vendor->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600">
            <label for="is_active" class="text-sm text-gray-700 font-medium">Active</label>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('admin.vendors.index') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-semibold hover:bg-brand-600">Update Vendor</button>
        </div>
    </form>
</div>
@endsection