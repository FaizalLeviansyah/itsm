<?php

namespace App\Http\Middleware;

use App\Services\MasterEmployeeGate;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Re-checks the global employment gate on authenticated requests, so an
 * account whose employment_status is revoked cannot keep using an existing
 * session until it expires.
 *
 * Checking at login only is not enough: sessions here are long lived
 * (the portal uses a one-year "remember me" for PWA support).
 *
 * The master DB is queried at most once per user per TTL window to keep this
 * cheap on every request. Default TTL: 5 minutes.
 *
 * Only accounts that exist in the master employee table are affected. Vessel
 * logins, applicants, external signers and local-only admins pass through, so
 * this is safe to apply to a shared authenticated route group.
 *
 * Register in bootstrap/app.php:
 *   $middleware->alias([
 *       'employment.active' => \App\Http\Middleware\EnsureEmploymentActive::class,
 *   ]);
 *
 * Then apply to authenticated route groups:
 *   Route::middleware(['auth', 'employment.active'])->group(...);
 */
class EnsureEmploymentActive
{
    /** Seconds to cache a positive check per user. */
    private const CACHE_TTL = 300;

    public function __construct(
        private MasterEmployeeGate $employeeGate
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $email = $this->currentEmail($request);

        // Not authenticated, or not an employee-style session: nothing to do.
        if (!$email) {
            return $next($request);
        }

        $cacheKey = 'employment_gate_ok:' . sha1(strtolower($email));

        if (Cache::has($cacheKey)) {
            return $next($request);
        }

        // Only employees are subject to the employment gate. Accounts that do
        // not exist in the master employee table - vessel logins, applicants,
        // external signers, local-only admins - are left alone.
        $employee = $this->employeeGate->findByEmail($email);

        if (!$employee) {
            Cache::put($cacheKey, true, self::CACHE_TTL);

            return $next($request);
        }

        if (!$this->employeeGate->passes($employee)) {
            Log::warning('EnsureEmploymentActive: session terminated', [
                'email' => $email,
                'employment_status' => $employee->employment_status ?? null,
                'is_active' => $employee->is_active ?? null,
                'path' => $request->path(),
            ]);

            $this->terminateSession($request);

            return $this->deny($request, 'Akun Anda tidak aktif. Silakan hubungi HR atau IT.');
        }

        Cache::put($cacheKey, true, self::CACHE_TTL);

        return $next($request);
    }

    private function currentEmail(Request $request): ?string
    {
        $user = auth()->user();
        if ($user) {
            return $user->email_work ?? $user->email ?? null;
        }

        // Session-based auth (marine, survey, whistle)
        foreach (['marine_user_email', 'admin_email'] as $key) {
            if ($email = session($key)) {
                return $email;
            }
        }

        $adminEmployee = session('admin_employee');
        if (is_array($adminEmployee) && isset($adminEmployee['email_work'])) {
            return $adminEmployee['email_work'];
        }

        return null;
    }

    private function terminateSession(Request $request): void
    {
        try {
            if (auth()->check()) {
                auth()->logout();
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Throwable $e) {
            // Session may be unavailable on stateless routes.
        }
    }

    private function deny(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 403);
        }

        foreach (['login', 'admin.login', 'auth.login'] as $routeName) {
            try {
                return redirect()->route($routeName)->with('error', $message);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return response($message, 403);
    }
}
