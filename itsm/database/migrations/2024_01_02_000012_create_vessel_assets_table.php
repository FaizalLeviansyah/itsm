<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add vessel_id to assets for vessel-specific asset tracking
        Schema::table('assets', function (Blueprint $table) {
            $table->string('vessel_name')->nullable()->after('location');
            $table->unsignedBigInteger('vessel_id')->nullable()->after('vessel_name');
        });

        // Add vessel_id to tickets for vessel-specific tickets
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('vessel_name')->nullable()->after('location');
            $table->unsignedBigInteger('vessel_id')->nullable()->after('vessel_name');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['vessel_name', 'vessel_id']);
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['vessel_name', 'vessel_id']);
        });
    }
};
