<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Auto-assign rules
        Schema::create('auto_assign_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('sub_category_id')->nullable()->constrained();
            $table->foreignId('assign_to')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->integer('priority_order')->default(0);
            $table->timestamps();
        });

        // Maintenance schedules
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('asset_id')->constrained();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
            $table->date('next_due_date');
            $table->date('last_performed_at')->nullable();
            $table->boolean('auto_create_ticket')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Canned responses
        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });

        // Custom fields
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('field_key');
            $table->enum('field_type', ['text', 'number', 'select', 'date', 'textarea'])->default('text');
            $table->json('options')->nullable(); // for select type
            $table->foreignId('asset_category_id')->nullable()->constrained();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Custom field values for assets
        Schema::create('asset_custom_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_custom_values');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('canned_responses');
        Schema::dropIfExists('maintenance_schedules');
        Schema::dropIfExists('auto_assign_rules');
    }
};
