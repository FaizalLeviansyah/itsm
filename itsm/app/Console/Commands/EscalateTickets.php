<?php

namespace App\Console\Commands;

use App\Models\EscalationRule;
use App\Models\Ticket;
use App\Models\TicketEscalation;
use App\Models\TicketHistory;
use App\Notifications\TicketNotification;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class EscalateTickets extends Command
{
    protected $signature = 'tickets:escalate';
    protected $description = 'Escalate tickets that have not been responded to within the configured time';

    public function handle(WhatsAppService $waService): void
    {
        $rules = EscalationRule::with(['priority', 'escalateTo'])
            ->where('is_active', true)
            ->orderBy('priority_id')
            ->orderBy('level')
            ->get();

        $escalatedCount = 0;

        foreach ($rules as $rule) {
            $tickets = Ticket::with(['priority', 'requester', 'assignee'])
                ->where('priority_id', $rule->priority_id)
                ->whereIn('status', ['open', 'assigned'])
                ->whereNull('first_response_at')
                ->where('created_at', '<=', now()->subMinutes($rule->escalation_minutes))
                ->whereDoesntHave('escalations', function ($q) use ($rule) {
                    $q->where('level', '>=', $rule->level);
                })
                ->get();

            foreach ($tickets as $ticket) {
                // Create escalation record
                TicketEscalation::create([
                    'ticket_id' => $ticket->id,
                    'escalated_to' => $rule->escalate_to,
                    'escalated_from' => $ticket->assigned_to,
                    'level' => $rule->level,
                    'reason' => "Tidak ada respons dalam {$rule->escalation_minutes} menit (Level {$rule->level})",
                ]);

                // Reassign ticket
                $oldAssignee = $ticket->assigned_to;
                $ticket->update([
                    'assigned_to' => $rule->escalate_to,
                    'assigned_by' => null,
                    'status' => 'assigned',
                ]);

                // Log history
                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $rule->escalate_to,
                    'field' => 'escalation',
                    'old_value' => "Level " . ($rule->level - 1),
                    'new_value' => "Level {$rule->level}",
                    'note' => "Auto-escalated: Tidak ada respons dalam {$rule->escalation_minutes} menit",
                ]);

                // Notify via email
                $rule->escalateTo->notify(new TicketNotification(
                    $ticket,
                    'escalated',
                    "Tidak ada respons dalam {$rule->escalation_minutes} menit"
                ));

                // Notify via WhatsApp
                $phone = $rule->escalateTo->phone ?? config('services.whatsapp.default_to');
                $waService->sendMessage($phone, "⚠️ *ESCALATION Level {$rule->level}*\n\nTicket: {$ticket->ticket_number}\nJudul: {$ticket->title}\nPrioritas: {$ticket->priority->name}\n\nTicket ini belum ditangani selama {$rule->escalation_minutes} menit.");

                $escalatedCount++;
                $this->info("Escalated: {$ticket->ticket_number} → {$rule->escalateTo->name} (Level {$rule->level})");
            }
        }

        $this->info("Done. {$escalatedCount} tickets escalated.");
    }
}
