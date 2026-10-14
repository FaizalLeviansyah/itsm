<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class SyncEmployees extends Command
{
    protected $signature = 'sync:employees';
    protected $description = 'Sync employees and vessels from Master API to local ITSM users table';

    public function handle()
    {
        // ==========================================
        // 1. SINKRONISASI PEGAWAI (EMPLOYEES VIA API)
        // ==========================================
        $this->info('Starting employee synchronization from Master API...');

        // Mengambil API Key & URL khusus Employee dari .env
        $employeeApiKey = env('MASTER_EMPLOYEE_API_KEY', 'AZX09KWEGRKT8hiwBHoliBOOI6zbRg5X');
        $employeeApiUrl = env('MASTER_EMPLOYEE_API_URL', 'http://api.amarin.biz.id/api/v1/data/db_master_amarin_original/tbl_employee');
        
        $totalEmployeeSynced = 0;

        try {
            // Mengirimkan API Key via Header X-API-Key (Aman & Tidak tercatat di URL log)
            $employeeResponse = Http::withHeaders([
                'X-API-Key' => $employeeApiKey,
                'Accept'    => 'application/json',
            ])->get($employeeApiUrl, [
                'per_page' => 500, // Memastikan seluruh data pegawai ditarik
                'all'      => 1
            ]);

            if ($employeeResponse->successful()) {
                $jsonResponse = $employeeResponse->json();
                $employees = $jsonResponse['data'] ?? $jsonResponse;

                foreach ($employees as $emp) {
                    $emp = (object) $emp;

                    $email = $emp->email_work ?? $emp->email ?? null;
                    if (!$email) continue;

                    // Ambil full_name atau name asli dari API, pastikan bukan berupa email
                    $rawName = $emp->full_name ?? $emp->employee_name ?? $emp->name ?? null;
                    
                    if (!$rawName || str_contains($rawName, '@')) {
                        // Jika kosong atau isinya email, buatkan nama rapi dari prefix email
                        $name = ucwords(str_replace(['.', '_'], ' ', explode('@', $email)[0]));
                    } else {
                        // Gunakan full_name asli dari API master
                        $name = trim($rawName);
                    }

                    $lowerEmail = strtolower(trim($email));
                    $rawTitle = $emp->job_title ?? $emp->position ?? '';
                    $rawDept  = $emp->department ?? $emp->directorate ?? '';
                    
                    $titleLower = strtolower(trim($rawTitle));
                    $deptLower  = strtolower(trim($rawDept));

                    // Mapping Role
                    if (
                        in_array($lowerEmail, ['head.it@amarinshipmgmt.com', 'itoperation@amarinshipmgmt.com']) ||
                        str_contains($lowerEmail, 'head.it') || 
                        str_contains($lowerEmail, 'itoperation') || 
                        str_contains($titleLower, 'head it') || 
                        str_contains($titleLower, 'it operation')
                    ) {
                        $role = 'admin';
                    } elseif (
                        in_array($lowerEmail, [
                            'it@amarinshipmgmt.com',
                            'it.support@amarinshipmgmt.com',
                            'it_support@amarinshipmgmt.com',
                            'support.it@amarinshipmgmt.com'
                        ]) ||
                        str_contains($lowerEmail, 'it.support') ||
                        str_contains($titleLower, 'it support') || 
                        str_contains($titleLower, 'it staff') || 
                        str_contains($titleLower, 'technician') ||
                        $titleLower === 'it' ||
                        $deptLower === 'it' || 
                        str_contains($deptLower, 'information technology')
                    ) {
                        $role = 'technician';
                    } else {
                        $role = 'user';
                    }

                    $phone = $emp->phone ?? null;
                    if ($phone) {
                        $phone = preg_replace('/[^0-9]/', '', $phone);
                        if (str_starts_with($phone, '08')) {
                            $phone = '628' . substr($phone, 2);
                        }
                    }

                    User::updateOrCreate(
                        ['email' => strtolower(trim($email))],
                        [
                            'name'            => $name,
                            'full_name'       => $name,
                            'employee_number' => $emp->employee_number ?? null,
                            'password'        => $emp->password ?? bcrypt('defaultpassword'),
                            'role'            => $role,
                            'department'      => $rawDept ?: null,
                            'job_title'       => $rawTitle ?: null,
                            'position'        => $rawTitle ?: null,
                            'phone'           => $phone,
                            'company_id'      => $emp->company_id ?? null,
                            'is_active'       => true,
                            'source'          => 'employee_api'
                        ]
                    );
                    $totalEmployeeSynced++;
                }

                // Memastikan role it.support konsisten
                User::where('email', 'it.support@amarinshipmgmt.com')->update(['role' => 'technician']);
                $this->info("Successfully synchronized {$totalEmployeeSynced} employees from API.");
            } else {
                $this->error("Failed to fetch employee data from API. Status: " . $employeeResponse->status() . " | Response: " . $employeeResponse->body());
            }
        } catch (\Exception $e) {
            $this->error("Employee sync error: " . $e->getMessage());
        }

        // ==========================================
        // 2. SINKRONISASI KAPAL (VESSELS)
        // ==========================================
        $this->info('Starting vessel synchronization...');
        
        $vesselApiKey = env('MASTER_VESSEL_API_KEY', 'OJABxyAuodPWhds3S0YsPlbu40DTRAwe');
        $vesselApiUrl = env('MASTER_VESSEL_API_URL', 'http://api.amarin.biz.id/api/v1/data/db_master_ship/vessel');
        $totalVesselSynced = 0;

        try {
            $vesselResponse = Http::withHeaders([
                'X-API-Key' => $vesselApiKey,
                'Accept'    => 'application/json',
            ])->get($vesselApiUrl, [
                'per_page' => 500
            ]);

            if ($vesselResponse->successful()) {
                $vessels = $vesselResponse->json()['data'] ?? [];
                
                foreach ($vessels as $vessel) {
                    $vesselName = $vessel['vessel_name'] ?? $vessel['name'] ?? null;
                    if (!$vesselName) continue;

                    $email = $vessel['login_email'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $vesselName)) . '@vessel.amarin.biz.id';

                    User::updateOrCreate(
                        ['email' => strtolower(trim($email))],
                        [
                            'name'       => $vesselName,
                            'full_name'  => $vesselName,
                            'password'   => $vessel['login_password'] ?? $vessel['password'] ?? bcrypt('vesselpassword'),
                            'role'       => 'user',
                            'department' => 'Vessel',
                            'job_title'  => 'Vessel',
                            'position'   => 'Vessel - ' . $vesselName,
                            'source'     => 'vessel_api',
                            'source_id'  => $vessel['id'] ?? null,
                            'is_active'  => true,
                        ]
                    );
                    $totalVesselSynced++;
                }
                $this->info("Successfully synchronized {$totalVesselSynced} vessels.");
            } else {
                $this->error("Failed to fetch vessel data from API. Status: " . $vesselResponse->status());
            }
        } catch (\Exception $e) {
            $this->error("Vessel sync error: " . $e->getMessage());
        }
    }
}