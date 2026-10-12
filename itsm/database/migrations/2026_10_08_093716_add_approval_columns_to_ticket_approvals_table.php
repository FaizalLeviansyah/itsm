<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_approvals', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('ticket_approvals', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('ticket_approvals', 'notes')) {
                $table->text('notes')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_approvals', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'approved_at', 'notes']);
        });
    }
};