<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration is superseded by 2026_09_27_144941 which already adds softDeletes
     * to both users and tickets. Guard with hasColumn to be safe.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('tickets', 'deleted_at')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('tickets', 'deleted_at')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
