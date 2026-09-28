@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->unreadNotifications->count() }} unread</p>
    </div>
    @if(Auth::user()->unreadNotifications->count() > 0)
    <form action="{{ route('notifications.readAll') }}" method="POST">
        @csrf
        <button type="submit" class="text-sm text-brand-600 font-medium hover:text-brand-700">Mark all as read</button>
    </form>
    @endif
</div>

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
    @forelse($notifications as $notification)
    <div class="flex items-start gap-4 px-6 py-4 border-b border-gray-100 transition hover:bg-gray-50/80 {{ $notification->read_at ? '' : 'bg-blue-50/40' }}">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 shadow-sm
            {{ match($notification->data['type'] ?? '') {
                'created' => 'bg-blue-100 text-blue-600',
                'assigned' => 'bg-indigo-100 text-indigo-600',
                'resolved' => 'bg-green-100 text-green-600',
                'escalated' => 'bg-red-100 text-red-600',
                'reopened' => 'bg-orange-100 text-orange-600',
                'approval_required' => 'bg-amber-100 text-amber-600',
                default => 'bg-gray-100 text-gray-600'
            } }}">
            <i class="text-sm
                {{ match($notification->data['type'] ?? '') {
                    'created' => 'fas fa-plus',
                    'assigned' => 'fas fa-user-check',
                    'resolved' => 'fas fa-check-double',
                    'escalated' => 'fas fa-exclamation-triangle',
                    'reopened' => 'fas fa-undo',
                    'approval_required' => 'fas fa-clock',
                    default => 'fas fa-bell'
                } }}"></i>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-1">
                @if(!empty($notification->data['ticket_number']))
                <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-200">
                    {{ $notification->data['ticket_number'] }}
                </span>
                @endif
                <p class="text-sm text-gray-900 {{ $notification->read_at ? '' : 'font-semibold' }}">
                    {{ $notification->data['message'] ?? $notification->data['title'] ?? 'Notification' }}
                </p>
            </div>

            @if(!empty($notification->data['title']))
            <p class="text-xs font-medium text-gray-700 mb-1.5">
                {{ Str::limit($notification->data['title'], 70) }}
            </p>
            @endif

            <!-- Metadata UX khusus Admin & Teknisi Monitoring -->
            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 mt-2">
                @if(!empty($notification->data['requester_name']))
                <span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded text-gray-600">
                    <i class="fas fa-user text-[10px] text-gray-400"></i> Requester: <strong class="text-gray-800">{{ $notification->data['requester_name'] }}</strong>
                </span>
                @endif

                @if(!empty($notification->data['assignee_name']) && $notification->data['assignee_name'] !== 'Unassigned')
                <span class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded text-gray-600">
                    <i class="fas fa-user-cog text-[10px] text-gray-400"></i> Technician: <strong class="text-gray-800">{{ $notification->data['assignee_name'] }}</strong>
                </span>
                @endif

                @if(!empty($notification->data['vessel_name']))
                <span class="inline-flex items-center gap-1 bg-blue-50 px-2 py-0.5 rounded text-blue-700">
                    <i class="fas fa-ship text-[10px] text-blue-500"></i> {{ $notification->data['vessel_name'] }}
                </span>
                @endif

                <span class="text-gray-400 ml-auto">{{ $notification->created_at->diffForHumans() }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2 self-center">
            @if(!$notification->read_at)
            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-200 text-gray-400 hover:text-brand-600 transition" title="Mark as read">
                    <i class="fas fa-check text-xs"></i>
                </button>
            </form>
            @endif
            @if(!empty($notification->data['ticket_id']))
            <a href="{{ url('/tickets/' . $notification->data['ticket_id']) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-brand-500 hover:text-white text-gray-600 transition" title="View ticket">
                <i class="fas fa-external-link-alt text-xs"></i>
            </a>
            @endif
        </div>
    </div>
    @empty
    <div class="px-6 py-16 text-center">
        <i class="fas fa-bell-slash text-4xl text-gray-200 mb-3"></i>
        <p class="text-gray-500 font-medium">No notifications found</p>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div class="mt-4">{{ $notifications->links() }}</div>
@endif
@endsection