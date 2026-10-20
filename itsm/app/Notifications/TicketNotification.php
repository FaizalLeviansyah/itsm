<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Mail\Mailables\Attachment;

class TicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 10;

    protected Ticket $ticket;
    protected string $type;
    protected string $message;

    public function __construct(Ticket $ticket, string $type, string $message = '')
    {
        $this->ticket = $ticket;
        $this->type = $type;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database', 'mail', \App\Channels\WhatsAppChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        $isRequester = ($notifiable->id === $this->ticket->requester_id);
        $isAdmin = ($notifiable->role === 'admin');
        
        // 1. Dapatkan URL Logo Publik yang Valid (Gunakan URL Domain Aplikasi)
        $logoUrl = config('app.url') . '/storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg';

        // 2. Susun Konteks Masalah Singkat untuk Preview Text di Inbox
        $issueContext = "Issue: " . $this->ticket->title . " (" . ($this->ticket->vessel_name ?: ($this->ticket->location ?: 'Office')) . ")";

        $mail = (new MailMessage)
            ->subject($this->getSubject($isRequester))
            ->line($issueContext) // Dipasang paling atas agar terbaca di Mail Peek/Preview Text!
            ->line("![Amarin Logo]({$logoUrl})");

        switch ($this->type) {
            case 'created':
                if ($isRequester) {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("Your ticket **{$this->ticket->ticket_number}** has been successfully submitted.");
                } elseif ($isAdmin) {
                    $mail->greeting("Dear Admin {$notifiable->name},")
                         ->line("A new ticket **{$this->ticket->ticket_number}** has been created.")
                         ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'));
                } else {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("A new ticket **{$this->ticket->ticket_number}** has been created by **" . ($this->ticket->requester->name ?? 'User') . "**.");
                }

                $mail->line("**Subject / Title:** {$this->ticket->title}")
                     ->line("**Category:** " . ($this->ticket->category->name ?? '-'))
                     ->line("**Priority:** " . ($this->ticket->priority->name ?? '-'))
                     ->line("**Location/Vessel:** " . ($this->ticket->vessel_name ?: ($this->ticket->location ?: 'Office')))
                     ->action('View Ticket Details', url("/tickets/{$this->ticket->id}"));
                break;
                
            case 'open':
            case 'waiting_for_assign':
                if ($isRequester) {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("Your ticket **{$this->ticket->ticket_number}** is open and waiting to be assigned to a technician.");
                } else {
                    $greetingName = $isAdmin ? "Dear Admin {$notifiable->name}," : "Dear {$notifiable->name},";
                    $mail->greeting($greetingName)
                         ->line("Action Required: Ticket **{$this->ticket->ticket_number}** is currently Open / Waiting for Assign.")
                         ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'));
                }
                $mail->line("**Title:** {$this->ticket->title}")
                     ->action('View Ticket Details', url("/tickets/{$this->ticket->id}"));
                break;

            case 'in_progress':
                if ($isRequester) {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("Good news! Your ticket **{$this->ticket->ticket_number}** is now being worked on by our team.");
                } elseif ($isAdmin) {
                    $mail->greeting("Dear Admin {$notifiable->name},")
                         ->line("Ticket **{$this->ticket->ticket_number}** status updated to **In Progress**.")
                         ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'))
                         ->line("**Assigned Technician:** " . ($this->ticket->assignee->name ?? 'Unassigned'));
                } else {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("Ticket **{$this->ticket->ticket_number}** status has been changed to **In Progress**.");
                }
                $mail->line("**Title:** {$this->ticket->title}")
                     ->line("**Priority:** " . ($this->ticket->priority->name ?? '-'))
                     ->action('View Ticket Details', url("/tickets/{$this->ticket->id}"));
                break;

            case 'assigned':
                if ($isRequester) {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("Your ticket **{$this->ticket->ticket_number}** has been assigned to technician **" . ($this->ticket->assignee->name ?? 'Technician') . "**.");
                } elseif ($isAdmin) {
                    $mail->greeting("Dear Admin {$notifiable->name},")
                         ->line("Ticket **{$this->ticket->ticket_number}** has been assigned.")
                         ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'))
                         ->line("**Assigned Technician:** " . ($this->ticket->assignee->name ?? 'Unassigned'));
                } else {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("Ticket **{$this->ticket->ticket_number}** has been assigned to you.")
                         ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'))
                         ->line("**Deadline:** " . ($this->ticket->due_date?->format('d M Y H:i') ?? 'Not set'));
                }
                $mail->line("**Title:** {$this->ticket->title}")
                     ->action('View Ticket Details', url("/tickets/{$this->ticket->id}"));
                break;

            case 'resolved':
                if ($isRequester) {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("Your ticket **{$this->ticket->ticket_number}** has been resolved.")
                         ->line("**Title:** {$this->ticket->title}")
                         ->line("**Resolved by:** " . ($this->ticket->assignee->name ?? 'Technician'))
                         ->line('⭐ Please provide your rating to close this ticket.')
                         ->action('Rate Service & Close Ticket', url("/tickets/{$this->ticket->id}"));
                } elseif ($isAdmin) {
                    $mail->greeting("Dear Admin {$notifiable->name},")
                         ->line("Ticket **{$this->ticket->ticket_number}** has been marked as resolved.")
                         ->line("**Title:** {$this->ticket->title}")
                         ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'))
                         ->line("**Resolved by Technician:** " . ($this->ticket->assignee->name ?? 'Technician'))
                         ->line("Status: Awaiting Requester Rating & Closure.")
                         ->action('View Ticket Details', url("/tickets/{$this->ticket->id}"));
                } else {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("Ticket **{$this->ticket->ticket_number}** has been marked as resolved.")
                         ->line("**Title:** {$this->ticket->title}")
                         ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'))
                         ->action('View Ticket Details', url("/tickets/{$this->ticket->id}"));
                }
                break;

            case 'reopened':
                if ($isRequester) {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("You have successfully reopened ticket **{$this->ticket->ticket_number}**.");
                } elseif ($isAdmin) {
                    $mail->greeting("Dear Admin {$notifiable->name},")
                         ->line("Alert: Ticket **{$this->ticket->ticket_number}** has been reopened by Requester **" . ($this->ticket->requester->name ?? 'User') . "**.")
                         ->line("**Previously Assigned Technician:** " . ($this->ticket->assignee->name ?? 'Unassigned'));
                } else {
                    $mail->greeting("Dear {$notifiable->name},")
                         ->line("Ticket **{$this->ticket->ticket_number}** has been reopened by the requester **" . ($this->ticket->requester->name ?? 'User') . "**.");
                }
                $mail->line("**Title:** {$this->ticket->title}")
                     ->line("**Reason:** {$this->message}")
                     ->action('View Ticket Details', url("/tickets/{$this->ticket->id}"));
                break;

            case 'escalated':
                $mail->greeting($isAdmin ? "Dear Admin {$notifiable->name}," : "Dear {$notifiable->name},")
                     ->line("⚠️ Ticket **{$this->ticket->ticket_number}** has been escalated.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'))
                     ->line("**Assigned Technician:** " . ($this->ticket->assignee->name ?? 'Unassigned'))
                     ->line("**Reason:** {$this->message}")
                     ->action('View Escalated Ticket', url("/tickets/{$this->ticket->id}"));
                break;

            case 'approval_required':
                $mail->greeting($isAdmin ? "Dear Admin {$notifiable->name}," : "Dear {$notifiable->name},")
                     ->line("Change Request **{$this->ticket->ticket_number}** needs your approval.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'))
                     ->action('Review & Approve', url("/tickets/{$this->ticket->id}"));
                break;

            default:
                $mail->greeting($isAdmin ? "Dear Admin {$notifiable->name}," : "Dear {$notifiable->name},")
                     ->line($this->message ?: "Ticket **{$this->ticket->ticket_number}** has a new update.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->line("**Requested by:** " . ($this->ticket->requester->name ?? 'User'))
                     ->action('View Ticket Details', url("/tickets/{$this->ticket->id}"));
        }

        return $mail->salutation("Regards,\nAmarin Ship Management — IT Department");
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

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'type' => $this->type,
            'message' => $this->message ?: $this->getSubject($notifiable->id === $this->ticket->requester_id),
            'requester_name' => $this->ticket->requester?->name ?? 'Unknown',
            'assignee_name' => $this->ticket->assignee?->name ?? 'Unassigned',
            'vessel_name' => $this->ticket->vessel_name ?? null,
        ];
    }

    /**
     * Memformat Subject Email tanpa awalan [Amarin ITSM] dan menyertakan Subject/Masalah Tiket
     */
    private function getSubject(bool $isRequester): string
    {
        $ticketNum = $this->ticket->ticket_number;
        $title = \Illuminate\Support\Str::limit($this->ticket->title, 35);

        return match ($this->type) {
            'created' => ($isRequester ? "Ticket Submitted: {$ticketNum} - {$title}" : "New Ticket Alert: {$ticketNum} - {$title}"),
            'open', 'waiting_for_assign' => "Ticket Open: {$ticketNum} - {$title}",
            'in_progress' => "Ticket In Progress: {$ticketNum} - {$title}",
            'assigned' => "Ticket Assigned: {$ticketNum} - {$title}",
            'resolved' => "Ticket Resolved: {$ticketNum} - {$title}",
            'escalated' => "⚠️ Escalation: {$ticketNum} - {$title}",
            'approval_required' => "Approval Required: {$ticketNum} - {$title}",
            'approval_decided' => "Approval Update: {$ticketNum} - {$title}",
            'reopened' => "Ticket Reopened: {$ticketNum} - {$title}",
            default => "Update: {$ticketNum} - {$title}",
        };
    }
}