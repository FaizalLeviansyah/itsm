<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Employee;

class SyncEmployees extends Command
{
    protected $signature = 'sync:employees';
    protected $description = 'Sync employees from local Employee master database to local ITSM users table';

    public function handle()
    {
        $this->info('Starting employee synchronization from local Employee model...');

        try {
            $employees = Employee::where('is_active', 1)
                ->whereNotNull('email_work')
                ->where('email_work', '!=', '')
                ->get();

            $totalEmployeeSynced = 0;

            foreach ($employees as $emp) {
                $email = $emp->email_work;
                if (!$email) continue;

                $fullName = $emp->full_name 
                    ?? $emp->name 
                    ?? $emp->employee_name 
                    ?? $emp->fullname 
                    ?? ucwords(str_replace(['.', '_'], ' ', explode('@', $email)[0]));

                $lowerEmail = strtolower(trim($email));
                $rawTitle = $emp->job_title ?? $emp->position ?? '';
                $rawDept  = $emp->department ?? $emp->directorate ?? '';
                
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
                    ['email' => strtolower(trim($email))],
                    [
                        'name' => trim($fullName),
                        'full_name' => trim($fullName),
                        'employee_number' => $emp->employee_number ?? null,
                        'password' => $emp->password ?? bcrypt('defaultpassword'),
                        'role' => $role,
                        'department' => $rawDept ?: null,
                        'job_title' => $rawTitle ?: null,
                        'position' => $rawTitle ?: null,
                        'phone' => $emp->phone ?? null,
                        'company_id' => $emp->company_id ?? null,
                        'is_active' => true,
                        'source' => 'employee'
                    ]
                );
                $totalEmployeeSynced++;
            }

            User::where('email', 'it.support@amarinshipmgmt.com')->update(['role' => 'technician']);
            $this->info("Successfully synchronized {$totalEmployeeSynced} employees.");

        } catch (\Exception $e) {
            $this->error("Employee sync error: " . $e->getMessage());
        }

        // ==========================================
        // 2. SINKRONISASI KAPAL (VESSELS)
        // ==========================================
        $this->info('Starting vessel synchronization...');
        
        $apiKey = env('MASTER_VESSEL_API_KEY', env('MASTER_API_KEY', 'OJABxyAuodPWhds3S0YsPlbu40DTRAwe'));
        $vesselApiUrl = env('MASTER_VESSEL_API_URL', 'http://api.amarin.biz.id/api/v1/data/db_master_ship/vessel');
        $totalVesselSynced = 0;

        try {
            $vesselResponse = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->get("$vesselApiUrl?page=1&per_page=500");

            if ($vesselResponse->successful()) {
                $vessels = $vesselResponse->json()['data'] ?? [];
                
                foreach ($vessels as $vessel) {
                    $vesselName = $vessel['vessel_name'] ?? $vessel['name'] ?? null;
                    if (!$vesselName) continue;

                    $email = $vessel['login_email'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $vesselName)) . '@vessel.amarin.biz.id';

                    User::updateOrCreate(
                        ['email' => strtolower(trim($email))],
                        [
                            'name' => $vesselName,
                            'full_name' => $vesselName,
                            'password' => $vessel['login_password'] ?? $vessel['password'] ?? bcrypt('vesselpassword'),
                            'role' => 'user',
                            'department' => 'Vessel',
                            'job_title' => 'Vessel',
                            'position' => 'Vessel - ' . $vesselName,
                            'source' => 'vessel_api',
                            'source_id' => $vessel['id'] ?? null,
                            'is_active' => true,
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