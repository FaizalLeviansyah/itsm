<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-close resolved tickets that have had no response for 3 days
Schedule::command('tickets:auto-close')->dailyAt('00:00');

// Check SLA breach every 15 minutes
Schedule::command('tickets:check-sla')->everyFifteenMinutes();

// Run escalation check every 10 minutes
Schedule::command('tickets:escalate')->everyTenMinutes();

// Send daily WA reminder to requesters for unrated resolved tickets (9am)
Schedule::command('itsm:remind-rating')->dailyAt('09:00');
