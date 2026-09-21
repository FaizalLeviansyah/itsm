<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Str;

class SyncEmployees extends Command
{
    protected $signature = 'sync:employees';
    protected $description = 'Sync employees and vessels from Master API to local ITSM database';

    public function handle()
    {
        $this->info('Starting employee synchronization...');

        $apiUrl = env('MASTER_EMPLOYEE_API_URL', 'http://api.amarin.biz.id/api/v1/data/db_master_amarin_original/tbl_employee');
        $apiKey = env('MASTER_API_KEY', 'AZX09KWEGRKT8hiwBHoliBOOI6zbRg5X');

        $page = 1;
        $perPage = 100;
        $totalEmployeeSynced = 0;

        // ==========================================
        // 1. SINKRONISASI PEGAWAI (EMPLOYEES)
        // ==========================================
        do {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->get("$apiUrl?page=$page&per_page=$perPage");

            if (!$response->successful()) {
                $this->error("Failed to fetch employee data from API on page {$page}.");
                break;
            }

            $result = $response->json();
            $employees = $result['data'] ?? [];

            if (empty($employees)) {
                break;
            }

            foreach ($employees as $emp) {
                $email = $emp['email_work'] ?? null;
                if (!$email) continue;

                $name = $emp['name'] ?? $emp['employee_name'] ?? $emp['fullname'] ?? ucwords(str_replace(['.', '_'], ' ', explode('@', $email)[0]));

                $lowerEmail = strtolower(trim($email));
                $rawTitle = $emp['job_title'] ?? $emp['position'] ?? '';
                $rawDept  = $emp['department'] ?? $emp['directorate'] ?? '';
                
                $titleLower = strtolower(trim($rawTitle));
                $deptLower  = strtolower(trim($rawDept));

                // 1. Cek Admin
                if (
                    in_array($lowerEmail, ['head.it@amarinshipmgmt.com', 'itoperation@amarinshipmgmt.com']) ||
                    str_contains($lowerEmail, 'head.it') || 
                    str_contains($lowerEmail, 'itoperation') || 
                    str_contains($titleLower, 'head it') || 
                    str_contains($titleLower, 'it operation')
                ) {
                    $role = 'admin';
                } 
                // 2. Cek Technician
                elseif (
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
                } 
                // 3. Sisanya User biasa
                else {
                    $role = 'user';
                }

                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'employee_number' => $emp['employee_number'] ?? null,
                        'password' => $emp['password'] ?? bcrypt('defaultpassword'),
                        'role' => $role,
                        'department' => $rawDept ?: null,      // Perbaikan: Simpan Departemen
                        'job_title' => $rawTitle ?: null,      // Perbaikan: Simpan Job Title
                        'position' => $rawTitle ?: null,       // Perbaikan: Simpan Posisi
                        'phone' => $emp['phone'] ?? null,
                        'source' => 'employee'
                    ]
                );
                $totalEmployeeSynced++;
            }
            $page++;
        } while (count($employees) == $perPage);

        // Paksa update untuk memastikan it.support tidak meleset
        User::where('email', 'it.support@amarinshipmgmt.com')->update(['role' => 'technician']);


        // ==========================================
        // 2. SINKRONISASI KAPAL (VESSELS)
        // ==========================================
        $this->info('Starting vessel synchronization...');
        
        $vesselApiUrl = env('MASTER_VESSEL_API_URL', 'http://api.amarin.biz.id/api/v1/data/db_master_ship/vessel');
        $totalVesselSynced = 0;

        $vesselResponse = Http::withHeaders([
            'X-API-Key' => $apiKey,
            'Accept' => 'application/json',
        ])->get("$vesselApiUrl?page=1&per_page=500"); // Asumsi total kapal di bawah 500

        if ($vesselResponse->successful()) {
            $vessels = $vesselResponse->json()['data'] ?? [];
            
            foreach ($vessels as $vessel) {
                $vesselName = $vessel['vessel_name'] ?? $vessel['name'] ?? null;
                if (!$vesselName) continue;

                // Gunakan email API, jika kosong buat format: mtqueencentury@vessel.amarin.biz.id
                $email = $vessel['login_email'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $vesselName)) . '@vessel.amarin.biz.id';

                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $vesselName,
                        'password' => $vessel['login_password'] ?? $vessel['password'] ?? bcrypt('vesselpassword'),
                        'role' => 'user',
                        'department' => 'Vessel', // Semua kapal akan masuk departemen 'Vessel'
                        'job_title' => 'Vessel',
                        'position' => 'Vessel - ' . $vesselName,
                        'source' => 'vessel_api',
                        'source_id' => $vessel['id'] ?? null,
                    ]
                );
                $totalVesselSynced++;
            }
        } else {
            $this->error("Failed to fetch vessel data from API.");
        }

        $this->info("Successfully synchronized {$totalEmployeeSynced} employees and {$totalVesselSynced} vessels.");
    }
}