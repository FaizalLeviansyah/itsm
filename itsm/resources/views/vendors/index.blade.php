@extends('layouts.app')
@section('title', 'Vendor Management')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Third-Party & Vendor Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage third-party lists for operational support (Satcom, Starlink, Dahua, etc.)</p>
    </div>
    <a href="{{ route('admin.vendors.create') }}" class="bg-brand-500 hover:bg-brand-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Add Vendor
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-xs">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50/50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-6 py-3">Vendor Name</th>
                <th class="px-6 py-3">Code</th>
                <th class="px-6 py-3">Contact Person</th>
                <th class="px-6 py-3">Email / Phone</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 text-sm">
            @forelse($vendors as $vendor)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-6 py-4 font-semibold text-gray-900">{{ $vendor->name }}</td>
                <td class="px-6 py-4 text-gray-600"><span class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $vendor->code ?? '-' }}</span></td>
                <td class="px-6 py-4 text-gray-600">{{ $vendor->contact_person ?? '-' }}</td>
                <td class="px-6 py-4 text-gray-600">
                    <div>{{ $vendor->email ?? '-' }}</div>
                    <div class="text-xs text-gray-400">{{ $vendor->phone ?? '-' }}</div>
                </td>
                <td class="px-6 py-4">
                    @if($vendor->is_active)
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200">Active</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-200">Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('admin.vendors.edit', $vendor) }}" class="text-brand-600 hover:text-brand-800 text-xs font-semibold">Edit</a>
                    <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this vendor?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-400">No vendor data available. Please add a new vendor.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection