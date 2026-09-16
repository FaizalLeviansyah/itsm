<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\TicketHistory;
use App\Notifications\TicketNotification;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    public function index()
    {
        $pendingApprovals = TicketApproval::with(['ticket.requester', 'ticket.priority', 'ticket.category'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        $myApprovals = TicketApproval::with(['ticket', 'requester'])
            ->where('requested_by', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('approvals.index', compact('pendingApprovals', 'myApprovals'));
    }

    public function approve(Request $request, TicketApproval $approval)
    {
        $approval->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'decided_at' => now(),
        ]);

        $ticket = $approval->ticket;
        $ticket->update(['status' => 'open']);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'approval',
            'new_value' => 'approved',
            'note' => 'Change request approved by ' . Auth::user()->name,
        ]);

        // Notify requester
        $ticket->requester->notify(new TicketNotification($ticket, 'approval_decided', 'approved'));

        // WA notification
        $phone = $ticket->requester->phone ?? config('services.whatsapp.default_to');
        $this->waService->sendMessage($phone, "✅ *Change Request Approved*\n\nTicket: {$ticket->ticket_number}\nTitle: {$ticket->title}\n\nApproved by: " . Auth::user()->name);

        return back()->with('success', 'Change request approved successfully.');
    }

    public function reject(Request $request, TicketApproval $approval)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $approval->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
            'decided_at' => now(),
        ]);

        $ticket = $approval->ticket;
        $ticket->update(['status' => 'cancelled']);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'approval',
            'new_value' => 'rejected',
            'note' => 'Rejected: ' . $request->rejection_reason,
        ]);

        // Notify requester
        $ticket->requester->notify(new TicketNotification($ticket, 'approval_decided', 'rejected'));

        $phone = $ticket->requester->phone ?? config('services.whatsapp.default_to');
        $this->waService->sendMessage($phone, "❌ *Change Request Rejected*\n\nTicket: {$ticket->ticket_number}\nTitle: {$ticket->title}\nReason: {$request->rejection_reason}");

        return back()->with('success', 'Change request rejected.');
    }
}
