<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\TicketHistory;
use App\Notifications\TicketNotification;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicTicketController extends Controller
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    public function show($token)
    {
        $ticket = Ticket::with(['category', 'priority', 'assignedTechnicians', 'company', 'rating', 'comments'])
            ->where('public_token', $token)
            ->firstOrFail();

        return view('tickets.public-review', compact('ticket'));
    }

    public function submit(Request $request, $token)
    {
        $ticket = Ticket::with(['requester', 'assignee', 'assignedTechnicians'])
            ->where('public_token', $token)
            ->firstOrFail();

        // PROTEKSI BACKEND: Hanya izinkan submit jika status tiket bernilai 'resolved'
        if ($ticket->status !== 'resolved') {
            return back()->with('error', 'Penilaian atau Reopen hanya dapat dilakukan saat status tiket adalah Resolved.');
        }

        $request->validate([
            'action'        => 'required|in:rate,reopen',
            'ratings'       => 'required_if:action,rate|array',
            'ratings.*'     => 'required|integer|min:1|max:5',
            'feedbacks'     => 'nullable|array',
            'feedback'      => 'nullable|string|max:1000',
            'reason'        => 'required_if:action,reopen|nullable|string|max:1000',
        ]);

        if ($request->action === 'rate') {
            $ratingsArray = $request->input('ratings', []);
            $feedbacksArray = $request->input('feedbacks', []);
            
            // Hitung rata-rata skor jika diperlukan untuk kolom tunggal 'rating' utama, atau ambil yang pertama
            $avgRating = count($ratingsArray) > 0 ? array_sum($ratingsArray) / count($ratingsArray) : 5;
            $firstTechId = array_key_first($ratingsArray) ?? $ticket->assigned_to;
            $firstFeedback = isset($feedbacksArray[$firstTechId]) ? $feedbacksArray[$firstTechId] : $request->feedback;

            TicketRating::updateOrCreate(
                ['ticket_id' => $ticket->id],
                [
                    'user_id'                 => $ticket->requester_id,
                    'technician_id'           => $firstTechId,
                    'rating'                  => round($avgRating),
                    'feedback'                => $request->feedback ?? $firstFeedback,
                    'resolution_time_minutes' => $ticket->resolution_time,
                ]
            );

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $ticket->requester_id,
                'field'     => 'status',
                'old_value' => 'resolved',
                'new_value' => 'closed',
                'note'      => 'Rated via Public Review Link: ' . round($avgRating) . ' Stars',
            ]);

            return back()->with('success', 'Thank you! Your rating has been saved and the ticket is now officially Closed.');
        } 
        
        // ==========================================
        // 2. SKENARIO AKSI: REOPEN TICKET BY REQUESTER
        // ==========================================
        if ($request->action === 'reopen') {
            $oldStatus = $ticket->status;

            // Update status kembali ke In Progress & hapus timestamp resolved
            $ticket->update([
                'status'      => 'in_progress',
                'resolved_at' => null,
            ]);

            // Tambahkan komentar alasan reopen di percakapan tiket
            $ticket->comments()->create([
                'user_id'     => $ticket->requester_id,
                'comment'     => '[REOPENED VIA PUBLIC LINK] Reason: ' . $request->reason,
                'is_internal' => false,
            ]);

            // Catat ke TicketHistory
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $ticket->requester_id,
                'field'     => 'status',
                'old_value' => $oldStatus,
                'new_value' => 'in_progress',
                'note'      => 'Reopened by Requester via Public Review: ' . $request->reason,
            ]);

            // --- KIRIM NOTIFIKASI WA DENGAN REMARK/ALASAN KE TEKNISI & ADMIN ---
            try {
                $requesterName = $ticket->requester?->name ?? 'Requester';

                $waMsg  = "⚠️ *[TICKET REOPENED - ACTION REQUIRED]* ⚠️\n\n";
                $waMsg .= "Ticket Number: *{$ticket->ticket_number}*\n";
                $waMsg .= "Title: *{$ticket->title}*\n";
                $waMsg .= "Reopened By: *{$requesterName}*\n\n";
                $waMsg .= "📝 *Requester Remark / Reason:*\n";
                $waMsg .= "_{$request->reason}_\n\n";
                $waMsg .= "Status has been reverted to *IN PROGRESS*. Please check the ITSM Portal and follow up with the requester immediately.\n\n";
                $waMsg .= "👉 Link: " . route('tickets.show', $ticket->id);

                // A. Kirim ke WA Single Assignee (jika ada nomor HP)
                if ($ticket->assignee && !empty($ticket->assignee->phone)) {
                    $this->waService->sendMessage($ticket->assignee->phone, $waMsg);
                }

                // B. Kirim ke Multi-Technicians (jika tim teknisi diset)
                if ($ticket->assignedTechnicians && $ticket->assignedTechnicians->count() > 0) {
                    foreach ($ticket->assignedTechnicians as $tech) {
                        if (!empty($tech->phone) && $tech->id !== $ticket->assigned_to) {
                            $this->waService->sendMessage($tech->phone, $waMsg);
                        }
                    }
                }

                // C. Kirim Alert WA ke Admin Default
                $adminPhone = env('WA_DEFAULT_TO', '628563339320');
                if ($adminPhone) {
                    $this->waService->sendMessage($adminPhone, $waMsg);
                }

                // D. Kirim Email Notifikasi ke Teknisi
                if ($ticket->assignee) {
                    $ticket->assignee->notify(new TicketNotification($ticket, 'reopened', $request->reason));
                }

            } catch (\Exception $e) {
                Log::error('Failed sending Reopen WA/Email Notification: ' . $e->getMessage());
            }

            return back()->with('success', 'Your request to Reopen this ticket has been submitted. Our technical team has been notified with your reason.');
        }

        return back();
    }

    
}