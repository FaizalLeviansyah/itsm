<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Central gate for employee authentication against the master database
 * (tbl_employee in db_master_amarin_original).
 *
 * Why this class exists:
 *
 * 1. Connection naming is not consistent across internal systems. The same
 *    master database is registered as 'master', 'amarin', 'mysql_master',
 *    'mysql_second', 'master_employee', 'master_amarin', 'mysql_amarin',
 *    'mysql_master_amarin' or 'db_master_amarin_original' depending on the
 *    project. Previously the SSO client hardcoded 'master' only, so the check
 *    silently failed (try/catch) on most systems.
 *
 * 2. The employment gate must be applied in one place: an employee may only
 *    authenticate when employment_status is 'Active' (HR source of truth),
 *    is_active = 1 and is_deleted = 0. Per-application rules (access_* column,
 *    local roles) are evaluated afterwards.
 *
 * Fail behaviour:
 *   - No master connection configured at all (the project does not use the
 *     master DB) => gate is skipped, the application decides on its own.
 *   - Master connection exists but the employee is missing or not active
 *     => access denied.
 */
class MasterEmployeeGate
{
    /**
     * Employment status values allowed to authenticate.
     * NULL is treated as Active because the column is nullable and defaults
     * to 'Active' in the database.
     */
    public const LOGINABLE_EMPLOYMENT_STATUSES = ['Active'];

    /**
     * Candidate connection names for the master employee database.
     */
    private const DEFAULT_CONNECTIONS = [
        'master',
        'amarin',
        'mysql_master',
        'master_employee',
        'master_amarin',
        'mysql_amarin',
        'mysql_master_amarin',
        'mysql_second',
        'db_master_amarin_original',
    ];

    /**
     * Resolve the connection name that actually points to the master
     * employee database, or null when this project has none.
     */
    public function resolveConnection(): ?string
    {
        $candidates = config('sso.master_connections') ?: self::DEFAULT_CONNECTIONS;
        $table = config('sso.employee_table', 'tbl_employee');

        foreach ((array) $candidates as $connection) {
            if (!Config::has("database.connections.{$connection}")) {
                continue;
            }

            try {
                DB::connection($connection)->table($table)->limit(1)->exists();

                return $connection;
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Fetch the employee row from the master database by work email.
     * Returns null when not found or when there is no master connection.
     */
    public function findByEmail(string $email): ?object
    {
        $connection = $this->resolveConnection();

        if (!$connection) {
            return null;
        }

        $table = config('sso.employee_table', 'tbl_employee');
        $emailColumn = config('sso.master_email_column', 'email_work');

        try {
            return DB::connection($connection)
                ->table($table)
                ->where($emailColumn, $email)
                ->first();
        } catch (\Throwable $e) {
            Log::warning('MasterEmployeeGate: employee lookup failed', [
                'connection' => $connection,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Whether the given master employee row is allowed to authenticate.
     */
    public function passes(object $employee): bool
    {
        if ((int) ($employee->is_active ?? 0) !== 1) {
            return false;
        }

        if ((int) ($employee->is_deleted ?? 0) === 1) {
            return false;
        }

        // Column may be absent on legacy schemas; only enforce when present.
        if (!property_exists($employee, 'employment_status')) {
            return true;
        }

        $status = $employee->employment_status;

        return $status === null
            || in_array($status, self::LOGINABLE_EMPLOYMENT_STATUSES, true);
    }

    /**
     * Full gate check for an email address.
     *
     * @return array{allowed: bool, reason: ?string, employee: ?object}
     */
    public function check(string $email, ?string $accessColumn = null): array
    {
        $connection = $this->resolveConnection();

        // This project is not wired to the master DB - nothing to enforce here.
        if (!$connection) {
            return ['allowed' => true, 'reason' => null, 'employee' => null];
        }

        $employee = $this->findByEmail($email);

        if (!$employee) {
            return [
                'allowed' => false,
                'reason' => 'Akun tidak ditemukan di database master. Hubungi HR atau IT.',
                'employee' => null,
            ];
        }

        if (!$this->passes($employee)) {
            Log::warning('MasterEmployeeGate: login blocked, employee not active', [
                'email' => $email,
                'employment_status' => $employee->employment_status ?? null,
                'is_active' => $employee->is_active ?? null,
                'is_deleted' => $employee->is_deleted ?? null,
            ]);

            return [
                'allowed' => false,
                'reason' => 'Akun Anda tidak aktif. Silakan hubungi HR atau IT.',
                'employee' => $employee,
            ];
        }

        if ($accessColumn) {
            if (!property_exists($employee, $accessColumn)) {
                Log::warning('MasterEmployeeGate: access column missing on employee row', [
                    'email' => $email,
                    'column' => $accessColumn,
                ]);
            } elseif (empty($employee->{$accessColumn})) {
                return [
                    'allowed' => false,
                    'reason' => 'Anda tidak memiliki akses ke aplikasi ini. Hubungi administrator.',
                    'employee' => $employee,
                ];
            }
        }

        return ['allowed' => true, 'reason' => null, 'employee' => $employee];
    }
}
