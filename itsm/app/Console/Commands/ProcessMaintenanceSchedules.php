<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\Priority;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class ProcessMaintenanceSchedules extends Command
{
    protected $signature = 'maintenance:process';
    protected $description = 'Create tickets for due maintenance schedules';

    public function handle(WhatsAppService $waService): void
    {
        $schedules = MaintenanceSchedule::with(['asset', 'assignee'])
            ->where('is_active', true)
            ->where('auto_create_ticket', true)
            ->where('next_due_date', '<=', today())
            ->get();

        $created = 0;
        $mediumPriority = Priority::where('slug', 'medium')->first();

        foreach ($schedules as $schedule) {
            $ticket = Ticket::create([
                'ticket_number' => Ticket::generateTicketNumber(),
                'title' => "[PM] {$schedule->title} - {$schedule->asset->name}",
                'description' => "Preventive Maintenance terjadwal.\n\n{$schedule->description}\n\nAsset: {$schedule->asset->asset_tag} - {$schedule->asset->name}",
                'category_id' => $schedule->asset->asset_category_id ?? 1,
                'priority_id' => $mediumPriority->id ?? 3,
                'requester_id' => $schedule->assigned_to ?? 1,
                'assigned_to' => $schedule->assigned_to,
                'assigned_at' => now(),
                'status' => $schedule->assigned_to ? 'assigned' : 'open',
                'type' => 'service_request',
                'company_id' => $schedule->company_id,
                'due_date' => now()->addHours($mediumPriority->sla_hours ?? 24),
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $schedule->assigned_to ?? 1,
                'field' => 'status',
                'new_value' => 'open',
                'note' => 'Auto-created from maintenance schedule',
            ]);

            // Update schedule
            $schedule->update(['last_performed_at' => today()]);
            $schedule->calculateNextDueDate();

            // Notify assignee
            if ($schedule->assignee) {
                $phone = $schedule->assignee->phone ?? config('services.whatsapp.default_to');
                $waService->sendMessage($phone, "🔧 *Preventive Maintenance*\n\nAsset: {$schedule->asset->name}\nJadwal: Hari ini\nTicket: {$ticket->ticket_number}\n\nSilakan lakukan maintenance sesuai prosedur.");
            }

            $created++;
            $this->info("Created: {$ticket->ticket_number} for {$schedule->asset->name}");
        }

        $this->info("Done. {$created} maintenance tickets created.");
    }
}
