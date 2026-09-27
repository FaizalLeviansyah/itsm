<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use Carbon\Carbon;

class AutoCloseResolvedTickets extends Command
{
    protected $signature = 'tickets:auto-close';
    protected $description = 'Otomatis menutup tiket berstatus resolved yang melebihi 3 hari tanpa respon';

    public function handle()
    {
        $threshold = Carbon::now()->subDays(3);

        $affected = Ticket::where('status', 'resolved')
            ->where('updated_at', '<=', $threshold)
            ->update([
                'status' => 'closed',
                'closed_at' => Carbon::now(),
                'close_reason' => 'Otomatis ditutup oleh sistem karena tidak ada respon dari requester selama 3 hari.'
            ]);

        $this->info("Berhasil menutup {$affected} tiket berstatus resolved.");
    }
}