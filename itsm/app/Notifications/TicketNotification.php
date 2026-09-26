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
        // Pengecekan role/posisi penerima email
        $isRequester = $notifiable->id === $this->ticket->requester_id;
        $mail = (new MailMessage)->subject($this->getSubject($isRequester));

        switch ($this->type) {
            case 'created':
                if ($isRequester) {
                    $mail->greeting('New Ticket Created')
                         ->line("Your ticket **{$this->ticket->ticket_number}** has been successfully submitted.");
                } else {
                    $mail->greeting('New Ticket Alert')
                         ->line("A new ticket **{$this->ticket->ticket_number}** has been created by **{$this->ticket->requester->name}**.");
                }
                $mail->line("**Title:** {$this->ticket->title}")
                     ->line("**Priority:** {$this->ticket->priority->name}")
                     ->line("**Category:** {$this->ticket->category->name}")
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
                break;
                
            case 'open':
            case 'waiting_for_assign':
                if ($isRequester) {
                    $mail->greeting('Ticket Status Updated')
                         ->line("Your ticket **{$this->ticket->ticket_number}** is currently open and waiting to be assigned to a technician.");
                } else {
                    $mail->greeting('Action Required: Unassigned Ticket')
                         ->line("Ticket **{$this->ticket->ticket_number}** from **{$this->ticket->requester->name}** is currently Open / Waiting for Assign.")
                         ->line("Please review and assign a technician.");
                }
                $mail->line("**Title:** {$this->ticket->title}")
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
                break;

            case 'in_progress':
                if ($isRequester) {
                    $mail->greeting('Ticket is In Progress')
                         ->line("Good news! Your ticket **{$this->ticket->ticket_number}** is now being worked on by our team.");
                } else {
                    $mail->greeting('Ticket Status Updated')
                         ->line("Ticket **{$this->ticket->ticket_number}** status has been changed to **In Progress**.");
                }
                $mail->line("**Title:** {$this->ticket->title}")
                     ->line("**Priority:** {$this->ticket->priority->name}")
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
                break;

            case 'assigned':
                if ($isRequester) {
                    $mail->greeting('Technician Assigned')
                         ->line("Your ticket **{$this->ticket->ticket_number}** has been assigned to a technician.");
                } else {
                    $mail->greeting('Ticket Assigned to You')
                         ->line("Ticket **{$this->ticket->ticket_number}** has been assigned to you.")
                         ->line("**Deadline:** " . ($this->ticket->due_date?->format('d M Y H:i') ?? 'Not set'))
                         ->line('Please respond as soon as possible.');
                }
                $mail->line("**Title:** {$this->ticket->title}")
                     ->line("**Priority:** {$this->ticket->priority->name}")
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
                break;

            case 'resolved':
                if ($isRequester) {
                    $mail->greeting('Your Ticket Has Been Resolved')
                         ->line("Ticket **{$this->ticket->ticket_number}** has been resolved.")
                         ->line("**Title:** {$this->ticket->title}")
                         ->line("**Resolved by:** " . ($this->ticket->assignee->name ?? 'Technician'))
                         ->line('⭐ Please provide your rating to close this ticket.')
                         ->action('Rate Service', url("/tickets/{$this->ticket->id}"));
                } else {
                    $mail->greeting('Ticket Resolved')
                         ->line("Ticket **{$this->ticket->ticket_number}** has been marked as resolved.")
                         ->line("**Title:** {$this->ticket->title}")
                         ->line("**Requester:** {$this->ticket->requester->name}")
                         ->line("Waiting for requester to provide rating and close the ticket.")
                         ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
                }
                break;

            case 'reopened':
                if ($isRequester) {
                    $mail->greeting('Ticket Reopened')
                         ->line("You have successfully reopened ticket **{$this->ticket->ticket_number}**.");
                } else {
                    $mail->greeting('Ticket Reopened Alert')
                         ->line("Ticket **{$this->ticket->ticket_number}** has been reopened by the requester.")
                         ->line('Please investigate and resolve again.');
                }
                $mail->line("**Title:** {$this->ticket->title}")
                     ->line("**Reason:** {$this->message}")
                     ->action('View Ticket', url("/tickets/{$this->ticket->id}"));
                break;

            case 'escalated':
                $mail->greeting('⚠️ Ticket Escalation')
                     ->line("Ticket **{$this->ticket->ticket_number}** has been escalated.")
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

            default:
                $mail->greeting('Ticket Update')
                     ->line($this->message ?: "Ticket **{$this->ticket->ticket_number}** has a new update.")
                     ->line("**Title:** {$this->ticket->title}")
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
            'message' => $this->message ?: $this->getSubject($notifiable->id === $this->ticket->requester_id),
        ];
    }

    private function getSubject(bool $isRequester): string
    {
        // Custom subjek jika diperlukan perbedaan antara requester dan admin
        $prefix = "[ITSM]";
        
        return match ($this->type) {
            'created' => "{$prefix} " . ($isRequester ? "Ticket Submitted: {$this->ticket->ticket_number}" : "New Ticket Alert: {$this->ticket->ticket_number}"),
            'open', 'waiting_for_assign' => "{$prefix} Ticket Open: {$this->ticket->ticket_number}",
            'in_progress' => "{$prefix} Ticket In Progress: {$this->ticket->ticket_number}",
            'assigned' => "{$prefix} Ticket Assigned: {$this->ticket->ticket_number}",
            'resolved' => "{$prefix} Ticket Resolved: {$this->ticket->ticket_number}",
            'escalated' => "{$prefix} ⚠️ Escalation: {$this->ticket->ticket_number}",
            'approval_required' => "{$prefix} Approval Required: {$this->ticket->ticket_number}",
            'approval_decided' => "{$prefix} Approval Update: {$this->ticket->ticket_number}",
            'reopened' => "{$prefix} Ticket Reopened: {$this->ticket->ticket_number}",
            default => "{$prefix} Update: {$this->ticket->ticket_number}",
        };
    }
}