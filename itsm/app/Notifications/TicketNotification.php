<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNotification extends Notification
{
    use Queueable;

    protected Ticket $ticket;
    protected string $type;
    protected string $message;

    public function __construct(Ticket $ticket, string $type, string $message = '')
    {
        $this->ticket = $ticket;
        $this->type = $type;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->getSubject());

        switch ($this->type) {
            case 'created':
                $mail->greeting('New Ticket Created')
                     ->line("Ticket **{$this->ticket->ticket_number}** has been submitted.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->line("**Priority:** {$this->ticket->priority->name}")
                     ->line("**Requester:** {$this->ticket->requester->name}")
                     ->line("**Category:** {$this->ticket->category->name}")
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
                break;

            case 'assigned':
                $mail->greeting('Ticket Assigned to You')
                     ->line("Ticket **{$this->ticket->ticket_number}** has been assigned to you.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->line("**Priority:** {$this->ticket->priority->name}")
                     ->line("**Deadline:** " . ($this->ticket->due_date?->format('d M Y H:i') ?? 'Not set'))
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"))
                     ->line('Please respond as soon as possible.');
                break;

            case 'resolved':
                $mail->greeting('Your Ticket Has Been Resolved')
                     ->line("Ticket **{$this->ticket->ticket_number}** has been resolved.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->line("**Resolved by:** {$this->ticket->assignee->name}")
                     ->line('⭐ Please provide your rating to close this ticket.')
                     ->action('Rate Service', url("/tickets/{$this->ticket->id}"));
                break;

            case 'escalated':
                $mail->greeting('⚠️ Ticket Escalation')
                     ->line("Ticket **{$this->ticket->ticket_number}** has been escalated to you.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->line("**Reason:** {$this->message}")
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"))
                     ->line('Immediate attention required.');
                break;

            case 'approval_required':
                $mail->greeting('Approval Required')
                     ->line("Change Request **{$this->ticket->ticket_number}** needs your approval.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->line("**Requester:** {$this->ticket->requester->name}")
                     ->action('Review & Approve', url("/tickets/{$this->ticket->id}"));
                break;

            case 'approval_decided':
                $mail->greeting('Approval Status Update')
                     ->line("Change Request **{$this->ticket->ticket_number}** has been {$this->message}.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
                break;

            case 'reopened':
                $mail->greeting('Ticket Reopened')
                     ->line("Ticket **{$this->ticket->ticket_number}** has been reopened by the requester.")
                     ->line("**Title:** {$this->ticket->title}")
                     ->line("**Reason:** {$this->message}")
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"))
                     ->line('Please investigate and resolve again.');
                break;

            default:
                $mail->greeting('Ticket Update')
                     ->line($this->message)
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
        }

        return $mail->line('Thank you for using Amarin ITSM.');
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'type' => $this->type,
            'message' => $this->message ?: $this->getSubject(),
        ];
    }

    private function getSubject(): string
    {
        return match ($this->type) {
            'created' => "[ITSM] New Ticket: {$this->ticket->ticket_number}",
            'assigned' => "[ITSM] Ticket Assigned: {$this->ticket->ticket_number}",
            'resolved' => "[ITSM] Ticket Resolved: {$this->ticket->ticket_number}",
            'escalated' => "[ITSM] ⚠️ Escalation: {$this->ticket->ticket_number}",
            'approval_required' => "[ITSM] Approval Required: {$this->ticket->ticket_number}",
            'approval_decided' => "[ITSM] Approval Update: {$this->ticket->ticket_number}",
            'reopened' => "[ITSM] Ticket Reopened: {$this->ticket->ticket_number}",
            default => "[ITSM] Update: {$this->ticket->ticket_number}",
        };
    }
}
