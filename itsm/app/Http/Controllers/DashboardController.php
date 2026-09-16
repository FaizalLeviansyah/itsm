<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        } elseif ($user->role === 'technician') {
            return $this->technicianDashboard();
        }

        return $this->userDashboard();
    }

    private function technicianDashboard()
    {
        $user = Auth::user();

        $stats = [
            'assigned_to_me' => Ticket::where('assigned_to', $user->id)->whereNotIn('status', ['resolved', 'closed', 'cancelled'])->count(),
            'in_progress' => Ticket::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
            'resolved_today' => Ticket::where('assigned_to', $user->id)->whereDate('resolved_at', today())->count(),
            'overdue' => Ticket::where('assigned_to', $user->id)->where('due_date', '<', now())->whereNotIn('status', ['resolved', 'closed'])->count(),
            'avg_rating' => round($user->ratings()->avg('rating') ?? 0, 1),
            'total_resolved_month' => Ticket::where('assigned_to', $user->id)->where('resolved_at', '>=', now()->startOfMonth())->count(),
        ];

        $myTickets = Ticket::with(['requester', 'priority', 'category'])
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->orderByRaw("FIELD(status, 'in_progress', 'assigned', 'open', 'pending', 'resolved')")
            ->orderBy('due_date')
            ->limit(15)
            ->get();

        $overdueTickets = Ticket::with(['requester', 'priority'])
            ->where('assigned_to', $user->id)
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->orderBy('due_date')
            ->get();

        return view('dashboard.technician', compact('stats', 'myTickets', 'overdueTickets'));
    }

    private function adminDashboard()
    {
        $companyId = request('company_id');
        $companyFilter = fn($q) => $companyId ? $q->where('company_id', $companyId) : $q;

        $stats = [
            'total_tickets' => Ticket::when($companyId, fn($q) => $q->where('company_id', $companyId))->count(),
            'open_tickets' => Ticket::when($companyId, fn($q) => $q->where('company_id', $companyId))->where('status', 'open')->count(),
            'in_progress' => Ticket::when($companyId, fn($q) => $q->where('company_id', $companyId))->whereIn('status', ['assigned', 'in_progress'])->count(),
            'resolved' => Ticket::when($companyId, fn($q) => $q->where('company_id', $companyId))->where('status', 'resolved')->count(),
            'overdue' => Ticket::when($companyId, fn($q) => $q->where('company_id', $companyId))->where('due_date', '<', now())->whereNotIn('status', ['resolved', 'closed'])->count(),
            'total_assets' => Asset::when($companyId, fn($q) => $q->where('company_id', $companyId))->count(),
            'avg_rating' => round(TicketRating::avg('rating') ?? 0, 1),
            'avg_resolution_time' => round(TicketRating::avg('resolution_time_minutes') ?? 0),
        ];

        $recentTickets = Ticket::with(['requester', 'assignee', 'priority', 'category'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $ticketsByStatus = Ticket::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $ticketsByPriority = Ticket::join('priorities', 'tickets.priority_id', '=', 'priorities.id')
            ->when($companyId, fn($q) => $q->where('tickets.company_id', $companyId))
            ->select('priorities.name', 'priorities.color', DB::raw('count(*) as count'))
            ->groupBy('priorities.name', 'priorities.color')
            ->get();

        $companies = \App\Models\Company::where('is_active', true)->get();

        $topTechnicians = User::where('role', 'technician')
            ->withCount('ticketsAssigned')
            ->withAvg('ratings', 'rating')
            ->orderByDesc('ratings_avg_rating')
            ->limit(5)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentTickets', 'ticketsByStatus', 'ticketsByPriority', 'topTechnicians', 'companies'));
    }

    private function userDashboard()
    {
        $user = Auth::user();

        $stats = [
            'my_tickets' => Ticket::where('requester_id', $user->id)->count(),
            'open_tickets' => Ticket::where('requester_id', $user->id)->where('status', 'open')->count(),
            'in_progress' => Ticket::where('requester_id', $user->id)->whereIn('status', ['assigned', 'in_progress'])->count(),
            'resolved' => Ticket::where('requester_id', $user->id)->where('status', 'resolved')->count(),
        ];

        $recentTickets = Ticket::with(['assignee', 'priority', 'category'])
            ->where('requester_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $pendingRatings = Ticket::where('requester_id', $user->id)
            ->where('status', 'resolved')
            ->whereDoesntHave('rating')
            ->get();

        return view('dashboard.user', compact('stats', 'recentTickets', 'pendingRatings'));
    }
}
