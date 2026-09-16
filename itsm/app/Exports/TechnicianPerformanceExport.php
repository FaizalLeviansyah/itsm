<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TechnicianPerformanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $startDate;

    public function __construct($startDate = null)
    {
        $this->startDate = $startDate ?? now()->startOfMonth();
    }

    public function collection()
    {
        return User::whereIn('role', ['technician', 'admin'])
            ->withCount(['ticketsAssigned as resolved_count' => function ($q) {
                $q->where('resolved_at', '>=', $this->startDate);
            }])
            ->withAvg(['ratings as avg_rating' => function ($q) {
                $q->where('ticket_ratings.created_at', '>=', $this->startDate);
            }], 'rating')
            ->withAvg(['ratings as avg_response' => function ($q) {
                $q->where('ticket_ratings.created_at', '>=', $this->startDate);
            }], 'response_rating')
            ->withAvg(['ratings as avg_resolution' => function ($q) {
                $q->where('ticket_ratings.created_at', '>=', $this->startDate);
            }], 'resolution_rating')
            ->withAvg(['ratings as avg_professionalism' => function ($q) {
                $q->where('ticket_ratings.created_at', '>=', $this->startDate);
            }], 'professionalism_rating')
            ->withAvg(['ratings as avg_resolution_time' => function ($q) {
                $q->where('ticket_ratings.created_at', '>=', $this->startDate);
            }], 'resolution_time_minutes')
            ->having('resolved_count', '>', 0)
            ->orderByDesc('avg_rating')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Teknisi', 'Tickets Resolved', 'Avg Rating',
            'Avg Response Rating', 'Avg Resolution Rating',
            'Avg Professionalism', 'Avg Resolution Time (min)',
        ];
    }

    public function map($tech): array
    {
        return [
            $tech->name,
            $tech->resolved_count,
            number_format($tech->avg_rating ?? 0, 1),
            number_format($tech->avg_response ?? 0, 1),
            number_format($tech->avg_resolution ?? 0, 1),
            number_format($tech->avg_professionalism ?? 0, 1),
            round($tech->avg_resolution_time ?? 0),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
