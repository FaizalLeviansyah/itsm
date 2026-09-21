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
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Tetap menggunakan 'required' biasa agar kapal bisa login menggunakan username/nama kapal
        $request->validate([
            'email' => 'required', 
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        // ----------------------------------------------------------------------
        // 0. API VESSEL INTERCEPTOR (MENGGUNAKAN EXISTING PASSWORD DARI API)
        // ----------------------------------------------------------------------
        try {
            $response = Http::withHeaders([
                'X-API-Key' => 'vUCwahxySBlYglyN1LZ3p6SRh0xcK8Vk',
                'Accept'    => 'application/json'
            ])->get('http://api.amarin.biz.id/api/v1/data/db_master_ship/vessel', [
                'page'     => 1,
                'per_page' => 100, // Diperbesar untuk mengambil seluruh list armada
                'sort'     => '-id',
            ]);

            if ($response->successful()) {
                $vessels = $response->json()['data'] ?? [];

                // Cari kecocokan input login (email/username/kode) dengan data API
                $matchedVessel = collect($vessels)->first(function ($v) use ($email) {
                    return strtolower($v['vessel_name'] ?? '') === strtolower($email) 
                        || strtolower($v['login_email'] ?? '') === strtolower($email)
                        || strtolower($v['vessel_code'] ?? '') === strtolower($email);
                });

                if ($matchedVessel) {
                    $passwordValid = false;
                    
                    // Deteksi field password dari API (mengantisipasi nama key login_password atau password)
                    $apiPassword = $matchedVessel['login_password'] ?? $matchedVessel['password'] ?? '';

                    // Validasi Hash vs Plaintext sesuai standar bawaan sistem Anda
                    if (str_starts_with($apiPassword, '$2y$') || str_starts_with($apiPassword, '$2a$')) {
                        $passwordValid = Hash::check($password, $apiPassword);
                    } else {
                        $passwordValid = ($apiPassword === $password);
                    }

                    if ($passwordValid) {
                        $defaultCompany = Company::first();
                        
                        // Memastikan format email sinkronisasi tidak bertabrakan di database users
                        $safeEmail = filter_var($email, FILTER_VALIDATE_EMAIL) 
                            ? strtolower($email) 
                            : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $matchedVessel['vessel_name'])) . '@vessel.amarin.biz.id';

                        $localUser = User::updateOrCreate(
                            ['email' => $safeEmail],
                            [
                                'name'       => $matchedVessel['vessel_name'] ?? $email,
                                'password'   => Hash::make($password),
                                'department' => 'Vessel',
                                'position'   => 'Vessel - ' . ($matchedVessel['vessel_name'] ?? ''),
                                'source'     => 'vessel_api',
                                'source_id'  => $matchedVessel['id'] ?? null,
                                'company_id' => $defaultCompany?->id ?? 1, // Sinkronisasi otomatis ke PT Amarin Ship Management
                                'role'       => 'user',
                                'is_active'  => 1
                            ]
                        );

                        Auth::login($localUser, $request->boolean('remember'));
                        $request->session()->regenerate();
                        return redirect()->intended('/dashboard');
                    }
                }
            }
        } catch (\Exception $e) {
            // Jika API bermasalah/down, proses akan lanjut ke database lokal sebagai fallback
        }
        // ----------------------------------------------------------------------
        // END OF API VESSEL INTERCEPTOR
        // ----------------------------------------------------------------------


        // 1. Try local DB first (fastest)
        $user = User::where('email', $email)->first();

        if ($user) {
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                if (!$user->is_active) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Your account is inactive. Please contact an administrator.']);
                }

                $masterEmployee = app(\App\Services\MasterEmployeeGate::class)->findByEmail($email);

                if ($masterEmployee && !app(\App\Services\MasterEmployeeGate::class)->passes($masterEmployee)) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors(['email' => 'Your account is inactive. Please contact HR or IT.']);
                }

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

            if ($employee && !app(\App\Services\MasterEmployeeGate::class)->passes($employee)) {
                return back()->withErrors(['email' => 'Your account is inactive. Please contact HR or IT.']);
            }

            if ($employee) {
                $passwordValid = false;

                if (str_starts_with($employee->password ?? '', '$2y$') || str_starts_with($employee->password ?? '', '$2a$')) {
                    $passwordValid = Hash::check($password, $employee->password);
                } else {
                    $passwordValid = ($employee->password === $password);
                }

                if ($passwordValid) {
                    $defaultCompany = Company::first();

                    $assignedRole = $user->role ?? 'user';
                    
                    $empEmail = strtolower($email);
                    $department = strtolower($employee->directorate ?? $employee->department ?? '');
                    $jobTitle = strtolower($employee->job_title ?? '');

                    if (str_contains($empEmail, 'itoperation') || str_contains($empEmail, 'faizal') || str_contains($jobTitle, 'head_it') || str_contains($jobTitle, 'head of it') || str_contains($jobTitle, 'it manager') || $empEmail === 'head.it@amarinshipmgmt.com') {
                        $assignedRole = 'admin';
                    } elseif (str_contains($department, 'it') || str_contains($jobTitle, 'it support') || str_contains($jobTitle, 'it operation')) {
                        $assignedRole = 'technician';
                    }

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

        // 3. Try vessel from db_master_ship (FALLBACK)
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