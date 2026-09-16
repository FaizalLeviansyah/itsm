<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Services\MasterEmployeeGate;
use App\Services\SsoTokenVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SsoCallbackController extends Controller
{
    public function __construct(
        private SsoTokenVerifier $tokenVerifier,
        private MasterEmployeeGate $employeeGate
    ) {}

    /**
     * Handle SSO callback from Amarin Portal.
     */
    public function handle(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'SSO token not found.');
        }

        // Verify token
        $payload = $this->tokenVerifier->verify($token);

        if (!$payload) {
            Log::warning('SSO: Invalid or expired token', ['ip' => $request->ip()]);
            return redirect()->route('login')->with('error', 'SSO token is invalid or expired. Please log in manually.');
        }

        // Global employment gate (employment_status Active + is_active + not
        // deleted) plus the per-app access column.
        $gate = $this->employeeGate->check($payload['email'], config('sso.access_column'));

        if (!$gate['allowed']) {
            Log::warning('SSO: Access denied by master employee gate', [
                'email' => $payload['email'],
                'reason' => $gate['reason'],
            ]);

            return redirect()->route('login')->with('error', $gate['reason']);
        }

        // Strategy 1: Find existing local user by email
        $user = User::where('email', $payload['email'])->first();

        // Strategy 2: Auto-create from tbl_employee (same logic as LoginController)
        if (!$user) {
            try {
                $employee = Employee::where('email_work', $payload['email'])
                    ->where('is_active', 1)
                    ->first();

                if ($employee) {
                    $defaultCompany = \App\Models\Company::first();

                    // Tentukan role secara dinamis
                    $assignedRole = 'user';
                    $empEmail = strtolower($payload['email']);
                    $department = strtolower($employee->directorate ?? $employee->department ?? '');
                    $jobTitle = strtolower($employee->job_title ?? '');

                    if (str_contains($empEmail, 'itoperation') || str_contains($empEmail, 'faizal') || str_contains($jobTitle, 'head_it') || str_contains($jobTitle, 'head of it') || str_contains($jobTitle, 'it manager') || $empEmail === 'head.it@amarinshipmgmt.com') {
                        $assignedRole = 'admin';
                    } elseif (str_contains($department, 'it') || str_contains($jobTitle, 'it support') || str_contains($jobTitle, 'it operation')) {
                        $assignedRole = 'technician';
                    }

                    $user = User::create([
                        'name' => $employee->full_name ?: $payload['email'],
                        'email' => $payload['email'],
                        'password' => Hash::make(\Illuminate\Support\Str::random(32)),
                        'phone' => $employee->phone ?? null,
                        'department' => $employee->directorate ?? null,
                        'position' => $employee->job_title ?? null,
                        'source' => 'employee',
                        'source_id' => $employee->employee_id ?? null,
                        'company_id' => $defaultCompany?->id,
                        'role' => $assignedRole,
                        'is_active' => true,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('SSO: Failed to auto-create user', ['email' => $payload['email'], 'error' => $e->getMessage()]);
            }
        } else {
            // Pastikan role ter-update otomatis menjadi admin jika memenuhi kriteria
            $empEmail = strtolower($payload['email']);
            $jobTitle = strtolower($user->position ?? '');
            if (str_contains($empEmail, 'itoperation') || str_contains($empEmail, 'faizal') || str_contains($jobTitle, 'head_it') || str_contains($jobTitle, 'head of it') || str_contains($jobTitle, 'it manager') || $empEmail === 'head.it@amarinshipmgmt.com') {
                if ($user->role !== 'admin') {
                    $user->update(['role' => 'admin']);
                }
            }
        }

        if (!$user) {
            Log::warning('SSO: User not found and cannot auto-create', ['email' => $payload['email']]);
            return redirect()->route('login')->with('error', 'Account not found in this system. Please log in manually.');
        }

        if (!$user->is_active) {
            return redirect()->route('login')->with('error', 'Your account is inactive. Please contact an administrator.');
        }

        // Auto login
        Auth::login($user, true);
        $request->session()->regenerate();

        Log::info('SSO: Auto-login successful', ['email' => $payload['email'], 'ip' => $request->ip()]);

        return redirect()->intended(config('sso.redirect_after_login', '/dashboard'));
    }
}