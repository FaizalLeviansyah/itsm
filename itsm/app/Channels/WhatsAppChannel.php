<?php

namespace App\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Send the given notification via WhatsApp.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $phone = $notifiable->phone ?? null;
        if (empty($phone)) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        if (! empty($message)) {
            $this->waService->sendMessage($phone, $message);
        }
    }
}
