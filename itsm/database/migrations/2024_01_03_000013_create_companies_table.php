<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique(); // e.g. ASM, AMR
            $table->string('full_name')->nullable(); // PT Amarin Ship Management
            $table->string('logo')->nullable(); // path to logo
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('asset_prefix', 10)->default('AST'); // for asset tag generation
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add company_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('source_id')->constrained()->nullOnDelete();
        });

        // Add company_id to tickets
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('vessel_id')->constrained()->nullOnDelete();
        });

        // Add company_id to assets
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('vessel_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
        Schema::dropIfExists('companies');
    }
};
