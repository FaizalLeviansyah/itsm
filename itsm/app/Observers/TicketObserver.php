<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\SlaPolicy;
use App\Notifications\TicketNotification;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TicketObserver
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Handle the Ticket "creating" event.
     */
    public function creating(Ticket $ticket): void
    {
        $sla = SlaPolicy::where('priority', $ticket->priority)->first();

        if ($sla) {
            $ticket->due_date = Carbon::now()->addMinutes($sla->resolution_time_minutes);
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // 1. Jika ada perubahan Assignee (Assign / Reassign)
        if ($ticket->isDirty('assigned_to')) {
            $ticket->load(['assignee', 'requester', 'priority']);
            
            // 1. Notifikasi ke Teknisi Baru
            if ($ticket->assignee) {
                try {
                    $this->waService->sendMessage(
                        $ticket->assignee->phone,
                        "*[TICKET ASSIGNED]*\n\nTiket baru ditugaskan kepada Anda:\n" .
                        "Ticket: *{$ticket->ticket_number}*\n" .
                        "Title: {$ticket->title}\n" .
                        "Priority: " . ($ticket->priority->name ?? '-') . "\n" .
                        "Silakan cek sistem ITSM untuk memproses."
                    );
                    $ticket->assignee->notify(new TicketNotification($ticket, 'assigned'));
                } catch (\Exception $e) {
                    Log::error('Notifikasi Assign ke Teknisi Gagal: ' . $e->getMessage());
                }
            }

            // 2. Notifikasi ke Requester (Memastikan Pembuat Tiket Tahu Tiketnya Di-assign)
            if ($ticket->requester && $ticket->requester->phone) {
                try {
                    $techName = $ticket->assignee->name ?? 'Teknisi';
                    $this->waService->sendMessage(
                        $ticket->requester->phone,
                        "*[TICKET UPDATE]*\n\nTiket Anda *{$ticket->ticket_number}* saat ini telah di-assign dan sedang ditangani oleh *{$techName}*."
                    );
                    $ticket->requester->notify(new TicketNotification($ticket, 'assigned'));
                } catch (\Exception $e) {
                    Log::error('Notifikasi Assign ke Requester Gagal: ' . $e->getMessage());
                }
            }
        }

        // 2. Jika terjadi perubahan Status
        if ($ticket->isDirty('status')) {
            $ticket->load(['assignee', 'requester', 'priority']);
            $newStatus = $ticket->status;

            $recipients = collect();
            if ($ticket->requester) $recipients->push($ticket->requester);
            if ($ticket->assignee) $recipients->push($ticket->assignee);
            
            // Tambahkan admin agar tetap memantau
            $admins = User::where('role', 'admin')->where('is_active', true)->get();
            $recipients = $recipients->merge($admins)->unique('id');

            foreach ($recipients as $user) {
                try {
                    $user->notify(new TicketNotification($ticket, $newStatus));
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim email status update ke {$user->email}: " . $e->getMessage());
                }
            }
        }
    }

    public function deleted(Ticket $ticket): void {}
    public function restored(Ticket $ticket): void {}
    public function forceDeleted(Ticket $ticket): void {}
}