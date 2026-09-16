@extends('layouts.app')
@section('title', 'Asset Audit')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Asset Audit</h1>
        <p class="text-sm text-gray-500 mt-1">Verify asset existence and condition through periodic audits</p>
    </div>
    <a href="{{ route('audits.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-semibold transition">
        <i class="fas fa-plus text-xs"></i> New Audit
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Audit</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Status</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Location</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase px-6 py-3">Progress</th>
                    <th class="text-center text-xs font-semibold text-gray-500 uppercase px-6 py-3">Assets</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Auditor</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audits as $audit)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 cursor-pointer" onclick="window.location='{{ route('audits.show', $audit) }}'">
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-brand-600">{{ $audit->audit_number }}</p>
                        <p class="text-xs text-gray-500">{{ Str::limit($audit->title, 35) }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @php $asc = ['planned'=>'bg-gray-50 text-gray-600 border-gray-200','in_progress'=>'bg-blue-50 text-blue-700 border-blue-200','completed'=>'bg-green-50 text-green-700 border-green-200','cancelled'=>'bg-red-50 text-red-700 border-red-200']; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium border {{ $asc[$audit->status] ?? '' }}">{{ ucfirst(str_replace('_',' ',$audit->status)) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $audit->location ?? $audit->vessel_name ?? '-' }}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-16 bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full bg-brand-500" style="width:{{ $audit->progress_percent }}%"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-600">{{ $audit->progress_percent }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center text-sm font-medium text-gray-800">{{ $audit->total_assets }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $audit->auditor->name }}</td>
                    <td class="px-6 py-4 text-xs text-gray-500">{{ $audit->scheduled_date->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <i class="fas fa-clipboard-check text-4xl text-gray-200 mb-3"></i>
                        <p class="text-gray-500 font-medium">No audits yet</p>
                        <p class="text-sm text-gray-400 mt-1">Create a new audit to verify assets</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($audits->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $audits->links() }}</div>
    @endif
</div>
@endsection
