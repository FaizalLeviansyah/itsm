<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        // Summary stats
        $stats = [
            'total_tickets' => Ticket::where('created_at', '>=', $startDate)->count(),
            'resolved_tickets' => Ticket::where('resolved_at', '>=', $startDate)->count(),
            'avg_resolution_time' => round(TicketRating::where('created_at', '>=', $startDate)->avg('resolution_time_minutes') ?? 0),
            'avg_rating' => round(TicketRating::where('created_at', '>=', $startDate)->avg('rating') ?? 0, 1),
            'sla_compliance' => $this->calculateSlaCompliance($startDate),
            'first_response_avg' => $this->calculateAvgFirstResponse($startDate),
        ];

        // Technician Performance
        $techPerformance = User::whereIn('role', ['technician', 'admin'])
            ->withCount(['ticketsAssigned as resolved_count' => function ($q) use ($startDate) {
                $q->where('resolved_at', '>=', $startDate);
            }])
            ->withAvg(['ratings as avg_rating' => function ($q) use ($startDate) {
                $q->where('ticket_ratings.created_at', '>=', $startDate);
            }], 'rating')
            ->withAvg(['ratings as avg_response' => function ($q) use ($startDate) {
                $q->where('ticket_ratings.created_at', '>=', $startDate);
            }], 'response_rating')
            ->withAvg(['ratings as avg_resolution' => function ($q) use ($startDate) {
                $q->where('ticket_ratings.created_at', '>=', $startDate);
            }], 'resolution_rating')
            ->withAvg(['ratings as avg_professionalism' => function ($q) use ($startDate) {
                $q->where('ticket_ratings.created_at', '>=', $startDate);
            }], 'professionalism_rating')
            ->withAvg(['ratings as avg_resolution_time' => function ($q) use ($startDate) {
                $q->where('ticket_ratings.created_at', '>=', $startDate);
            }], 'resolution_time_minutes')
            ->having('resolved_count', '>', 0)
            ->orderByDesc('avg_rating')
            ->get();

        // Tickets by category
        $byCategory = Ticket::where('tickets.created_at', '>=', $startDate)
            ->join('categories', 'tickets.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as count'))
            ->groupBy('categories.name')
            ->get();

        // Daily ticket trend
        $dailyTrend = Ticket::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.index', compact('stats', 'techPerformance', 'byCategory', 'dailyTrend', 'period'));
    }

    private function calculateSlaCompliance($startDate): float
    {
        $total = Ticket::where('created_at', '>=', $startDate)
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        if ($total === 0) return 100;

        $breached = Ticket::where('created_at', '>=', $startDate)
            ->whereIn('status', ['resolved', 'closed'])
            ->where('sla_breached', true)
            ->count();

        return round((($total - $breached) / $total) * 100, 1);
    }

    private function calculateAvgFirstResponse($startDate): int
    {
        $tickets = Ticket::where('created_at', '>=', $startDate)
            ->whereNotNull('first_response_at')
            ->get();

        if ($tickets->isEmpty()) return 0;

        $totalMinutes = $tickets->sum(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->first_response_at);
        });

        return round($totalMinutes / $tickets->count());
    }
}
