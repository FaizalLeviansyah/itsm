<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company; // <-- Import Model Company
use App\Models\Priority;
use App\Models\SubCategory;
use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\TicketHistory;
use App\Models\TicketRating;
use App\Models\User;
use App\Notifications\TicketNotification;
use App\Services\SlaCalculator;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    protected WhatsAppService $waService;
    protected SlaCalculator $slaCalculator;

    public function __construct(WhatsAppService $waService, SlaCalculator $slaCalculator)
    {
        $this->waService = $waService;
        $this->slaCalculator = $slaCalculator;
    }

    public function index(Request $request)
    {
        $query = Ticket::with(['requester', 'assignee', 'priority', 'category', 'company']);

        if (Auth::user()->role === 'user') {
            $query->where('requester_id', Auth::id());
        } elseif (Auth::user()->role === 'technician') {
            if ($request->get('view', 'assigned') === 'assigned') {
                $query->where('assigned_to', Auth::id());
            }
        }

        // Filters...
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority_id', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            });
        }

        $tickets = $query->orderByDesc('created_at')->paginate(15);
        $priorities = Priority::all();
        $categories = Category::where('is_active', true)->get();

        return view('tickets.index', compact('tickets', 'priorities', 'categories'));
    }

    public function create()
    {
        $categories = Category::with('subCategories')->where('is_active', true)->get();
        $priorities = Priority::orderBy('sort_order')->get();
        $assets = Asset::where('assigned_to', Auth::id())->orWhereNull('assigned_to')->get();
        
        $companies = Company::where('is_active', true)->orderBy('name')->get(); 

        return view('tickets.create', compact('categories', 'priorities', 'assets', 'companies'));
    }

    public function store(Request $request)
    {
        // Hapus validasi company_id karena otomatis ditarik dari Auth User
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'priority_id' => 'required|exists:priorities,id',
            'type' => 'required|in:incident,service_request,problem,change_request',
        ]);

        $priority = Priority::find($request->priority_id);

        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'priority_id' => $request->priority_id,
            'requester_id' => Auth::id(),
            'company_id' => Auth::user()->company_id, // <-- KUNCI AUTO ASSIGN
            'type' => $request->type,
            'impact' => $request->impact ?? 'low',
            'urgency' => $request->urgency ?? 'low',
            'location' => $request->location,
            'vessel_name' => $request->vessel_name,
            'due_date' => $this->slaCalculator->calculateDueDate(now(), $priority->sla_hours ?? 24),
        ]);

        if ($request->type === 'change_request') {
            TicketApproval::create([
                'ticket_id' => $ticket->id,
                'requested_by' => Auth::id(),
                'justification' => $request->description,
                'approval_level' => 'it_head',
                'status' => 'pending',
            ]);
            $ticket->update(['status' => 'pending']);

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new TicketNotification($ticket, 'approval_required'));
            }
        } else {
            $autoAssignee = \App\Models\AutoAssignRule::findAssignee(
                $request->category_id,
                $request->sub_category_id,
                Auth::user()->company_id, // <-- Gunakan dari Auth User
                $request->vessel_name,
                Auth::user()->source
            );
            if ($autoAssignee) {
                $ticket->update([
                    'assigned_to' => $autoAssignee,
                    'assigned_at' => now(),
                    'status' => 'assigned',
                ]);
                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $autoAssignee,
                    'field' => 'assigned_to',
                    'new_value' => User::find($autoAssignee)->name,
                    'note' => 'Auto-assigned based on rule',
                ]);
            }
        }

        if ($request->filled('assets')) {
            $ticket->assets()->attach($request->assets);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets/' . $ticket->id, 'public');
                $ticket->attachments()->create([
                    'user_id' => Auth::id(),
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $path,
                ]);
            }
        }

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'status',
            'new_value' => 'open',
            'note' => 'Ticket created',
        ]);

        $ticket->load(['requester', 'priority', 'category', 'company']);
        $this->waService->notifyTicketCreated($ticket);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new TicketNotification($ticket, 'created'));
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeTicketAccess($ticket);

        $ticket->load([
            'requester', 'assignee', 'assigner', 'priority', 'category',
            'subCategory', 'comments.user', 'attachments', 'histories.user',
            'rating', 'assets',
        ]);

        $technicians = User::whereIn('role', ['technician', 'admin'])->where('is_active', true)->get();

        $cannedResponses = [];
        if (in_array(Auth::user()->role, ['admin', 'technician'])) {
            $cannedResponses = \App\Models\CannedResponse::where('is_active', true)
                ->where(function ($q) use ($ticket) {
                    $q->where('category_id', $ticket->category_id)->orWhereNull('category_id');
                })
                ->orderByDesc('usage_count')
                ->limit(10)
                ->get();
        }

        return view('tickets.show', compact('ticket', 'technicians', 'cannedResponses'));
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldAssignee = $ticket->assigned_to;
        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'assigned_to',
            'old_value' => $oldAssignee ? User::find($oldAssignee)->name : null,
            'new_value' => User::find($request->assigned_to)->name,
            'note' => 'Ticket assigned to technician',
        ]);

        $ticket->load(['assignee', 'requester', 'priority']);
        $this->waService->notifyTicketAssigned($ticket);

        $ticket->assignee->notify(new TicketNotification($ticket, 'assigned'));

        return back()->with('success', 'Ticket assigned successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,assigned,in_progress,pending,resolved,closed,cancelled',
        ]);

        if ($request->status === 'closed' && $ticket->status === 'resolved' && !$ticket->rating) {
            return back()->with('error', 'Ticket cannot be closed until the requester provides a rating.');
        }

        $oldStatus = $ticket->status;
        $data = ['status' => $request->status];

        if ($request->status === 'resolved') {
            $data['resolved_at'] = now();
            $data['resolution_notes'] = $request->resolution_notes;
        } elseif ($request->status === 'closed') {
            $data['closed_at'] = now();
        } elseif ($request->status === 'in_progress' && !$ticket->first_response_at) {
            $data['first_response_at'] = now();
        }

        $ticket->update($data);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'status',
            'old_value' => $oldStatus,
            'new_value' => $request->status,
            'note' => $request->notes ?? null,
        ]);

        if ($request->status === 'resolved') {
            $ticket->load(['assignee', 'requester', 'priority']);
            $this->waService->notifyTicketResolved($ticket);
            $ticket->requester->notify(new TicketNotification($ticket, 'resolved'));
        }

        return back()->with('success', 'Ticket status updated successfully.');
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        $request->validate(['comment' => 'required|string']);

        $ticket->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'is_internal' => $request->boolean('is_internal'),
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    public function reopen(Request $request, Ticket $ticket)
    {
        if ($ticket->requester_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($ticket->status, ['resolved', 'closed'])) {
            return back()->with('error', 'Only resolved or closed tickets can be reopened.');
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $ticket->update([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
            'sla_breached' => false,
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'status',
            'old_value' => 'closed',
            'new_value' => 'open',
            'note' => 'Reopened: ' . $request->reason,
        ]);

        if ($ticket->assignee) {
            $ticket->load(['requester', 'priority', 'assignee']);
            $ticket->assignee->notify(new TicketNotification($ticket, 'reopened', $request->reason));
        }

        return back()->with('success', 'Ticket reopened successfully.');
    }

    public function getSubCategories($categoryId)
    {
        $subCategories = \App\Models\SubCategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return response()->json($subCategories);
    }

    public function edit(Ticket $ticket)
    {
        $this->authorizeTicketAccess($ticket);

        if (!in_array($ticket->status, ['open', 'assigned'])) {
            return back()->with('error', 'Tickets can only be edited when status is Open or Assigned.');
        }

        $categories = Category::with('subCategories')->where('is_active', true)->get();
        $priorities = Priority::orderBy('sort_order')->get();

        return view('tickets.edit', compact('ticket', 'categories', 'priorities'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeTicketAccess($ticket);

        if (!in_array($ticket->status, ['open', 'assigned'])) {
            return back()->with('error', 'Tickets can only be edited when status is Open or Assigned.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $ticket->update($request->only(['title', 'description', 'category_id', 'priority_id', 'impact', 'location', 'vessel_name']));

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'edited',
            'note' => 'Ticket edited by requester',
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated successfully.');
    }

    private function authorizeTicketAccess(Ticket $ticket): void
    {
        $user = Auth::user();
        if ($user->role === 'user' && $ticket->requester_id !== $user->id) {
            abort(403);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array',
            'action' => 'required|in:assign,close,cancel',
        ]);

        $tickets = Ticket::whereIn('id', $request->ticket_ids)->get();
        $count = 0;

        foreach ($tickets as $ticket) {
            if ($request->action === 'assign' && $request->filled('assign_to')) {
                $ticket->update([
                    'assigned_to' => $request->assign_to,
                    'assigned_at' => now(),
                    'status' => 'assigned',
                ]);
                $count++;
            } elseif ($request->action === 'close') {
                $ticket->update(['status' => 'closed', 'closed_at' => now()]);
                $count++;
            } elseif ($request->action === 'cancel') {
                $ticket->update(['status' => 'cancelled']);
                $count++;
            }
        }

        return back()->with('success', "{$count} tickets updated successfully.");
    }
    
    // ==========================================
    // TAMBAHAN BARU: OPSI 1 (Selesai & Rating)
    // ==========================================
    public function rateAndClose(Request $request, Ticket $ticket)
    {
        // 1. Validasi hanya requester yang bisa melakukan ini
        if ($ticket->requester_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Pastikan status tiket sudah 'resolved' oleh teknisi
        if ($ticket->status !== 'resolved') {
            return back()->with('error', 'Hanya tiket dengan status Resolved yang dapat diulas dan ditutup.');
        }

        // 3. Validasi input rating 1-5 dan komentar
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000'
        ]);

        // 4. Simpan data rating ke tabel ticket_ratings
        $ticket->rating()->create([
            'user_id' => Auth::id(),
            'technician_id' => $ticket->assigned_to,
            'rating' => $request->rating,
            'feedback' => $request->feedback,
            'resolution_time_minutes' => $ticket->resolution_time,
        ]);

        // 5. Ubah status tiket menjadi Closed
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        // 6. Catat log history bahwa tiket ditutup oleh requester beserta ratingnya
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'status',
            'old_value' => 'resolved',
            'new_value' => 'closed',
            'note' => 'Tiket diselesaikan oleh requester dengan rating: ' . $request->rating . ' Bintang',
        ]);

        return back()->with('success', 'Terima kasih! Tiket telah berhasil ditutup dan ulasan Anda telah disimpan.');
    }
}