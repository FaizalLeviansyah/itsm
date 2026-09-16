<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        // 1. Try local DB first (fastest)
        $user = User::where('email', $email)->first();

        if ($user) {
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                if (!$user->is_active) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Your account is inactive. Please contact an administrator.']);
                }

                // Global employment gate. Only applied when the account exists
                // in the master employee table, so local-only accounts and
                // vessel users are not affected.
                $masterEmployee = app(\App\Services\MasterEmployeeGate::class)->findByEmail($email);

                if ($masterEmployee && !app(\App\Services\MasterEmployeeGate::class)->passes($masterEmployee)) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors(['email' => 'Your account is inactive. Please contact HR or IT.']);
                }

                // Force admin override if matches admin criteria
                $empEmail = strtolower($email);
                $jobTitle = strtolower($user->position ?? '');
                if (str_contains($empEmail, 'itoperation') || str_contains($empEmail, 'faizal') || str_contains($jobTitle, 'head_it') || str_contains($jobTitle, 'head of it') || str_contains($jobTitle, 'it manager') || $empEmail === 'head.it@amarinshipmgmt.com') {
                    if ($user->role !== 'admin') {
                        $user->update(['role' => 'admin']);
                    }
                }

                $request->session()->regenerate();
                return redirect()->intended('/dashboard');
            }
        }

        // 2. Try tbl_employee from db_master_amarin_original
        try {
            $employee = Employee::where('email_work', $email)->first();

            // Global employment gate before accepting the master password.
            if ($employee && !app(\App\Services\MasterEmployeeGate::class)->passes($employee)) {
                return back()->withErrors(['email' => 'Your account is inactive. Please contact HR or IT.']);
            }

            if ($employee) {
                $passwordValid = false;

                // Check if password is hashed (bcrypt starts with $2y$ or $2a$)
                if (str_starts_with($employee->password ?? '', '$2y$') || str_starts_with($employee->password ?? '', '$2a$')) {
                    $passwordValid = Hash::check($password, $employee->password);
                } else {
                    $passwordValid = ($employee->password === $password);
                }

                if ($passwordValid) {
                    $defaultCompany = Company::first();

                    // --- Logika Penentuan Role Dinamis ---
                    $assignedRole = $user->role ?? 'user';
                    
                    $empEmail = strtolower($email);
                    $department = strtolower($employee->directorate ?? $employee->department ?? '');
                    $jobTitle = strtolower($employee->job_title ?? '');

                    if (str_contains($empEmail, 'itoperation') || str_contains($empEmail, 'faizal') || str_contains($jobTitle, 'head_it') || str_contains($jobTitle, 'head of it') || str_contains($jobTitle, 'it manager') || $empEmail === 'head.it@amarinshipmgmt.com') {
                        $assignedRole = 'admin';
                    } elseif (str_contains($department, 'it') || str_contains($jobTitle, 'it support') || str_contains($jobTitle, 'it operation')) {
                        $assignedRole = 'technician';
                    }
                    // -------------------------------------

                    $localUser = User::updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => $employee->full_name ?: $email,
                            'password' => Hash::make($password),
                            'phone' => $employee->phone ?? null,
                            'department' => $employee->directorate ?? null,
                            'position' => $employee->job_title ?? null,
                            'source' => 'employee',
                            'source_id' => $employee->employee_id ?? null,
                            'company_id' => $user->company_id ?? $defaultCompany?->id,
                            'role' => $assignedRole,
                        ]
                    );

                    Auth::login($localUser, $request->boolean('remember'));
                    $request->session()->regenerate();
                    return redirect()->intended('/dashboard');
                }
            }
        } catch (\Exception $e) {
            // External DB not available, continue
        }

        // 3. Try vessel from db_master_ship
        try {
            $vessel = Vessel::where('login_email', $email)
                ->where('is_active', 1)
                ->first();
            
            if ($vessel) {
                $passwordValid = false;

                if (str_starts_with($vessel->login_password ?? '', '$2y$') || str_starts_with($vessel->login_password ?? '', '$2a$')) {
                    $passwordValid = Hash::check($password, $vessel->login_password);
                } else {
                    $passwordValid = ($vessel->login_password === $password);
                }

                if ($passwordValid) {
                    $defaultCompany = Company::first();
                    $localUser = User::updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => $vessel->name ?: $email,
                            'password' => Hash::make($password),
                            'department' => 'Vessel',
                            'position' => 'Vessel - ' . ($vessel->name ?? ''),
                            'source' => 'vessel',
                            'source_id' => $vessel->id ?? null,
                            'company_id' => $user ? ($user->company_id ?? $defaultCompany?->id) : $defaultCompany?->id,
                            'role' => $user ? ($user->role ?? 'user') : 'user',
                        ]
                    );

                    Auth::login($localUser, $request->boolean('remember'));
                    $request->session()->regenerate();
                    return redirect()->intended('/dashboard');
                }
            }
        } catch (\Exception $e) {
            // External DB not available, continue
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}