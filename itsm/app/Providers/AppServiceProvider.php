<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('viewReports', function ($user) {
            return in_array($user->role, ['admin', 'technician']);
        });

        Gate::define('manageSettings', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('manageTickets', function ($user) {
            return in_array($user->role, ['admin', 'technician']);
        });
    }
}
