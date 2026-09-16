<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_assign_rules', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('sub_category_id')->constrained()->nullOnDelete();
            $table->string('vessel_name')->nullable()->after('company_id');
            $table->enum('source_type', ['all', 'employee', 'vessel'])->default('all')->after('vessel_name');
        });
    }

    public function down(): void
    {
        Schema::table('auto_assign_rules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['vessel_name', 'source_type']);
        });
    }
};
