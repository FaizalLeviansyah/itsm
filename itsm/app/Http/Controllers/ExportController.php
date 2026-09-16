<?php

namespace App\Http\Controllers;

use App\Exports\TechnicianPerformanceExport;
use App\Exports\TicketReportExport;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportTicketsExcel(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());
        $filename = 'tickets-report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new TicketReportExport($startDate, $endDate), $filename);
    }

    public function exportTicketsPdf(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $stats = [
            'total_tickets' => Ticket::where('created_at', '>=', $startDate)->count(),
            'resolved_tickets' => Ticket::where('resolved_at', '>=', $startDate)->count(),
            'avg_resolution_time' => round(TicketRating::where('created_at', '>=', $startDate)->avg('resolution_time_minutes') ?? 0),
            'avg_rating' => round(TicketRating::where('created_at', '>=', $startDate)->avg('rating') ?? 0, 1),
        ];

        $tickets = Ticket::with(['requester', 'assignee', 'priority', 'category'])
            ->where('created_at', '>=', $startDate)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $techPerformance = User::whereIn('role', ['technician', 'admin'])
            ->withCount(['ticketsAssigned as resolved_count' => fn($q) => $q->where('resolved_at', '>=', $startDate)])
            ->withAvg(['ratings as avg_rating' => fn($q) => $q->where('ticket_ratings.created_at', '>=', $startDate)], 'rating')
            ->having('resolved_count', '>', 0)
            ->orderByDesc('avg_rating')
            ->get();

        $pdf = Pdf::loadView('exports.tickets-pdf', compact('stats', 'tickets', 'techPerformance', 'period', 'startDate'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('itsm-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportPerformanceExcel(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $filename = 'technician-performance-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new TechnicianPerformanceExport($startDate), $filename);
    }
}
