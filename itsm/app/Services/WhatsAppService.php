<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $session;
    protected string $defaultTo;

    public function __construct()
    {
        $this->apiUrl = env('WA_API_URL', 'https://wa.amarin.biz.id/message/send-text');
        $this->session = env('WA_SESSION', 'notif');
        $this->defaultTo = env('WA_DEFAULT_TO', '628563339320');
    }

    /**
     * Method utama untuk mengirim pesan via WhatsApp API
     */
    public function sendMessage(?string $to, string $message)
    {
        $targetNumber = $this->formatPhoneNumber($to);
        
        // Gunakan nomor default (fallback) jika nomor tujuan tidak ada atau kosong
        if (!$targetNumber) {
            $targetNumber = $this->defaultTo;
        }

        try {
            $response = Http::post($this->apiUrl, [
                'session' => $this->session,
                'to'      => (string) $targetNumber,
                'text'    => $message,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WhatsApp Notification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notifikasi saat tiket baru dibuat
     */
    public function notifyTicketCreated(Ticket $ticket)
    {
        $to = $ticket->requester->phone ?? $this->defaultTo;
        
        $message  = "*[TICKET CREATED]*\n\n";
        $message .= "Ticket Number: *{$ticket->ticket_number}*\n";
        $message .= "Title: {$ticket->title}\n";
        $message .= "Priority: {$ticket->priority->name}\n";
        $message .= "Status: Open\n\n";
        $message .= "Tiket Anda berhasil dibuat dan sedang menunggu penugasan tim IT.";

        return $this->sendMessage($to, $message);
    }

    /**
     * Notifikasi saat tiket di-assign ke teknisi
     */
    public function notifyTicketAssigned(Ticket $ticket)
    {
        $to = $ticket->assignee->phone ?? $this->defaultTo;
        
        $message  = "*[TICKET ASSIGNED]*\n\n";
        $message .= "Ticket Number: *{$ticket->ticket_number}*\n";
        $message .= "Title: {$ticket->title}\n";
        $message .= "Assigned To: {$ticket->assignee->name}\n\n";
        $message .= "Tiket telah ditugaskan. Silakan cek sistem untuk detail pekerjaan.";

        return $this->sendMessage($to, $message);
    }

    /**
     * Notifikasi saat tiket sudah berstatus Resolved
     */
    public function notifyTicketResolved(Ticket $ticket)
    {
        $to = $ticket->requester->phone ?? $this->defaultTo;
        
        $message  = "*[TICKET RESOLVED]*\n\n";
        $message .= "Ticket Number: *{$ticket->ticket_number}*\n";
        $message .= "Title: {$ticket->title}\n";
        $message .= "Resolution Notes:\n{$ticket->resolution_notes}\n\n";
        $message .= "Tiket Anda telah diselesaikan oleh tim IT. Silakan login ke sistem untuk memberikan rating dan menutup tiket Anda.";

        return $this->sendMessage($to, $message);
    }

    public function notifyTicketClosed(Ticket $ticket)
    {
        // Kirim notifikasi ke teknisi yang mengerjakan tiket ini
        $to = $ticket->assignee->phone ?? $this->defaultTo;
        
        // Ambil data rating (asumsikan relasi rating di Model Ticket menggunakan hasOne)
        $ratingData = $ticket->rating;
        $ratingValue = $ratingData ? $ratingData->rating : '-';
        $feedback = ($ratingData && $ratingData->feedback) ? $ratingData->feedback : '-';
        
        $message  = "*[TICKET CLOSED & RATED]*\n\n";
        $message .= "Ticket Number: *{$ticket->ticket_number}*\n";
        $message .= "Title: {$ticket->title}\n";
        $message .= "Rating: ⭐ {$ratingValue}/5\n";
        $message .= "Feedback: {$feedback}\n\n";
        $message .= "Tiket telah ditutup secara resmi oleh Requester. Terima kasih atas kerja keras tim IT!";

        return $this->sendMessage($to, $message);
    }

    /**
     * Helper untuk memformat nomor HP (mengubah Awalan 0 menjadi 62)
     */
    private function formatPhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) return null;

        // Hilangkan karakter selain angka
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ganti 0 di awal menjadi 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }
}