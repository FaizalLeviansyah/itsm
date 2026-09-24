<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class TicketReassignNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $technician;

    public function __construct($ticket, $technician)
    {
        $this->ticket = $ticket;
        $this->technician = $technician;
    }

    // Tentukan channel notifikasi
    public function via($notifiable)
    {
        return ['database', 'mail', \App\Channels\WhatsAppChannel::class];
    }

    // 1. Alert via Dashboard Sistem (Database)
    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => 'Permintaan ReAssign Tiket',
            'message' => "Teknisi {$this->technician->name} mengajukan Request ReAssign to Admin untuk tiket #{$this->ticket->ticket_number}.",
            'action_url' => route('tickets.show', $this->ticket->id)
        ];
    }

    // 2. Alert via Email
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Alert: Request ReAssign Tiket #' . $this->ticket->ticket_number)
                    ->greeting('Halo Pak ' . $notifiable->name . ',')
                    ->line("Teknisi {$this->technician->name} telah mengajukan Request ReAssign to Admin.")
                    ->line('Judul Tiket: ' . $this->ticket->title)
                    ->line('SLA saat ini sedang di-pause hingga tiket di-assign kembali.')
                    ->action('Lihat Tiket', route('tickets.show', $this->ticket->id))
                    ->line('Mohon segera ditindaklanjuti.');
    }

    // 3. Alert via WhatsApp (Format data untuk Custom Channel)
    public function toWhatsApp($notifiable)
    {
        return [
            'phone' => $notifiable->phone_number, // Pastikan ada field nomor HP di table users
            'message' => "*ALERT: REQUEST REASSIGN TIKET*\n\nHalo Pak {$notifiable->name},\n\nTeknisi *{$this->technician->name}* meminta ReAssign untuk tiket:\n*No:* #{$this->ticket->ticket_number}\n*Judul:* {$this->ticket->title}\n\nSilakan cek dashboard sistem untuk menugaskan ulang tiket ini."
        ];
    }
}