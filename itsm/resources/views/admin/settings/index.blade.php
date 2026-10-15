@extends('layouts.app')
@section('title', 'Settings')
@section('header', 'Admin Settings')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <!-- User Management -->
    <a href="{{ route('admin.settings.users') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition">
            <i class="fas fa-users text-blue-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">User Management</h3>
        <p class="text-sm text-gray-500 mt-1">Manage users, roles, and account statuses</p>
    </a>

    <!-- Third-Party / Vendors -->
    <a href="{{ route('admin.vendors.index') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-200 transition">
            <i class="fas fa-building-user text-indigo-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Third-Party & Vendors</h3>
        <p class="text-sm text-gray-500 mt-1">Manage vendor data (Satcom, Starlink, Dahua, etc.)</p>
    </a>

    <!-- Companies -->
    <a href="{{ route('admin.settings.companies') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-200 transition">
            <i class="fas fa-building text-cyan-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Companies</h3>
        <p class="text-sm text-gray-500 mt-1">Manage multi-company profiles, logos, and asset prefixes</p>
    </a>

    <!-- Ticket Categories -->
    <a href="{{ route('admin.settings.categories') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-green-200 transition">
            <i class="fas fa-tags text-green-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Ticket Categories</h3>
        <p class="text-sm text-gray-500 mt-1">Manage ticket categories and sub-categories</p>
    </a>

    <!-- Priorities & SLA -->
    <a href="{{ route('admin.settings.priorities') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-orange-200 transition">
            <i class="fas fa-flag text-orange-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Priorities & SLA</h3>
        <p class="text-sm text-gray-500 mt-1">Manage priority levels and SLA targets</p>
    </a>

    <!-- Asset Categories -->
    <a href="{{ route('admin.settings.asset-categories') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-200 transition">
            <i class="fas fa-layer-group text-purple-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Asset Categories</h3>
        <p class="text-sm text-gray-500 mt-1">Manage hardware and equipment categories</p>
    </a>

    <!-- SLA Calendar -->
    <a href="{{ route('admin.settings.holidays') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-red-200 transition">
            <i class="fas fa-calendar-alt text-red-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">SLA Calendar</h3>
        <p class="text-sm text-gray-500 mt-1">Manage holidays and non-working days for SLA calculations</p>
    </a>

    <!-- Escalation Rules -->
    <a href="{{ route('admin.settings.escalation') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-200 transition">
            <i class="fas fa-level-up-alt text-indigo-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Escalation Rules</h3>
        <p class="text-sm text-gray-500 mt-1">Configure auto-escalation rules for unassigned/unresponsive tickets</p>
    </a>

    <!-- Auto-Assign -->
    <a href="{{ route('admin.settings.auto-assign') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-emerald-200 transition">
            <i class="fas fa-robot text-emerald-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Auto-Assign</h3>
        <p class="text-sm text-gray-500 mt-1">Automated ticket routing to technicians by category</p>
    </a>

    <!-- Canned Responses -->
    <a href="{{ route('admin.settings.canned-responses') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-pink-200 transition">
            <i class="fas fa-comment-dots text-pink-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Canned Responses</h3>
        <p class="text-sm text-gray-500 mt-1">Quick reply templates for technical support staff</p>
    </a>

    <!-- Maintenance Schedule -->
    <a href="{{ route('admin.settings.maintenance') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-teal-200 transition">
            <i class="fas fa-wrench text-teal-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Maintenance Schedule</h3>
        <p class="text-sm text-gray-500 mt-1">Preventive maintenance scheduling for assets and vessels</p>
    </a>
</div>
@endsection