<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $session;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->session = config('services.whatsapp.session');
    }

    public function sendMessage(string $to, string $message): bool
    {
        if (empty($this->apiUrl) || empty($to)) {
            Log::warning('WhatsApp not configured or recipient empty', ['to' => $to]);
            return false;
        }

        try {
            $response = Http::timeout(10)->get($this->apiUrl, [
                'session' => $this->session,
                'to' => $to,
                'text' => $message,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', ['to' => $to]);
                return true;
            }

            Log::error('WhatsApp message failed', ['to' => $to, 'response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp service error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function notifyTicketCreated($ticket): void
    {
        $message = "🎫 *New Ticket Created*\n\n";
        $message .= "No: {$ticket->ticket_number}\n";
        $message .= "Title: {$ticket->title}\n";
        $message .= "Priority: {$ticket->priority->name}\n";
        $message .= "From: {$ticket->requester->name}\n";
        $message .= "Category: {$ticket->category->name}\n\n";
        $message .= "Please check ITSM Dashboard for details.";

        $this->sendMessage(config('services.whatsapp.default_to'), $message);
    }

    public function notifyTicketAssigned($ticket): void
    {
        $assignee = $ticket->assignee;
        $phone = $assignee->phone ?? config('services.whatsapp.default_to');

        $message = "🔧 *Ticket Assigned to You*\n\n";
        $message .= "No: {$ticket->ticket_number}\n";
        $message .= "Title: {$ticket->title}\n";
        $message .= "Priority: {$ticket->priority->name}\n";
        $message .= "From: {$ticket->requester->name}\n\n";
        $message .= "You are assigned to handle this ticket.\n";
        $message .= "Deadline: " . ($ticket->due_date ? $ticket->due_date->format('d M Y H:i') : 'Not set');

        $this->sendMessage($phone, $message);
    }

    public function notifyTicketResolved($ticket): void
    {
        $requester = $ticket->requester;
        $phone = $requester->phone ?? config('services.whatsapp.default_to');
        $ratingUrl = url("/tickets/{$ticket->id}");

        $message = "✅ *Ticket Resolved*\n\n";
        $message .= "No: {$ticket->ticket_number}\n";
        $message .= "Title: {$ticket->title}\n";
        $message .= "Handled by: {$ticket->assignee->name}\n\n";
        $message .= "Your ticket has been resolved.\n\n";
        $message .= "⭐ *Rating is REQUIRED* to close this ticket.\n";
        $message .= "Click to rate:\n";
        $message .= $ratingUrl;

        $this->sendMessage($phone, $message);
    }

    public function notifySlaBreach($ticket): void
    {
        $message = "⚠️ *SLA Breach Alert*\n\n";
        $message .= "No: {$ticket->ticket_number}\n";
        $message .= "Title: {$ticket->title}\n";
        $message .= "Status: {$ticket->status}\n";
        $message .= "Priority: {$ticket->priority->name}\n\n";
        $message .= "This ticket has exceeded its SLA target!";

        $this->sendMessage(config('services.whatsapp.default_to'), $message);
    }

    public function notifyRatingReminder($ticket): void
    {
        $requester = $ticket->requester;
        $phone = $requester->phone ?? config('services.whatsapp.default_to');
        $ratingUrl = url("/tickets/{$ticket->id}");

        $message = "🔔 *Reminder: Please Rate*\n\n";
        $message .= "No: {$ticket->ticket_number}\n";
        $message .= "Title: {$ticket->title}\n\n";
        $message .= "Your ticket has been resolved.\n";
        $message .= "⭐ Please rate the service to close the ticket.\n\n";
        $message .= "Click: {$ratingUrl}";

        $this->sendMessage($phone, $message);
    }
}
