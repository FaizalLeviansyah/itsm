@extends('layouts.app')
@section('title', 'Approvals')
@section('header', 'Change Request Approvals')

@section('content')
<!-- Pending Approvals -->
@if($pendingApprovals->count() > 0)
<div class="mb-8">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Awaiting Approval ({{ $pendingApprovals->count() }})</h3>
    <div class="space-y-4">
        @foreach($pendingApprovals as $approval)
        <div class="bg-white rounded-xl border border-orange-200 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-sm font-medium text-primary-600">{{ $approval->ticket->ticket_number }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Pending Approval</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: {{ $approval->ticket->priority->color }}20; color: {{ $approval->ticket->priority->color }}">{{ $approval->ticket->priority->name }}</span>
                    </div>
                    <h4 class="text-base font-semibold text-gray-800">{{ $approval->ticket->title }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($approval->ticket->description, 200) }}</p>
                    <div class="flex items-center space-x-4 mt-3 text-xs text-gray-500">
                        <span><i class="fas fa-user mr-1"></i>{{ $approval->ticket->requester->name }}</span>
                        <span><i class="fas fa-folder mr-1"></i>{{ $approval->ticket->category->name }}</span>
                        <span><i class="fas fa-clock mr-1"></i>{{ $approval->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-3 mt-4 pt-4 border-t">
                <form action="{{ route('approvals.approve', $approval) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-check mr-1"></i> Approve
                    </button>
                </form>
                <form action="{{ route('approvals.reject', $approval) }}" method="POST" class="flex-1 flex space-x-2">
                    @csrf
                    <input type="text" name="rejection_reason" required placeholder="Reason for rejection..." class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- My Approval History -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="p-5 border-b"><h3 class="text-sm font-semibold text-gray-700">Approval History</h3></div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Ticket</th>
                    <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Requester</th>
                    <th class="text-center text-xs font-medium text-gray-500 uppercase px-6 py-3">Status</th>
                    <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($myApprovals as $approval)
                <tr>
                    <td class="px-6 py-4">
                        <a href="{{ route('tickets.show', $approval->ticket) }}" class="text-sm font-medium text-primary-600">{{ $approval->ticket->ticket_number }}</a>
                        <p class="text-xs text-gray-500">{{ Str::limit($approval->ticket->title, 40) }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $approval->requester->name }}</td>
                    <td class="px-6 py-4 text-center">
                        @php $colors = ['pending'=>'bg-yellow-100 text-yellow-800','approved'=>'bg-green-100 text-green-800','rejected'=>'bg-red-100 text-red-800']; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$approval->status] }}">{{ ucfirst($approval->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $approval->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No approval history yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($myApprovals->hasPages())
    <div class="px-6 py-4 border-t">{{ $myApprovals->links() }}</div>
    @endif
</div>
@endsection
