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

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    @forelse($notifications as $notification)
    <div class="flex items-start gap-4 px-6 py-4 border-b border-gray-50 {{ $notification->read_at ? '' : 'bg-blue-50/30' }}">
        <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
            {{ match($notification->data['type'] ?? '') {
                'created' => 'bg-blue-100',
                'assigned' => 'bg-indigo-100',
                'resolved' => 'bg-green-100',
                'escalated' => 'bg-red-100',
                'approval_required' => 'bg-amber-100',
                default => 'bg-gray-100'
            } }}">
            <i class="text-xs
                {{ match($notification->data['type'] ?? '') {
                    'created' => 'fas fa-plus text-blue-600',
                    'assigned' => 'fas fa-user-check text-indigo-600',
                    'resolved' => 'fas fa-check text-green-600',
                    'escalated' => 'fas fa-exclamation text-red-600',
                    'approval_required' => 'fas fa-clock text-amber-600',
                    default => 'fas fa-bell text-gray-600'
                } }}"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-800 {{ $notification->read_at ? '' : 'font-medium' }}">
                {{ $notification->data['message'] ?? $notification->data['title'] ?? 'Notification' }}
            </p>
            @if(!empty($notification->data['ticket_number']))
            <p class="text-xs text-gray-500 mt-0.5">
                <span class="font-mono text-brand-600">{{ $notification->data['ticket_number'] }}</span>
                @if(!empty($notification->data['title'])) — {{ Str::limit($notification->data['title'], 50) }} @endif
            </p>
            @endif
            <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if(!$notification->read_at)
            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-100 text-gray-400 hover:text-brand-600" title="Mark as read">
                    <i class="fas fa-check text-xs"></i>
                </button>
            </form>
            @endif
            @if(!empty($notification->data['ticket_id']))
            <a href="{{ url('/tickets/' . $notification->data['ticket_id']) }}" class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-100 text-gray-400 hover:text-brand-600" title="View ticket">
                <i class="fas fa-external-link-alt text-xs"></i>
            </a>
            @endif
        </div>
    </div>
    @empty
    <div class="px-6 py-16 text-center">
        <i class="fas fa-bell-slash text-4xl text-gray-200 mb-3"></i>
        <p class="text-gray-500 font-medium">No notifications</p>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div class="mt-4">{{ $notifications->links() }}</div>
@endif
@endsection
