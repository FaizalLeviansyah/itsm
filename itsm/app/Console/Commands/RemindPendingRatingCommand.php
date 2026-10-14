<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemindPendingRatingCommand extends Command
{
    protected $signature = 'itsm:remind-rating';
    protected $description = 'Send daily WA reminder to requesters for unrated resolved tickets';

    public function handle(WhatsAppService $waService)
    {
        // Ambil tiket resolved yang belum di-rating & berumur setidaknya 1 hari
        $tickets = Ticket::where('status', 'resolved')
            ->doesntHave('rating')
            ->whereNotNull('public_token')
            ->where('updated_at', '<=', now()->subDay())
            ->get();

        foreach ($tickets as $ticket) {
            if ($ticket->requester && !empty($ticket->requester->phone)) {
                try {
                    $publicLink = route('ticket.public.review', $ticket->public_token);

                    $waMessage  = "🔔 *[DAILY REMINDER - IT SERVICE RATING]*\n\n";
                    $waMessage .= "Dear *{$ticket->requester->full_name}*,\n\n";
                    $waMessage .= "This is a friendly reminder that ticket *{$ticket->title}* (#{$ticket->ticket_number}) has been resolved.\n\n";
                    $waMessage .= "Please take 5 seconds to rate our technical support so this ticket can be officially closed (no login required):\n\n";
                    $waMessage .= "👉 {$publicLink}\n\n";
                    $waMessage .= "_Thank you for your valuable feedback!_";

                    $waService->sendMessage($ticket->requester->phone, $waMessage);
                    
                    // Update timestamp agar tidak terkirim ganda di hari yang sama
                    $ticket->touch(); 
                } catch (\Exception $e) {
                    Log::error("Failed to send daily WA rating reminder for ticket #{$ticket->id}: " . $e->getMessage());
                }
            }
        }
    }
}