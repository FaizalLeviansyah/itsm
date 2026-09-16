<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class CheckSlaBreach extends Command
{
    protected $signature = 'tickets:check-sla';
    protected $description = 'Check for SLA breaches and send notifications';

    public function handle(WhatsAppService $waService): void
    {
        $breachedTickets = Ticket::with(['priority', 'requester', 'assignee'])
            ->whereNotIn('status', ['resolved', 'closed', 'cancelled'])
            ->where('sla_breached', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->get();

        foreach ($breachedTickets as $ticket) {
            $ticket->update(['sla_breached' => true]);
            $waService->notifySlaBreach($ticket);
            $this->info("SLA breached: {$ticket->ticket_number}");
        }

        $this->info("Checked. {$breachedTickets->count()} tickets breached SLA.");
    }
}
