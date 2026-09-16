<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ?? now()->startOfMonth();
        $this->endDate = $endDate ?? now();
    }

    public function collection()
    {
        return Ticket::with(['requester', 'assignee', 'priority', 'category', 'rating'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Ticket', 'Judul', 'Tipe', 'Kategori', 'Prioritas',
            'Status', 'Requester', 'Assignee', 'Vessel',
            'Created', 'Resolved', 'SLA Hours', 'SLA Breach',
            'Resolution Time (min)', 'Rating',
        ];
    }

    public function map($ticket): array
    {
        $resolutionTime = null;
        if ($ticket->assigned_at && $ticket->resolved_at) {
            $resolutionTime = $ticket->assigned_at->diffInMinutes($ticket->resolved_at);
        }

        return [
            $ticket->ticket_number,
            $ticket->title,
            ucfirst(str_replace('_', ' ', $ticket->type)),
            $ticket->category->name,
            $ticket->priority->name,
            ucfirst(str_replace('_', ' ', $ticket->status)),
            $ticket->requester->name,
            $ticket->assignee->name ?? '-',
            $ticket->vessel_name ?? '-',
            $ticket->created_at->format('d/m/Y H:i'),
            $ticket->resolved_at?->format('d/m/Y H:i') ?? '-',
            $ticket->priority->sla_hours,
            $ticket->sla_breached ? 'YES' : 'NO',
            $resolutionTime ?? '-',
            $ticket->rating->rating ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
