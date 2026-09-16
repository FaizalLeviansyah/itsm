<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\SlaPolicy;
use Carbon\Carbon;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function creating(Ticket $ticket): void
    {
        // Ambil aturan SLA sesuai prioritas tiket yang sedang dibuat
        $sla = SlaPolicy::where('priority', $ticket->priority)->first();

        if ($sla) {
            // Hitung due_date berdasarkan waktu sekarang + resolusi SLA
            $ticket->due_date = Carbon::now()->addMinutes($sla->resolution_time_minutes);
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
