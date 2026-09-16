<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Str;

class SyncEmployees extends Command
{
    protected $signature = 'sync:employees';
    protected $description = 'Sync employees from Master API to local ITSM database';

    public function handle()
    {
        $this->info('Starting employee synchronization...');

        $apiUrl = env('MASTER_EMPLOYEE_API_URL', 'http://api.amarin.biz.id/api/v1/data/db_master_amarin_original/tbl_employee');
        $apiKey = env('MASTER_API_KEY', 'AZX09KWEGRKT8hiwBHoliBOOI6zbRg5X');

        $page = 1;
        $perPage = 100;
        $totalSynced = 0;

        do {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->get("$apiUrl?page=$page&per_page=$perPage");

            if (!$response->successful()) {
                $this->error("Failed to fetch data from API on page {$page}.");
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
                $titleLower = strtolower(trim($emp['job_title'] ?? $emp['position'] ?? ''));
                $deptLower  = strtolower(trim($emp['department'] ?? $emp['directorate'] ?? ''));

                // TENTUKAN ROLE BERDASARKAN PRIORITAS UTAMA
                
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
                // 2. Cek Technician (Whitelist mutlak termasuk it.support)
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
                    ]
                );
                $totalSynced++;
            }

            $page++;
        } while (count($employees) == $perPage);

        // Paksa update untuk memastikan it.support tidak meleset karena cache/updateOrCreate
        User::where('email', 'it.support@amarinshipmgmt.com')->update(['role' => 'technician']);

        $this->info("Successfully synchronized {$totalSynced} employees.");
    }
}