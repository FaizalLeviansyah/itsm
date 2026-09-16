@extends('layouts.app')
@section('title', 'Service Catalog')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Service Catalog</h1>
    <p class="text-sm text-gray-500 mt-1">Available IT services. Select a service to create a request.</p>
</div>

@forelse($services as $categoryName => $items)
<div class="mb-8">
    <h2 class="text-base font-semibold text-gray-800 mb-4">{{ $categoryName }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($items as $service)
        <a href="{{ route('tickets.create', ['service' => $service->id]) }}" class="bg-white rounded-xl border border-gray-100 p-5 hover:shadow-md hover:border-brand-200 transition group">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-brand-50 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-brand-100 transition">
                    <i class="fas fa-{{ $service->icon ?? 'concierge-bell' }} text-brand-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ $service->name }}</h3>
                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $service->description }}</p>
                    <div class="flex items-center gap-3 mt-3 text-[10px] text-gray-400">
                        <span><i class="fas fa-clock mr-0.5"></i> SLA: {{ $service->sla_hours }}h</span>
                        @if($service->approval_required !== 'none')
                        <span class="text-amber-600"><i class="fas fa-check-double mr-0.5"></i> Approval required</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@empty
<div class="bg-white rounded-xl border border-gray-100 p-16 text-center">
    <i class="fas fa-concierge-bell text-4xl text-gray-200 mb-3"></i>
    <p class="text-gray-500 font-medium">Service catalog is empty</p>
    <p class="text-sm text-gray-400 mt-1">Admins can add services in Settings</p>
</div>
@endforelse
@endsection
