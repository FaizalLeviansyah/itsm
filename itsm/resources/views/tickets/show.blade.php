@extends('layouts.app')
@section('title', $ticket->ticket_number)

@section('content')
<!-- Ticket Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <span class="text-sm text-gray-500">TICKET {{ $ticket->ticket_number }}</span>
            @php $priorityBg = ['Critical'=>'bg-red-100 text-red-700','High'=>'bg-orange-100 text-orange-700','Medium'=>'bg-amber-100 text-amber-700','Low'=>'bg-gray-100 text-gray-600']; @endphp
            <span class="text-xs font-bold uppercase px-2 py-0.5 rounded {{ $priorityBg[$ticket->priority->name] ?? 'bg-gray-100 text-gray-600' }}">{{ $ticket->priority->name }} Priority</span>
            @if($ticket->is_overdue)
            <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded animate-pulse">⚠️ SLA BREACH</span>
            @endif
        </div>
        <h1 class="text-xl font-bold text-gray-900">{{ $ticket->title }}</h1>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
        @if(in_array($ticket->status, ['open', 'assigned']) && $ticket->requester_id === Auth::id())
        <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            <i class="fas fa-edit text-xs text-gray-400"></i> Edit
        </a>
        @endif
        @can('manageTickets')
            @if(Auth::user()->role === 'admin')
            <button onclick="document.getElementById('assign-modal').classList.toggle('hidden')" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <i class="fas fa-{{ $ticket->assigned_to ? 'exchange-alt' : 'user-plus' }} text-xs text-gray-400"></i> 
                {{ $ticket->assigned_to ? 'Reassign' : 'Assign' }}
            </button>
            @elseif(Auth::user()->role === 'technician')
            <button onclick="document.getElementById('assign-modal').classList.toggle('hidden')" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <i class="fas fa-share text-xs text-gray-400"></i> Reassign to Admin
            </button>
            @endif

            <button onclick="document.getElementById('status-modal').classList.toggle('hidden')" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <i class="fas fa-tasks text-xs text-gray-400"></i> Change Status
            </button>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- START TAMBAHAN: BANNER & ACTION APPROVAL ADMIN / ATASAN -->
        @if($ticket->status === 'pending' && $ticket->approvals->where('status', 'pending')->count() > 0)
            <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-5 mb-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-exclamation-triangle text-amber-600 text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-amber-900">Tiket Menunggu Approval</h3>
                            <p class="text-xs text-amber-800 mt-0.5">
                                Tiket ini dibuat oleh <strong>{{ $ticket->histories->first()?->user->name ?? 'Admin/Teknisi' }}</strong> 
                                atas nama <strong>{{ $ticket->requester->name }}</strong>. Membutuhkan verifikasi Admin/Atasan agar dapat dijalankan.
                            </p>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'admin')
                        <div class="flex items-center gap-2 shrink-0">
                            <!-- Form Reject -->
                            <form action="{{ route('tickets.status', $ticket) }}" method="POST" onsubmit="return confirm('Tolak tiket ini?')">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <input type="hidden" name="resolution_notes" value="Ditolak oleh Admin/Atasan saat tahapan Approval On Behalf.">
                                <button type="submit" class="px-3.5 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </form>

                            <!-- Form Approve -->
                            <form action="{{ route('tickets.status', $ticket) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="open">
                                <input type="hidden" name="resolution_notes" value="Disetujui oleh Admin/Atasan (Approved On Behalf).">
                                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition shadow flex items-center gap-1.5">
                                    <i class="fas fa-check"></i> Approve & Process
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        <!-- END TAMBAHAN: BANNER & ACTION APPROVAL -->
        <!-- Description -->
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-gray-500 text-sm"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">Issue Description</h3>
            </div>
            <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $ticket->description }}</div>

            @if($ticket->attachments->count() > 0)
            <div class="mt-5 pt-5 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Attachments ({{ $ticket->attachments->count() }})</p>
                <div class="space-y-2">
                    @foreach($ticket->attachments as $att)
                        @php
                            $ext = pathinfo($att->path, PATHINFO_EXTENSION);
                            $viewUrl = route('tickets.attachment.download', ['ticketId' => $ticket->id, 'attachmentId' => $att->id, 'mode' => 'view']);
                            $downloadUrl = route('tickets.attachment.download', ['ticketId' => $ticket->id, 'attachmentId' => $att->id]);
                            $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isVideo = in_array(strtolower($ext), ['mp4', 'webm', 'mov']);
                        @endphp

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <!-- Klik area teks/ikon untuk membuka Preview Modal -->
                            <div onclick="openPreviewModal('{{ $viewUrl }}', '{{ $downloadUrl }}', '{{ addslashes($att->original_name) }}', '{{ $ext }}')" 
                                class="flex items-center gap-3 flex-1 cursor-pointer pr-4">
                                <div class="w-8 h-8 bg-white border rounded flex items-center justify-center shrink-0">
                                    @if($isImg)
                                        <i class="fas fa-image text-blue-500 text-xs"></i>
                                    @elseif($isVideo)
                                        <i class="fas fa-video text-purple-500 text-xs"></i>
                                    @else
                                        <i class="fas fa-file text-gray-400 text-xs"></i>
                                    @endif
                                </div>
                                <span class="text-sm text-gray-700 flex-1 truncate" title="{{ $att->original_name }}">{{ $att->original_name }}</span>
                            </div>

                            <!-- Tombol khusus untuk Download Fisik File -->
                            <a href="{{ $downloadUrl }}" class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-200 rounded transition" title="Unduh File">
                                <i class="fas fa-download text-xs"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Rating (required) -->
        @if($ticket->status === 'resolved' && $ticket->requester_id === Auth::id() && !$ticket->rating)
        <div class="bg-white rounded-xl border-2 border-amber-300 p-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fas fa-star text-amber-500"></i></div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">⭐ Rating Required</h3>
                    <p class="text-xs text-gray-500">The ticket will not close until you submit a rating.</p>
                </div>
            </div>
            <form action="{{ route('tickets.rateAndClose', $ticket) }}" method="POST" class="space-y-4 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Overall Rating *</label>
                    <div class="flex gap-1" id="star-rating">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" onclick="setRating({{ $i }})" class="star text-4xl text-gray-200 hover:text-amber-400 transition cursor-pointer" data-value="{{ $i }}">★</button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating-value" required>
                </div>
                
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Feedback (optional)</label>
                    <textarea name="feedback" rows="3" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm" placeholder="Add additional comments..."></textarea>
                </div>
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-lg text-sm font-semibold transition">
                    Submit Rating & Close Ticket
                </button>
            </form>
        </div>
        @endif

        <!-- Rating Display -->
        @if($ticket->rating)
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-amber-400 text-lg">@for($i=1;$i<=5;$i++){!! $i <= $ticket->rating->rating ? '★' : '<span class="text-gray-200">★</span>' !!}@endfor</span>
                <span class="text-sm font-semibold text-gray-700">{{ $ticket->rating->rating }}/5</span>
            </div>
            @if($ticket->rating->feedback)<p class="text-sm text-gray-600 italic">"{{ $ticket->rating->feedback }}"</p>@endif
            @if($ticket->rating->resolution_time_minutes)<p class="text-xs text-gray-400 mt-2">Resolution time: {{ floor($ticket->rating->resolution_time_minutes/60) }}h {{ $ticket->rating->resolution_time_minutes%60 }}m</p>@endif
        </div>
        @endif

        <!-- KODE BARU: REOPEN TICKET -->
        @if($ticket->status === 'resolved' && $ticket->requester_id === Auth::id())
        <div class="bg-white rounded-xl border border-red-100 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-red-50 rounded-lg flex items-center justify-center"><i class="fas fa-redo text-red-500 text-sm"></i></div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Issue not resolved?</h3>
                    <p class="text-xs text-gray-500">Reopen the ticket if the problem persists after resolution.</p>
                </div>
            </div>
            <form action="{{ route('tickets.reopen', $ticket) }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="reason" required placeholder="Explain why the issue is not resolved..." class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Reopen</button>
            </form>
        </div>
        @endif

        <!-- Activity & Comments -->
        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-history text-gray-500 text-sm"></i></div>
                    <h3 class="text-base font-semibold text-gray-900">Activity & Conversation</h3>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                @foreach($ticket->histories->take(5) as $history)
                <div class="flex gap-3">
                    <div class="w-8 h-8 bg-blue-50 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-bolt text-blue-500 text-xs"></i>
                    </div>
                    <div class="flex-1 bg-blue-50/50 border border-blue-100 rounded-lg p-3">
                        <p class="text-sm text-gray-700">
                            <!-- Amankan history user -->
                            <span class="font-medium">{{ $history->user?->name ?? 'System / User Terhapus' }}</span> •
                            @if($history->field === 'status') changed status to 
                                <span class="font-medium">
                                    {{ $history->new_value === 'resolved' ? 'Resolved (waiting User Confirmation)' : ucfirst(str_replace('_',' ',$history->new_value)) }}
                                </span>
                            @elseif($history->field === 'assigned_to') assigned to <span class="font-medium">{{ $history->new_value }}</span>
                            @else {{ $history->note ?? "updated {$history->field}" }}
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ $history->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach

                @foreach($ticket->comments as $comment)
                <div class="flex gap-3">
                    <div class="w-8 h-8 {{ $comment->is_internal ? 'bg-amber-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="text-[10px] font-bold {{ $comment->is_internal ? 'text-amber-700' : 'text-gray-600' }}">{{ strtoupper(substr($comment->user?->name ?? 'NA', 0, 2)) }}</span>
                    </div>
                    <div class="flex-1 {{ $comment->is_internal ? 'bg-amber-50 border-amber-100' : 'bg-gray-50 border-gray-100' }} border rounded-lg p-3">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-gray-800">{{ $comment->user?->name ?? 'User Non-Aktif' }}</span>
                            @if($comment->is_internal)<span class="text-[10px] bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded font-medium">Internal</span>@endif
                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        
                        {{-- Isi Komentar --}}
                        <p class="text-sm text-gray-700 whitespace-pre-wrap mb-2">{{ $comment->comment }}</p>

                        {{-- Preview Lampiran (Attachment) Milik Komentar Ini --}}
                        @if($comment->attachments && $comment->attachments->count() > 0)
                        <div class="mt-2 pt-2 border-t border-gray-200/60 flex flex-wrap gap-2">
                            @foreach($comment->attachments as $att)
                                @php
                                    $ext = strtolower(pathinfo($att->path, PATHINFO_EXTENSION));
                                    $viewUrl = route('tickets.attachment.download', ['ticketId' => $ticket->id, 'attachmentId' => $att->id, 'mode' => 'view']);
                                    $downloadUrl = route('tickets.attachment.download', ['ticketId' => $ticket->id, 'attachmentId' => $att->id]);
                                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    $isVideo = in_array($ext, ['mp4', 'mov', 'avi', 'mkv']);
                                @endphp

                                <div class="relative group bg-white border border-gray-200 rounded-md p-1.5 flex items-center gap-2 shadow-sm">
                                    @if($isImg)
                                        <a href="{{ $viewUrl }}" target="_blank" class="block w-10 h-10 overflow-hidden rounded flex-shrink-0">
                                            <img src="{{ $viewUrl }}" class="w-full h-full object-cover">
                                        </a>
                                    @elseif($isVideo)
                                        <div class="w-10 h-10 bg-purple-50 rounded flex items-center justify-center text-purple-600 flex-shrink-0">
                                            <i class="fas fa-video text-xs"></i>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 bg-gray-50 rounded flex items-center justify-center text-gray-500 flex-shrink-0">
                                            <i class="fas fa-file-archive text-xs"></i>
                                        </div>
                                    @endif
                                    
                                    <div class="text-xs pr-1">
                                        <a href="{{ $downloadUrl }}" class="font-medium text-gray-700 hover:text-blue-600 truncate max-w-[130px] block" title="{{ $att->original_name }}">
                                            {{ $att->original_name }}
                                        </a>
                                        <span class="text-[10px] uppercase text-gray-400">(.{{ $ext }})</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif

                    </div>
                </div>
                @endforeach
            </div>

            <!-- Add Comment -->
            @if(!in_array($ticket->status, ['closed', 'cancelled']))
            <!-- TAMBAHKAN enctype="multipart/form-data" -->
            <form action="{{ route('tickets.comment', $ticket) }}" method="POST" class="border-t border-gray-100 pt-5" enctype="multipart/form-data">
                @csrf
                @can('manageTickets')
                @if(isset($cannedResponses) && $cannedResponses->count() > 0)
                <div class="mb-3 flex flex-wrap gap-1.5">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase mr-1 self-center">Quick:</span>
                    @foreach($cannedResponses as $canned)
                    <button type="button" onclick="document.getElementById('comment-box').value='{{ addslashes($canned->content) }}'" class="text-[11px] px-2.5 py-1 bg-gray-100 hover:bg-brand-50 hover:text-brand-700 text-gray-600 rounded-full transition">{{ $canned->title }}</button>
                    @endforeach
                </div>
                @endif
                @endcan
                <div class="flex gap-3">
                    <div class="w-8 h-8 bg-brand-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-[10px] font-bold text-brand-700">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="flex-1">
                        <textarea name="comment" id="comment-box" rows="3" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" placeholder="Write a note or reply..."></textarea>
                        
                        <!-- AREA UPLOAD ATTACHMENT KOMENTAR -->
                        <div class="mt-3">
                            <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center hover:border-brand-400 transition cursor-pointer relative group bg-gray-50/50">
                                <div class="flex flex-col items-center justify-center pointer-events-none gap-1">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-paperclip text-gray-400 group-hover:text-brand-500 transition"></i>
                                        <p class="text-xs text-gray-600 font-medium">Click or drag files here to attach</p>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-medium">Mendukung semua format file (ZIP, RAR, Foto, Video, Dokumen)</p>
                                </div>
                                <!-- Hapus atribut accept agar semua ekstensi diizinkan -->
                               <input type="file" id="comment-file-upload" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.tar,.7z,.mp4,.mov,.avi,.mkv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewCommentFiles()">
                                <p class="text-xs text-gray-600 font-medium">Click or drag files here to attach (ZIP, RAR, Foto, Video, Dokumen)</p>
                            </div>
                            <!-- Container Preview File sebelum dikirim -->
                            <div id="comment-file-preview-container" class="mt-3 flex flex-wrap gap-2 hidden"></div>
                        </div>
                        <!-- AKHIR AREA UPLOAD -->

                        <div class="flex items-center justify-between mt-3">
                            @can('manageTickets')
                            <label class="flex items-center gap-2 text-sm text-gray-500">
                                <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                Internal note
                            </label>
                            @else<div></div>@endcan
                            <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                                Send Reply
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-5">
        <!-- Requester Info -->
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Requester Info</p>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold text-gray-600">{{ strtoupper(substr($ticket->requester?->name ?? 'NA', 0, 2)) }}</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $ticket->requester?->name ?? 'User Non-Aktif' }}</p>
                    <p class="text-xs text-gray-500">{{ $ticket->requester?->position ?? ($ticket->requester?->role ?? 'N/A') }}</p>
                </div>
            </div>
            <div class="space-y-2 text-sm">
                @if($ticket->company)
                <div class="flex justify-between">
                    <span class="text-gray-500">Company</span>
                    <span class="text-gray-800 font-medium">{{ $ticket->company->name }}</span>
                </div>
                @endif
                @if($ticket->requester?->department)<div class="flex justify-between"><span class="text-gray-500">Department</span><span class="text-gray-800 font-medium">{{ $ticket->requester->department }}</span></div>@endif
                @if($ticket->location)<div class="flex justify-between"><span class="text-gray-500">Location</span><span class="text-gray-800 font-medium">{{ $ticket->location }}</span></div>@endif
                @if($ticket->requester?->phone)<div class="flex justify-between"><span class="text-gray-500">Contact</span><span class="text-brand-600 font-medium">{{ $ticket->requester->phone }}</span></div>@endif
            </div>
        </div>

        <!-- ASSIGNEE INFO DENGAN TIMELINE TRAIL -->
        <div class="bg-white rounded-xl border border-gray-100 p-6 mb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Assignee Info</h3>
            
            @if($ticket->assignee)
                <div class="flex items-center gap-3">
                    <!-- Avatar Inisial Teknisi -->
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        {{ strtoupper(substr($ticket->assignee->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $ticket->assignee->name }}</div>
                        <div class="text-sm text-gray-500 capitalize">{{ $ticket->assignee->role ?? 'Technician' }}</div>
                    </div>
                </div>
            @else
                <!-- State Belum Di-assign -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-50 text-gray-400 border border-gray-200 border-dashed flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-gray-500">Unassigned</div>
                        <div class="text-sm text-gray-400">Belum ada teknisi</div>
                    </div>
                </div>
            @endif

            <!-- TIMELINE REASSIGN HISTORY -->
            @php
                $assignmentHistories = $ticket->histories->where('field', 'assigned_to')->sortByDesc('created_at');
            @endphp
            
            @if($assignmentHistories->count() > 0)
                <div class="mt-6 pt-5 border-t border-gray-100">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-4">Assignment Trail</h4>
                    
                    <div class="space-y-4 pl-2 border-l-2 border-gray-100 ml-2">
                        @foreach($assignmentHistories as $history)
                            <div class="relative pl-5">
                                <!-- Titik Timeline -->
                                <div class="absolute -left-[23px] top-1 w-2.5 h-2.5 rounded-full bg-blue-400 ring-4 ring-white"></div>
                                
                                <div class="text-xs text-gray-600 mb-1">
                                    <span class="font-semibold text-gray-800">{{ $history->user->name ?? 'System' }}</span> 
                                    assigned to 
                                    <span class="font-semibold text-blue-600">{{ $history->new_value }}</span>
                                </div>
                                
                                <!-- Menampilkan Catatan Alasan -->
                                @if($history->note)
                                    <div class="text-xs text-gray-600 italic bg-blue-50/50 p-2.5 rounded border border-blue-100/50 mt-1.5 mb-1">
                                        <i class="fas fa-quote-left text-blue-300 mr-1"></i> {{ $history->note }}
                                    </div>
                                @endif
                                
                                <!-- Waktu Spesifik -->
                                <div class="text-[10px] font-medium text-gray-400 flex items-center gap-1 mt-1.5">
                                    <i class="far fa-clock"></i> {{ $history->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Assets -->
        @if($ticket->assets->count() > 0)
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Device Details</p>
            @foreach($ticket->assets as $asset)
            <a href="{{ route('assets.show', $asset) }}" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-purple-100 rounded flex items-center justify-center"><i class="fas fa-server text-purple-600 text-xs"></i></div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $asset->name }}</p>
                        <p class="text-xs text-gray-500">{{ $asset->asset_tag }}</p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif

        <!-- SLA Info -->
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">SLA Target</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Resolution Time</span>
                    @if($ticket->due_date && !in_array($ticket->status, ['resolved','closed']))
                        @if($ticket->is_overdue)
                        <span class="text-sm font-bold text-red-600">Overdue</span>
                        @else
                        <span class="text-sm font-bold text-green-600">{{ $ticket->due_date->diffForHumans(null, true) }} remaining</span>
                        @endif
                    @else
                        <span class="text-sm text-gray-600">-</span>
                    @endif
                </div>
                @if($ticket->due_date)
                <div class="w-full bg-gray-100 rounded-full h-2">
                    @php
                        $totalTime = $ticket->created_at->diffInMinutes($ticket->due_date);
                        $elapsed = $ticket->created_at->diffInMinutes(now());
                        $progress = $totalTime > 0 ? min(100, ($elapsed / $totalTime) * 100) : 0;
                    @endphp
                    <div class="h-2 rounded-full {{ $progress > 90 ? 'bg-red-500' : ($progress > 70 ? 'bg-amber-500' : 'bg-brand-500') }}" style="width: {{ $progress }}%"></div>
                </div>
                @endif
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">SLA Level</span>
                    <span class="text-gray-700 font-medium">{{ $ticket->priority->name }} ({{ $ticket->priority->sla_hours }}h Resolution)</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Status</span>
                    @php $sc = ['open'=>'text-blue-600','assigned'=>'text-sky-600','in_progress'=>'text-indigo-600','pending'=>'text-amber-600','resolved'=>'text-yellow-600','closed'=>'text-gray-500','cancelled'=>'text-red-600']; @endphp
                    <span class="font-medium {{ $sc[$ticket->status] ?? '' }}">
                        {{ $ticket->status === 'resolved' ? 'Resolved (waiting User Confirmation)' : ucfirst(str_replace('_',' ',$ticket->status)) }}
                    </span>
                </div>
                @if($ticket->assignee)
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Assignee</span>
                    <span class="text-gray-700 font-medium">{{ $ticket->assignee?->name ?? 'Teknisi Non-Aktif' }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Ticket Meta -->
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Ticket Details</p>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between"><span class="text-gray-500">Created</span><span class="text-gray-700">{{ $ticket->created_at->format('d M Y H:i') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Type</span><span class="text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->type)) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Category</span><span class="text-gray-700">{{ $ticket->category->name }}</span></div>
                @if($ticket->vessel_name)<div class="flex justify-between"><span class="text-gray-500">Vessel</span><span class="text-gray-700">{{ $ticket->vessel_name }}</span></div>@endif
                @if($ticket->resolved_at)<div class="flex justify-between"><span class="text-gray-500">Resolved</span><span class="text-green-600">{{ $ticket->resolved_at->format('d M Y H:i') }}</span></div>@endif
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
@can('manageTickets')
<div id="assign-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            @if(Auth::user()->role === 'admin')
                {{ $ticket->assigned_to ? 'Reassign Ticket' : 'Assign Ticket' }}
            @else
                Reassign to Admin
            @endif
        </h3>
        <form action="{{ route('tickets.assign', $ticket) }}" method="POST">
            @csrf
            
            <!-- Pilihan Teknisi/Admin -->
            <select name="assigned_to" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm mb-4">
                @if(Auth::user()->role === 'admin')
                    <option value="">Select Technician / Admin</option>
                    @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" {{ $ticket->assigned_to == $tech->id ? 'selected' : '' }}>{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                    @endforeach
                @else
                    <option value="">Pilih Admin untuk Reassign</option>
                    @foreach(\App\Models\User::where('role', 'admin')->get() as $admin)
                    <option value="{{ $admin->id }}">{{ $admin->name }} (Admin)</option>
                    @endforeach
                @endif
            </select>

            <!-- INPUT CATATAN BARU DITAMBAHKAN DI SINI -->
            <textarea name="note" rows="2" class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm mb-4" placeholder="{{ Auth::user()->role === 'admin' ? 'Catatan / Alasan Reassign (Opsional)...' : 'Alasan eskalasi ke Admin (Wajib diisi jika perlu)...' }}"></textarea>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('assign-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-lg text-sm">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-semibold">
                    @if(Auth::user()->role === 'admin')
                        {{ $ticket->assigned_to ? 'Reassign' : 'Assign' }}
                    @else
                        Reassign
                    @endif
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Status Modal -->
<div id="status-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/30" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Status</h3>
        <form action="{{ route('tickets.status', $ticket) }}" method="POST">
            @csrf
            <select name="status" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm mb-3">
                @php
                    $statuses = ['open','assigned','in_progress','pending','resolved','cancelled'];
                    if(Auth::user()->role === 'admin') {
                        $statuses[] = 'closed';
                    }
                    if(!in_array($ticket->status, $statuses)) {
                        $statuses[] = $ticket->status;
                    }
                @endphp
                @foreach($statuses as $s)
                <option value="{{ $s }}" {{ $ticket->status == $s ? 'selected' : '' }}>
                    {{ $s === 'resolved' ? 'Resolved (waiting User Confirmation)' : ucfirst(str_replace('_',' ',$s)) }}
                </option>
                @endforeach
            </select>
            <textarea name="resolution_notes" rows="3" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm mb-4" placeholder="Resolution notes (optional)..."></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('status-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-lg text-sm">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-semibold">Update</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
// Array untuk menampung file upload balasan
let selectedCommentFiles = [];

function previewCommentFiles() {
    const input = document.getElementById('comment-file-upload');
    
    Array.from(input.files).forEach(file => {
        selectedCommentFiles.push(file);
    });

    updateCommentFileInputAndPreview();
}

function removeCommentFile(index) {
    selectedCommentFiles.splice(index, 1);
    updateCommentFileInputAndPreview();
}

function updateCommentFileInputAndPreview() {
    const container = document.getElementById('comment-file-preview-container');
    const input = document.getElementById('comment-file-upload');
    
    // Perbarui data transfer form
    const dataTransfer = new DataTransfer();
    selectedCommentFiles.forEach(file => dataTransfer.items.add(file));
    input.files = dataTransfer.files;

    // Render ulang preview UI
    container.innerHTML = '';
    if (selectedCommentFiles.length > 0) {
        container.classList.remove('hidden');
        selectedCommentFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const isImage = file.type.startsWith('image/');
                const previewContent = isImage 
                    ? `<img src="${e.target.result}" class="w-full h-full object-cover rounded-md">`
                    : `<div class="w-full h-full bg-gray-100 rounded-md flex flex-col items-center justify-center p-1 text-center">
                           <i class="fas fa-file-alt text-gray-400 text-sm mb-1"></i>
                           <span class="text-[8px] text-gray-500 truncate w-full">${file.name}</span>
                       </div>`;

                const card = document.createElement('div');
                card.className = 'relative w-16 h-16 group/item bg-white p-0.5 rounded-md border border-gray-200 shrink-0 shadow-sm';
                card.innerHTML = `
                    ${previewContent}
                    <button type="button" onclick="removeCommentFile(${index})" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] shadow hover:bg-red-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(card);
            }
            reader.readAsDataURL(file);
        });
    } else {
        container.classList.add('hidden');
    }
}
</script>
<script>
function setRating(value) {
    document.getElementById('rating-value').value = value;
    document.querySelectorAll('.star').forEach((star, i) => {
        star.classList.toggle('text-amber-400', i < value);
        star.classList.toggle('text-gray-200', i >= value);
    });
}
</script>
@endpush