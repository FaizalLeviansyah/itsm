<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review & Rating - Ticket #{{ $ticket->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans py-10 px-4">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6 sm:p-8">
        
        <!-- Header Info -->
        <div class="flex items-center justify-between border-b pb-4 mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 bg-brand-50 text-brand-700 rounded-md">
                    Ticket #{{ $ticket->id }}
                </span>
                <h1 class="text-xl font-bold text-gray-900 mt-2">{{ $ticket->title }}</h1>
            </div>
            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                {{ $ticket->status == 'resolved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                {{ ucfirst($ticket->status) }}
            </span>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Detail Hasil Kerja Teknisi -->
        <div class="mb-6 bg-gray-50 rounded-xl p-5 border border-gray-200">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Catatan Penyelesaian Teknisi</h3>
            <p class="text-sm text-gray-800 whitespace-pre-line">{{ $ticket->resolution_notes ?? 'Belum ada catatan penyelesaian.' }}</p>
            <div class="mt-3 text-xs text-gray-500 flex items-center gap-2">
                <i class="fas fa-user-shield text-brand-600"></i> Teknisi: 
                <span class="font-medium text-gray-700">
                    {{ $ticket->technicians->pluck('name')->join(', ') ?: 'Tim IT Support' }}
                </span>
            </div>
        </div>

        @if($ticket->status != 'closed')
            <!-- Form Aksi: Beri Rating atau Reopen -->
            <form action="{{ route('ticket.public.submit', $ticket->public_token) }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="border-t pt-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-3">Beri Penilaian & Tutup Tiket</h3>
                    
                    <!-- Bintang Rating -->
                    <div class="flex items-center gap-2 mb-4">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer text-2xl text-gray-300 hover:text-yellow-400 peer">
                                <input type="radio" name="rating" value="{{ $i }}" class="peer hidden" required>
                                <i class="fas fa-star peer-checked:text-yellow-400"></i>
                            </label>
                        @endfor
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Komentar / Ulasan Layanan</label>
                        <textarea name="comment" rows="3" class="w-full border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-brand-500" placeholder="Tuliskan ulasan atau feedback Anda terhadap pelayanan teknisi..."></textarea>
                    </div>

                    <button type="submit" name="action" value="rate" class="mt-4 w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 px-4 rounded-xl text-sm transition shadow">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim Rating & Selesaikan (Close Ticket)
                    </button>
                </div>

                <!-- Opsi Jika Belum Selesai (Reopen) -->
                <div class="border-t pt-6">
                    <details class="group">
                        <summary class="text-xs font-semibold text-red-600 cursor-pointer hover:underline">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Kendala belum tuntas? Klik di sini untuk Reopen Tiket
                        </summary>
                        <div class="mt-3 space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Alasan Membuka Kembali Tiket</label>
                                <textarea name="reopen_reason" rows="2" class="w-full border border-red-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-red-500" placeholder="Jelaskan kendala apa yang masih terjadi..."></textarea>
                            </div>
                            <button type="submit" name="action" value="reopen" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                                Reopen Ticket
                            </button>
                        </div>
                    </details>
                </div>
            </form>
        @else
            <div class="text-center py-4 bg-green-50 rounded-xl text-green-800 text-sm font-medium">
                <i class="fas fa-lock mr-1"></i> Tiket ini telah selesai dan ditutup (Closed). Terima kasih atas partisipasi Anda.
            </div>
        @endif

    </div>
</body>
</html>