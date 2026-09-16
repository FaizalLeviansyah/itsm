<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan kolom di tabel users jika belum ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->integer('company_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'employee_number')) {
                $table->string('employee_number')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('employee_number');
            }
        });

        // Tambahkan kolom company_id di tabel tickets jika belum ada
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'company_id')) {
                $table->integer('company_id')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['company_id', 'employee_number', 'job_title']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
};