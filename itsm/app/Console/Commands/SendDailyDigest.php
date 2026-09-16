<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyDigest extends Command
{
    protected $signature = 'digest:send';
    protected $description = 'Send daily email digest to admins and technicians';

    public function handle(): void
    {
        $stats = [
            'new_today' => Ticket::whereDate('created_at', today())->count(),
            'open' => Ticket::where('status', 'open')->count(),
            'overdue' => Ticket::where('due_date', '<', now())->whereNotIn('status', ['resolved', 'closed'])->count(),
            'pending_rating' => Ticket::where('status', 'resolved')->whereDoesntHave('rating')->count(),
            'resolved_yesterday' => Ticket::whereDate('resolved_at', yesterday())->count(),
        ];

        $recipients = User::whereIn('role', ['admin', 'technician'])
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();

        foreach ($recipients as $user) {
            $personalStats = [
                'my_open' => Ticket::where('assigned_to', $user->id)->whereNotIn('status', ['resolved', 'closed', 'cancelled'])->count(),
                'my_overdue' => Ticket::where('assigned_to', $user->id)->where('due_date', '<', now())->whereNotIn('status', ['resolved', 'closed'])->count(),
            ];

            try {
                Mail::send('emails.daily-digest', compact('stats', 'personalStats', 'user'), function ($mail) use ($user) {
                    $mail->to($user->email)->subject('[ITSM] Daily Digest - ' . now()->format('d M Y'));
                });
                $this->info("Sent to: {$user->email}");
            } catch (\Exception $e) {
                $this->error("Failed: {$user->email} - {$e->getMessage()}");
            }
        }

        $this->info("Done. Sent to {$recipients->count()} users.");
    }
}
