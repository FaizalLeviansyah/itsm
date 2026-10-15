<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Kolom relasi ke tabel vendors (nullable jika ditangani internal)
            $table->foreignId('vendor_id')->nullable()->after('assigned_to')->constrained('vendors')->nullOnDelete();
            
            // Kolom pembeda jenis support (internal atau third_party)
            $table->enum('support_type', ['internal', 'third_party'])->default('internal')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'support_type']);
        });
    }
};