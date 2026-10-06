<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Company;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    protected WhatsAppService $waService;
    protected SlaCalculator $slaCalculator;

    public function __construct(WhatsAppService $waService, SlaCalculator $slaCalculator)
    {
        $this->waService = $waService;
        $this->slaCalculator = $slaCalculator;
    }

    /**
     * Helper privat untuk menembak notifikasi langsung ke WA Admin
     */
    private function notifyAdminViaWa(Ticket $ticket, string $actionName, string $extraNote = '')
    {
        try {
            $adminPhone = env('WA_DEFAULT_TO', '628563339320'); // Nomor admin
            $message  = "*[ADMIN ALERT - TICKET UPDATE]*\n\n";
            $message .= "Ticket: *{$ticket->ticket_number}*\n";
            $message .= "Title: {$ticket->title}\n";
            $message .= "Action: *{$actionName}*\n";
            $message .= "By: " . Auth::user()->name . "\n";
            if ($extraNote) {
                $message .= "Note: {$extraNote}\n";
            }
            $this->waService->sendMessage($adminPhone, $message);
        } catch (\Exception $e) {
            Log::error('WA Admin Alert Failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper privat BARU: Mengirim notifikasi ke semua pihak
     */
    private function sendStatusUpdateNotifications(Ticket $ticket, string $oldStatus, string $newStatus)
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $recipients = collect();

        if ($ticket->requester_id && $ticket->requester) {
            $recipients->push($ticket->requester);
        }

        if ($ticket->assigned_to && $ticket->assignee) {
            $recipients->push($ticket->assignee);
        }

        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        $recipients = $recipients->merge($admins);

        $finalRecipients = $recipients->filter()->unique('id');

        foreach ($finalRecipients as $user) {
            try {
                $user->notify(new TicketNotification($ticket, $newStatus));
            } catch (\Exception $e) {
                Log::error("Gagal mengirim email notifikasi ke {$user->email}: " . $e->getMessage());
            }
        }
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
        
        // PENAMBAHAN: Buka semua aset & ambil data user jika yang login Admin/Teknisi
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'technician') {
            $assets = Asset::all(); // Admin melihat semua aset dengan cepat
            $users = \App\Models\User::where('is_active', true)->orderBy('name')->get(); // Untuk Dropdown On Behalf Of
        } else {
            $assets = Asset::where('assigned_to', Auth::id())->orWhereNull('assigned_to')->get();
            $users = collect(); // Kosongkan untuk user biasa
        }

        $companies = Company::where('is_active', true)->orderBy('name')->get(); 

        return view('tickets.create', compact('categories', 'priorities', 'assets', 'companies', 'users'));
    }

    public function store(Request $request)
{
    // 1. Validasi Input Form
    $request->validate([
        'requester_id' => 'nullable|exists:users,id', // Validasi On Behalf Of
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'required|exists:categories,id',
        'sub_category_id' => 'nullable|exists:sub_categories,id',
        'priority_id' => 'required|exists:priorities,id',
        'request_type' => 'required|in:incident,service_request,problem,change_request', 
        'impact' => 'nullable|string',
        'urgency' => 'nullable|string',
        'location' => 'nullable|string|max:255',
        'vessel_name' => 'nullable|string|max:255',
        'asset_id' => 'nullable|exists:assets,id',
        'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,mp4,mov,avi|max:20480', 
    ], [
        'attachments.*.mimes' => 'Format file lampiran tidak diizinkan. Gunakan JPG, PNG, PDF, Office, atau Video (MP4/AVI).',
        'attachments.*.max' => 'Ukuran setiap file lampiran maksimal 20MB.'
    ]);

    $priority = Priority::find($request->priority_id);
    $ticketType = $request->request_type;

    // 2. Logika Penentu Requester & Deteksi On Behalf Of
    $actualRequesterId = Auth::id();
    $actualCompanyId = Auth::user()->company_id;
    $isOnBehalf = false;

    if (in_array(Auth::user()->role, ['admin', 'technician']) && $request->filled('requester_id') && $request->requester_id != Auth::id()) {
        $actualRequesterId = $request->requester_id;
        $requestedUser = \App\Models\User::find($request->requester_id);
        if ($requestedUser) {
            $actualCompanyId = $requestedUser->company_id;
        }
        $isOnBehalf = true;
    }

    // 3. Penentuan Status Awal & Kebutuhan Approval
    $initialStatus = 'open';
    $needsApproval = false;
    $approvalType = null;

    if ($ticketType === 'change_request') {
        $initialStatus = 'pending';
        $needsApproval = true;
        $approvalType = 'change_request';
    } elseif ($isOnBehalf) {
        $initialStatus = 'pending';
        $needsApproval = true;
        $approvalType = 'on_behalf';
    }

    // 4. Pembuatan Data Tiket
    $ticket = Ticket::create([
        'ticket_number' => Ticket::generateTicketNumber(),
        'title' => $request->title,
        'description' => $request->description,
        'category_id' => $request->category_id,
        'sub_category_id' => $request->sub_category_id,
        'priority_id' => $request->priority_id,
        'requester_id' => $actualRequesterId,
        'company_id' => $actualCompanyId,
        'type' => $ticketType,
        'impact' => $request->impact ?? 'low',
        'urgency' => $request->urgency ?? 'low',
        'location' => $request->location,
        'vessel_name' => $request->vessel_name,
        'status' => $initialStatus,
        'due_date' => $this->slaCalculator->calculateDueDate(now(), $priority->sla_hours ?? 24),
    ]);

    // 5. Handling Approval atau Auto-Assign Rule
    if ($needsApproval) {
        TicketApproval::create([
            'ticket_id' => $ticket->id,
            'requested_by' => Auth::id(), // User pembuat tiket (Admin/Teknisi)
            'justification' => $isOnBehalf ? 'Pembuatan tiket On Behalf Of membutuhkan approval Atasan/Admin.' : $request->description,
            'approval_level' => $approvalType === 'change_request' ? 'it_head' : 'admin_approval',
            'status' => 'pending',
        ]);
    } else {
        $autoAssignee = \App\Models\AutoAssignRule::findAssignee(
            $request->category_id,
            $request->sub_category_id,
            $actualCompanyId,
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

    // 6. Simpan Relasi Asset
    if ($request->filled('asset_id')) {
        $ticket->assets()->attach($request->asset_id);
    }

    // 7. Simpan Lampiran (Attachments)
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('tickets/' . $ticket->id, 'local'); 
            
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

    // 8. Catat History
    $historyNote = 'Ticket created';
    if ($isOnBehalf) {
        $requesterName = User::find($actualRequesterId)?->name ?? 'User';
        $historyNote .= " On Behalf Of {$requesterName} (Menunggu Approval Admin/Atasan)";
    }

    TicketHistory::create([
        'ticket_id' => $ticket->id,
        'user_id' => Auth::id(),
        'field' => 'status',
        'new_value' => $ticket->status,
        'note' => $historyNote,
    ]);

    // 9. Kirim Notifikasi
    $ticket->load(['requester', 'priority', 'category', 'company', 'attachments']);
    
    $extraNote = 'Kategori: ' . $ticket->category->name;
    if ($isOnBehalf) {
        $extraNote .= "\n\n⚠️ *MEMBUTUHKAN APPROVAL (ON BEHALF OF)* oleh Admin/Atasan.";
    }
    if ($ticket->attachments->count() > 0) {
        $extraNote .= "\n\n*Lampiran:* Tersedia " . $ticket->attachments->count() . " file/video.";
        $extraNote .= "\nAkses detail tiket: " . route('tickets.show', $ticket->id);
    }

    try {
        $this->waService->notifyTicketCreated($ticket);
        $this->notifyAdminViaWa($ticket, $isOnBehalf ? 'Ticket On Behalf Created (Needs Approval)' : 'Ticket Created', $extraNote);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new TicketNotification($ticket, $isOnBehalf ? 'on_behalf_approval_required' : 'created'));
        }
    } catch (\Exception $e) {
        Log::error('Notification Failed: ' . $e->getMessage());
    }

    $successMsg = $isOnBehalf 
        ? 'Tiket berhasil dibuat atas nama pengguna lain dan saat ini menunggu approval Admin/Atasan.' 
        : 'Ticket created successfully.';

    return redirect()->route('tickets.show', $ticket)->with('success', $successMsg);
}

    public function downloadAttachment($ticketId, $attachmentId)
{
    // Menggunakan ID langsung dari parameter URL
    $ticket = \App\Models\Ticket::find($ticketId);
    if (! $ticket) {
        abort(404, 'Tiket tidak ditemukan.');
    }

    $attachment = \App\Models\Attachment::where('id', $attachmentId)
                    ->where('ticket_id', $ticket->id)
                    ->first();

    if (! $attachment) {
        abort(404, 'Berkas lampiran tidak ditemukan.');
    }

    $path = $attachment->path;
    $disk = \Illuminate\Support\Facades\Storage::disk('local')->exists($path) ? 'local' : 'public';

    if (! \Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
        $path = str_replace('storage/', '', $path);
        if (! \Illuminate\Support\Facades\Storage::disk($disk)->exists($path)) {
            abort(404, 'File fisik lampiran kosong atau tidak ada di server.');
        }
    }

    // Jika ada parameter 'mode=view', tampilkan file langsung di browser (tanpa diunduh)
    if (request()->query('mode') === 'view') {
        return \Illuminate\Support\Facades\Storage::disk($disk)->response($path);
    }

    // Jika tidak ada parameter (default), paksa unduh file
    return \Illuminate\Support\Facades\Storage::disk($disk)->download($path, $attachment->original_name);
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
            'note' => 'nullable|string|max:500' // Validasi untuk form catatan
        ]);

        $targetUser = User::find($request->assigned_to);
        
        if (Auth::user()->role === 'technician' && $targetUser->role !== 'admin') {
            return back()->with('error', 'Akses ditolak: Technician hanya dapat melakukan Reassign ke Admin.');
        }

        $oldAssignee = $ticket->assigned_to;
        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        // Prioritaskan catatan dari form, jika kosong gunakan pesan default
        $historyNote = $request->note ?: (Auth::user()->role === 'technician' ? 'Ticket reassigned to Admin' : 'Ticket assigned to technician');

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'assigned_to',
            'old_value' => $oldAssignee ? User::find($oldAssignee)->name : null,
            'new_value' => $targetUser->name,
            'note' => $historyNote, // Simpan catatan
        ]);

        $ticket->load(['assignee', 'requester', 'priority']);
        
        $this->waService->notifyTicketAssigned($ticket);
        $this->notifyAdminViaWa($ticket, 'Ticket Assigned', 'Assigned to: ' . $targetUser->name . ($request->note ? "\nCatatan: " . $request->note : ""));

        $ticket->assignee->notify(new TicketNotification($ticket, 'assigned'));

        return back()->with('success', 'Ticket assigned successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        if ($ticket->status === 'closed' && Auth::user()->role !== 'admin') {
            return back()->with('error', 'Tiket yang sudah ditutup (Closed) terkunci permanen. Hanya Admin yang dapat merubahnya.');
        }

        $request->validate([
            'status' => 'required|in:open,assigned,in_progress,pending,resolved,closed,cancelled',
        ]);

        if (Auth::user()->role === 'technician' && $request->status === 'closed') {
            return back()->with('error', 'Akses ditolak: Technician tidak diizinkan menutup (Close) tiket.');
        }

        if ($request->status === 'closed' && $ticket->status === 'resolved' && !$ticket->rating) {
            if (Auth::user()->role !== 'admin') {
                return back()->with('error', 'Ticket cannot be closed until the requester provides a rating.');
            }
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
            'note' => $request->resolution_notes ?? null,
        ]);

        $ticket->load(['assignee', 'requester', 'priority']);

        $this->notifyAdminViaWa($ticket, 'Status Changed to ' . strtoupper($request->status), $request->resolution_notes ?? '-');
        if ($request->status === 'resolved') {
            $this->waService->notifyTicketResolved($ticket);
        }

        $this->sendStatusUpdateNotifications($ticket, $oldStatus, $request->status);

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

        if ($ticket->status !== 'resolved') {
            return back()->with('error', 'Hanya tiket dengan status Resolved yang dapat dibuka kembali.');
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $ticket->update([
            'status' => 'in_progress',
            'resolved_at' => null, 
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'status',
            'old_value' => 'resolved',
            'new_value' => 'in_progress',
            'note' => 'Reopened oleh Requester: ' . $request->reason,
        ]);

        $this->notifyAdminViaWa($ticket, 'Ticket Reopened', 'Alasan: ' . $request->reason);

        if ($ticket->assignee) {
            $ticket->load(['requester', 'priority', 'assignee']);
            $this->waService->sendMessage(
                $ticket->assignee->phone, 
                "*[TICKET REOPENED]*\n\nTiket {$ticket->ticket_number} dibuka kembali oleh Requester dengan alasan:\n{$request->reason}\n\nStatus saat ini kembali menjadi *In Progress*."
            );
            $ticket->assignee->notify(new TicketNotification($ticket, 'reopened', $request->reason));
        }

        $this->sendStatusUpdateNotifications($ticket, 'resolved', 'in_progress');

        return back()->with('success', 'Tiket belum selesai. Status dikembalikan ke In Progress dan Teknisi telah diberitahu.');
    }

    public function getSubCategories($categoryId)
    {
        return response()->json(
            SubCategory::where('category_id', $categoryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name'])
        );
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

        if ($request->action === 'close' && Auth::user()->role === 'technician') {
            return back()->with('error', 'Akses ditolak: Technician tidak diizinkan melakukan Close tiket massal.');
        }

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

    public function rateAndClose(Request $request, Ticket $ticket)
    {
        if ($ticket->requester_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($ticket->status !== 'resolved') {
            return back()->with('error', 'Hanya tiket dengan status Resolved yang dapat diulas dan ditutup.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000'
        ]);

        $ticket->rating()->create([
            'user_id' => Auth::id(),
            'technician_id' => $ticket->assigned_to,
            'rating' => $request->rating,
            'feedback' => $request->feedback,
            'resolution_time_minutes' => $ticket->resolution_time,
        ]);

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'field' => 'status',
            'old_value' => 'resolved',
            'new_value' => 'closed',
            'note' => 'Tiket diselesaikan oleh requester dengan rating: ' . $request->rating . ' Bintang',
        ]);

        try {
            $ticket->load(['assignee', 'rating']);
            
            $this->waService->notifyTicketClosed($ticket);
            $this->notifyAdminViaWa($ticket, 'Ticket Closed & Rated', 'Rating: ' . $request->rating . '/5');

            if ($ticket->assignee) {
                $ticket->assignee->notify(new TicketNotification($ticket, 'closed'));
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim WA Rate & Close: ' . $e->getMessage());
        }

        return back()->with('success', 'Terima kasih! Tiket telah berhasil ditutup dan ulasan Anda telah disimpan.');
    }
}