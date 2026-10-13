<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Services\WhatsAppService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SendPendingRatingNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $waService = app(WhatsAppService::class);

        // MODE TEST: Kirim ke nomor yang mau di tes 
        $targetPhoneTest = '628563339320'; // Mas Hendri

        // Cari tiket yang belum dirating (status resolved atau closed tanpa rating)
        $pendingTickets = Ticket::whereIn('status', ['resolved', 'closed'])
            ->doesntHave('rating')
            ->whereNotNull('public_token')
            ->get();

        if ($pendingTickets->isEmpty()) {
            $this->command->info('Tidak ada tiket yang menggantung / belum di-rating.');
            return;
        }

        // ==========================================
        // 1. UJI COBA KIRIM 1 TIKET DULU KE MAS HENDRI
        // ==========================================
        $sampleTicket = $pendingTickets->first();
        $publicLink = route('ticket.public.review', $sampleTicket->public_token);

        $waMessage  = "*[IT HELPDESK - REVIU & RATING TIKET]*\n\n";
        $waMessage .= "Halo *{$sampleTicket->requester?->name}*,\n";
        $waMessage .= "Tiket Anda *{$sampleTicket->ticket_number}* ({$sampleTicket->title}) telah diselesaikan oleh tim IT.\n\n";
        $waMessage .= "Mohon kesediaannya untuk memberikan ulasan/rating kinerja teknisi melalui tautan berikut (tanpa login):\n";
        $waMessage .= "👉 {$publicLink}\n\n";
        $waMessage .= "_Terima kasih_";

        // Kirim ke nomor Mas Hendri untuk dites
        $waService->sendMessage($targetPhoneTest, $waMessage);
        $this->command->info("Sampel tiket {$sampleTicket->ticket_number} berhasil dikirim nang WA  ({$targetPhoneTest})!");

        /* 
        // ==========================================
        // 2. UNCOMMENT BAGIAN INI JIKA MAS HENDRI SUDAH ACC
        // ==========================================
        foreach ($pendingTickets as $ticket) {
            if ($ticket->requester && !empty($ticket->requester->phone)) {
                $link = route('ticket.public.review', $ticket->public_token);
                
                $msg  = "*[IT HELPDESK - REVIU & RATING TIKET]*\n\n";
                $msg .= "Halo *{$ticket->requester->name}*,\n";
                $msg .= "Tiket Anda *{$ticket->ticket_number}* ({$ticket->title}) telah diselesaikan oleh tim IT.\n\n";
                $msg .= "Mohon kesediaannya untuk memberikan ulasan/rating kinerja teknisi melalui tautan berikut (tanpa login):\n";
                $msg .= "👉 {$link}\n\n";
                $msg .= "_Terima kasih_";

                $waService->sendMessage($ticket->requester->phone, $msg);
                $this->command->info("Terkirim ke {$ticket->requester->name} ({$ticket->requester->phone})");
            }
        }
        */
    }
}