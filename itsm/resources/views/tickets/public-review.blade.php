<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Support Service Review - #{{ $ticket->ticket_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen py-6 px-4">

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Header Info -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white text-center md:text-left">
            <span class="text-xs uppercase tracking-widest font-semibold text-blue-200">Ticket #{{ $ticket->ticket_number }}</span>
            <h1 class="text-xl md:text-2xl font-bold mt-1 leading-snug">{{ $ticket->title }}</h1>
        </div>

        <div class="p-5 md:p-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-600 text-lg"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-red-600 text-lg"></i> {{ session('error') }}
                </div>
            @endif

            <!-- 1. Initial Request / Issue Details -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 md:p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                    <i class="fas fa-file-alt text-gray-400"></i> Initial Issue / Request Details
                </h3>
                <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">{{ $ticket->description ?? 'No detailed description provided.' }}</p>
            </div>

            <!-- 2. STATUS-BASED DISPLAY -->

            @if($ticket->status === 'closed' && $ticket->rating)
                <!-- A. CLOSED STATUS WITH RATED RECORD -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-center">
                    <div class="text-3xl mb-2 text-amber-400">
                        @for($i=1; $i<=5; $i++)
                            {!! $i <= $ticket->rating->rating ? '★' : '<span class="text-gray-200">★</span>' !!}
                        @endfor
                    </div>
                    <h3 class="text-base font-bold text-gray-800">Thank You for Your Feedback!</h3>
                    <p class="text-xs text-gray-500 mt-1">This ticket has been officially closed.</p>
                    @if($ticket->rating->feedback)
                        <p class="text-xs text-gray-600 italic mt-2 bg-white/80 p-2.5 rounded border border-amber-100">"{{ $ticket->rating->feedback }}"</p>
                    @endif
                </div>

            @elseif($ticket->status === 'resolved')
                <!-- B. RESOLVED STATUS: RATING FORM & REOPEN OPTION -->
                <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-4 md:p-5">
                    <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-blue-600"></i> Your Issue Has Been Resolved
                    </h3>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $ticket->resolution_notes ?? 'Technical work has been completed by our IT team.' }}</p>

                    @php
                        $techNames = collect();
                        if ($ticket->assignedTechnicians && $ticket->assignedTechnicians->count() > 0) {
                            $techNames = $ticket->assignedTechnicians->pluck('name');
                        } elseif ($ticket->assignee) {
                            $techNames->push($ticket->assignee->name);
                        }
                    @endphp
                    <div class="mt-3 pt-3 border-t border-blue-100 text-xs text-gray-500 flex items-center gap-2">
                        <i class="fas fa-user-check text-blue-600"></i> Handled by: 
                        <span class="font-semibold text-gray-800">{{ $techNames->join(', ') ?: 'IT Support Team' }}</span>
                    </div>
                </div>

                <!-- B. RESOLVED STATUS: RATING FORM & REOPEN OPTION -->
                @php
                    $assignedTechs =$ticket->assignedTechnicians;
                    if ($assignedTechs->isEmpty() &&$ticket->assignee) {
                        $assignedTechs = collect([$ticket->assignee]);
                    }
                @endphp

                <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-4 md:p-5 mb-5">
                    <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-blue-600"></i> Your Issue Has Been Resolved
                    </h3>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $ticket->resolution_notes ?? 'Technical work has been completed by our IT team.' }}</p>

                    <div class="mt-3 pt-3 border-t border-blue-100 text-xs text-gray-500 flex items-center gap-2">
                        <i class="fas fa-user-check text-blue-600"></i> Handled by: 
                        <span class="font-semibold text-gray-800">{{ $assignedTechs->pluck('name')->join(', ') ?: 'IT Support Team' }}</span>
                    </div>
                </div>

                <!-- Multi-Technician Rating Form -->
                <form action="{{ route('ticket.public.submit', $ticket->public_token) }}" method="POST" id="review-form" class="space-y-6">
                    @csrf
                    <input type="hidden" name="action" value="rate">

                    <!-- GRID CARD TERPISAH DINAMIS UNTUK TIAP TEKNISI -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($assignedTechs as$tech)
                            <div class="bg-white p-5 rounded-xl border border-blue-100 shadow-xs space-y-3">
                                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($tech->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-900">{{ $tech->name }}</h4>
                                        <span class="text-[10px] text-gray-500 capitalize">{{ $tech->role ?? 'Technician' }}</span>
                                    </div>
                                </div>

                                <!-- Star Rating per Teknisi -->
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 mb-1">Rate Performance *</label>
                                    <div class="flex items-center gap-1" id="star-container-{{ $tech->id }}">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button type="button" onclick="setTechRating({{ $tech->id }}, {{$i }})" 
                                                    class="star-btn-{{ $tech->id }} text-2xl text-gray-300 hover:text-amber-400 focus:outline-none transition-transform hover:scale-110 cursor-pointer" 
                                                    data-value="{{ $i }}">★</button>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="ratings[{{ $tech->id }}]" id="rating-value-{{ $tech->id }}" required>
                                    <p id="rating-text-{{ $tech->id }}" class="text-[10px] font-semibold text-amber-600 mt-1 min-h-[14px]"></p>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-gray-700 uppercase mb-1">Feedback for {{ explode(' ', $tech->name)[0] }} (Opt)</label>
                                    <input type="text" name="feedbacks[{{ $tech->id }}]" class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-blue-500" placeholder="Optional comment...">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Feedback Umum Tiket -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Additional Overall Feedback (Optional)</label>
                        <textarea name="feedback" rows="2" class="w-full border border-gray-200 rounded-xl p-3 text-xs focus:ring-2 focus:ring-blue-500" placeholder="Write overall comments regarding the technical service..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl text-sm transition shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <i class="fas fa-paperplane"></i> Submit Ratings & Close Ticket
                    </button>
                </form>

                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-gray-200"></div>
                    <span class="flex-shrink mx-4 text-xs font-semibold text-gray-400 uppercase">Or</span>
                    <div class="flex-grow border-t border-gray-200"></div>
                </div>

                <!-- Reopen Accordion Form -->
                <div class="border border-red-200 rounded-xl bg-red-50/40 p-4">
                    <details class="group">
                        <summary class="flex justify-between items-center font-bold text-xs text-red-700 cursor-pointer list-none">
                            <span class="flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Issue Not Resolved Yet? Click here to Reopen</span>
                            <span class="transition group-open:rotate-180"><i class="fas fa-chevron-down"></i></span>
                        </summary>
                        <form action="{{ route('ticket.public.submit', $ticket->public_token) }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="action" value="reopen">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Please explain what is still not working *</label>
                                <textarea name="reason" rows="2" required class="w-full border border-red-200 rounded-lg p-2.5 text-xs bg-white focus:ring-2 focus:ring-red-500" placeholder="e.g., Printer is still unresponsive when printing..."></textarea>
                            </div>
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 rounded-lg text-xs transition">
                                Request Reopen Ticket
                            </button>
                        </form>
                    </details>
                </div>

            @else
                <!-- C. TICKET IN PROGRESS / NOT RESOLVED YET -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center space-y-3">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto text-xl animate-pulse">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="text-base font-bold text-blue-900">Ticket Currently In Progress</h3>
                    <p class="text-xs text-gray-600 leading-relaxed max-w-md mx-auto">
                        Your ticket status is currently <span class="font-bold uppercase text-blue-700">{{ str_replace('_', ' ', $ticket->status) }}</span>. The rating form will automatically become active once our technician resolves the issue.
                    </p>
                    <div class="inline-block bg-white border border-blue-200 rounded-lg px-4 py-2 text-xs font-semibold text-gray-700">
                        Current Status: <span class="text-blue-600 font-bold uppercase">{{ $ticket->status }}</span>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

<script>
function setTechRating(techId, value) {
    const inputEl = document.getElementById(`rating-value-${techId}`);
    if (inputEl) {
        inputEl.value = value;
    }

    const stars = document.querySelectorAll(`.star-btn-${techId}`);
    const label = document.getElementById(`rating-text-${techId}`);
    
    const labels = {
        1: '1/5 - Poor',
        2: '2/5 - Unsatisfactory',
        3: '3/5 - Average',
        4: '4/5 - Good & Fast',
        5: '5/5 - Excellent'
    };

    stars.forEach((star, index) => {
        if (index < value) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-amber-400');
        } else {
            star.classList.remove('text-amber-400');
            star.classList.add('text-gray-300');
        }
    });

    if (label) {
        label.innerText = labels[value] || '';
    }
}
</script>

</body>
</html>