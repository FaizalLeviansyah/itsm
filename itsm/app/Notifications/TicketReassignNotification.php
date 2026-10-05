<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Mail\Mailables\Attachment; // <--- Tambahkan import ini

class TicketReassignNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 10;

    protected $ticket;
    protected $technician;

    public function __construct($ticket, $technician)
    {
        $this->ticket = $ticket;
        $this->technician = $technician;
    }

    public function via($notifiable)
    {
        return ['database', 'mail', \App\Channels\WhatsAppChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'type' => 'escalated',
            'message' => "Teknisi {$this->technician->name} mengajukan Request ReAssign to Admin untuk tiket #{$this->ticket->ticket_number}.",
            'requester_name' => $this->ticket->requester?->name ?? 'Unknown',
            'assignee_name' => $this->technician->name ?? 'Technician',
            'vessel_name' => $this->ticket->vessel_name ?? null,
            'action_url' => route('tickets.show', $this->ticket->id)
        ];
    }

    public function toMail($notifiable)
    {
        // Menggunakan icon publik yang valid di folder public
        $logoUrl = asset('icon-512.png');

        return (new MailMessage)
                    ->subject('[Amarin ITSM] Alert: Request ReAssign Tiket #' . $this->ticket->ticket_number)
                    ->line("![Amarin Logo]({$logoUrl})")
                    ->greeting('Dear Admin ' . $notifiable->name . ',')
                    ->line("Teknisi **{$this->technician->name}** telah mengajukan **Request ReAssign to Admin**.")
                    ->line("**Ticket Number:** #{$this->ticket->ticket_number}")
                    ->line("**Title:** " . $this->ticket->title)
                    ->line("**Requester:** " . ($this->ticket->requester->name ?? 'User'))
                    ->line('SLA saat ini sedang di-pause hingga tiket di-assign kembali oleh Admin.')
                    ->action('Lihat & ReAssign Tiket', route('tickets.show', $this->ticket->id))
                    ->line('Mohon segera ditindaklanjuti.')
                    ->salutation("Regards,\nAmarin Ship Management — IT Department");
    }

    public function attachments(): array
    {
        $mailAttachments = [];
        if ($this->ticket->relationLoaded('attachments') || method_exists($this->ticket, 'attachments')) {
            foreach ($this->ticket->attachments as $file) {
                $mailAttachments[] = Attachment::fromStorageDisk('public', $file->file_path)
                                               ->as($file->file_name);
            }
        }
        return $mailAttachments;
    }

    public function toWhatsApp($notifiable)
    {
        return [
            'phone' => $notifiable->phone_number,
            'message' => "*ALERT: REQUEST REASSIGN TIKET*\n\nHalo Pak Admin {$notifiable->name},\n\nTeknisi *{$this->technician->name}* meminta ReAssign untuk tiket:\n*No:* #{$this->ticket->ticket_number}\n*Judul:* {$this->ticket->title}\n*Requester:* " . ($this->ticket->requester->name ?? 'User') . "\n\nSilakan cek dashboard sistem untuk menugaskan ulang tiket ini."
        ];
    }
}