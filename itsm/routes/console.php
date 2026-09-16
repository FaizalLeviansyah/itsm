<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Check SLA breach every 15 minutes
Schedule::command('tickets:check-sla')->everyFifteenMinutes();

// Run escalation check every 10 minutes
Schedule::command('tickets:escalate')->everyTenMinutes();

// Remind users to rate resolved tickets (every 6 hours)
Schedule::command('tickets:remind-rating')->everySixHours();

// Process maintenance schedules daily at 7am
Schedule::command('maintenance:process')->dailyAt('07:00');

// Send daily digest at 8am
Schedule::command('digest:send')->dailyAt('08:00');
