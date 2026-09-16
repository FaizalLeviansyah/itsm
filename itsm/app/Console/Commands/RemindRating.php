<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class RemindRating extends Command
{
    protected $signature = 'tickets:remind-rating';
    protected $description = 'Send WhatsApp reminder to users who have not rated their resolved tickets';

    public function handle(WhatsAppService $waService): void
    {
        // Find tickets resolved more than 4 hours ago but not yet rated
        $tickets = Ticket::with(['requester', 'assignee'])
            ->where('status', 'resolved')
            ->whereNull('closed_at')
            ->where('resolved_at', '<=', now()->subHours(4))
            ->whereDoesntHave('rating')
            ->get();

        $count = 0;
        foreach ($tickets as $ticket) {
            $waService->notifyRatingReminder($ticket);
            $count++;
            $this->info("Reminder sent: {$ticket->ticket_number} → {$ticket->requester->name}");
        }

        $this->info("Done. {$count} reminders sent.");
    }
}
