<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ITSM Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e40af; font-size: 18px; }
        .header p { margin: 5px 0 0; color: #666; }
        .stats { display: table; width: 100%; margin-bottom: 20px; }
        .stat-box { display: table-cell; text-align: center; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .stat-box .value { font-size: 16px; font-weight: bold; color: #1e40af; }
        .stat-box .label { font-size: 9px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #3b82f6; color: white; padding: 6px 4px; text-align: left; font-size: 9px; }
        td { padding: 5px 4px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        tr:nth-child(even) { background: #f8fafc; }
        .section-title { font-size: 13px; font-weight: bold; color: #1e40af; margin: 15px 0 8px; border-left: 3px solid #3b82f6; padding-left: 8px; }
        .footer { text-align: center; margin-top: 20px; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-breach { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Amarin ITSM - Laporan Ticket</h1>
        <p>Periode: {{ ucfirst($period) }} | Generated: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="value">{{ $stats['total_tickets'] }}</div>
            <div class="label">Total Tickets</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $stats['resolved_tickets'] }}</div>
            <div class="label">Resolved</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $stats['avg_resolution_time'] }} min</div>
            <div class="label">Avg Resolution Time</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $stats['avg_rating'] }}/5</div>
            <div class="label">Avg Rating</div>
        </div>
    </div>

    <div class="section-title">Daftar Ticket</div>
    <table>
        <thead>
            <tr>
                <th>No. Ticket</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Requester</th>
                <th>Assignee</th>
                <th>Created</th>
                <th>SLA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>{{ $ticket->ticket_number }}</td>
                <td>{{ Str::limit($ticket->title, 30) }}</td>
                <td>{{ $ticket->category->name }}</td>
                <td>{{ $ticket->priority->name }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</td>
                <td>{{ $ticket->requester->name }}</td>
                <td>{{ $ticket->assignee->name ?? '-' }}</td>
                <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                <td>@if($ticket->sla_breached)<span class="badge badge-breach">BREACH</span>@else OK @endif</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($techPerformance->count() > 0)
    <div class="section-title">Performa Teknisi</div>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Resolved</th>
                <th>Avg Rating</th>
            </tr>
        </thead>
        <tbody>
            @foreach($techPerformance as $tech)
            <tr>
                <td>{{ $tech->name }}</td>
                <td>{{ $tech->resolved_count }}</td>
                <td>{{ number_format($tech->avg_rating ?? 0, 1) }}/5</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        &copy; {{ date('Y') }} Amarin Ship Management - IT Service Management System | Confidential
    </div>
</body>
</html>
