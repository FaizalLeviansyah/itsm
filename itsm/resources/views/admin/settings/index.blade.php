@extends('layouts.app')
@section('title', 'Settings')
@section('header', 'Admin Settings')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <a href="{{ route('admin.settings.users') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition">
            <i class="fas fa-users text-blue-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">User Management</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola user, role, dan status akun</p>
    </a>

    <a href="{{ route('admin.settings.categories') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-green-200 transition">
            <i class="fas fa-tags text-green-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Kategori Ticket</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola kategori dan sub-kategori tiket</p>
    </a>

    <a href="{{ route('admin.settings.priorities') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-orange-200 transition">
            <i class="fas fa-flag text-orange-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Prioritas & SLA</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola level prioritas dan target SLA</p>
    </a>

    <a href="{{ route('admin.settings.asset-categories') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-200 transition">
            <i class="fas fa-layer-group text-purple-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Kategori Asset</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola kategori perangkat/asset</p>
    </a>

    <a href="{{ route('admin.settings.holidays') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-red-200 transition">
            <i class="fas fa-calendar-alt text-red-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">SLA Calendar</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola hari libur untuk perhitungan SLA</p>
    </a>

    <a href="{{ route('admin.settings.escalation') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-200 transition">
            <i class="fas fa-level-up-alt text-indigo-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Escalation Rules</h3>
        <p class="text-sm text-gray-500 mt-1">Atur auto-escalation jika tidak ada respons</p>
    </a>

    <a href="{{ route('admin.settings.companies') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-200 transition">
            <i class="fas fa-building text-cyan-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Companies</h3>
        <p class="text-sm text-gray-500 mt-1">Kelola multi-company, logo, dan asset prefix</p>
    </a>

    <a href="{{ route('admin.settings.auto-assign') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-emerald-200 transition">
            <i class="fas fa-robot text-emerald-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Auto-Assign</h3>
        <p class="text-sm text-gray-500 mt-1">Ticket otomatis ke teknisi berdasarkan kategori</p>
    </a>

    <a href="{{ route('admin.settings.canned-responses') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-pink-200 transition">
            <i class="fas fa-comment-dots text-pink-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Canned Responses</h3>
        <p class="text-sm text-gray-500 mt-1">Template jawaban cepat untuk teknisi</p>
    </a>

    <a href="{{ route('admin.settings.maintenance') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-teal-200 transition">
            <i class="fas fa-wrench text-teal-600 text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Maintenance Schedule</h3>
        <p class="text-sm text-gray-500 mt-1">Jadwal preventive maintenance asset</p>
    </a>
</div>
@endsection
