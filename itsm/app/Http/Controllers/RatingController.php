<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'response_rating' => 'nullable|integer|min:1|max:5',
            'resolution_rating' => 'nullable|integer|min:1|max:5',
            'professionalism_rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        if ($ticket->requester_id !== Auth::id()) {
            abort(403);
        }

        if ($ticket->rating) {
            return back()->with('error', 'You have already rated this ticket.');
        }

        $resolutionTime = null;
        if ($ticket->assigned_at && $ticket->resolved_at) {
            $resolutionTime = $ticket->assigned_at->diffInMinutes($ticket->resolved_at);
        }

        TicketRating::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'technician_id' => $ticket->assigned_to,
            'rating' => $request->rating,
            'response_rating' => $request->response_rating,
            'resolution_rating' => $request->resolution_rating,
            'professionalism_rating' => $request->professionalism_rating,
            'feedback' => $request->feedback,
            'resolution_time_minutes' => $resolutionTime,
        ]);

        // Close the ticket after rating
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);

        return back()->with('success', 'Thank you for your feedback!');
    }
}
