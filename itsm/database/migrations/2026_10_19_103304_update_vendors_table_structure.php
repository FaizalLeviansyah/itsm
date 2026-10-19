<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Pastikan kolom ditambahkan jika belum ada di database
            if (!Schema::hasColumn('vendors', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('vendors', 'code')) {
                $table->string('code', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('vendors', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('code');
            }
            if (!Schema::hasColumn('vendors', 'email')) {
                $table->string('email')->nullable()->after('contact_person');
            }
            if (!Schema::hasColumn('vendors', 'phone')) {
                $table->string('phone', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('vendors', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('vendors', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['name', 'code', 'contact_person', 'email', 'phone', 'address', 'is_active']);
        });
    }
};