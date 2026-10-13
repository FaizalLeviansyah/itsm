<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Rating;
use Illuminate\Http\Request;

class PublicTicketController extends Controller
{
    public function show($token)
    {
        $ticket = Ticket::with(['category', 'priority', 'technicians', 'company', 'ratings', 'comments'])
            ->where('public_token', $token)
            ->firstOrFail();

        return view('tickets.public-review', compact('ticket'));
    }

    public function submit(Request $request, $token)
    {
        $ticket = Ticket::where('public_token', $token)->firstOrFail();

        $request->validate([
            'action' => 'required|in:rate,reopen',
            'rating' => 'required_if:action,rate|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'reopen_reason' => 'required_if:action,reopen|string|max:1000',
        ]);

        if ($request->action === 'rate') {
            // Simpan Rating & Komentar
            Rating::updateOrCreate(
                ['ticket_id' => $ticket->id],
                [
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                    'user_id' => $ticket->requester_id ?? $ticket->user_id,
                ]
            );

            // Otomatis Closed Ticket saat user memberikan rating
            $ticket->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            return back()->with('success', 'Terima kasih! Rating & komentar Anda telah disimpan, dan tiket resmi ditutup (Closed).');
        } 
        
        if ($request->action === 'reopen') {
            // Reopen Tiket jika user belum puas dengan hasil kerja teknisi
            $ticket->update([
                'status' => 'open', // atau 'in_progress'
            ]);

            // Tambahkan komentar alasan reopen
            $ticket->comments()->create([
                'user_id' => $ticket->requester_id ?? $ticket->user_id,
                'comment' => '[REOPENED BY USER] Alasan: ' . $request->reopen_reason,
                'is_internal' => false,
            ]);

            return back()->with('success', 'Tiket berhasil dibuka kembali (Reopened). Tim teknisi akan segera meninjau kembali kendala Anda.');
        }

        return back();
    }
}